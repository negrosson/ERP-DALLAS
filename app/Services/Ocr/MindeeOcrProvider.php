<?php

namespace App\Services\Ocr;

use App\Contracts\OcrProviderInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Mindee\V2\Client;
use Mindee\V2\Product\Extraction\ExtractionResponse;
use Mindee\V2\Product\Extraction\Params\ExtractionParameters;
use Mindee\Input\PathInput;
use Mindee\ClientOptions\PollingOptions;
use Mindee\V2\Parsing\Inference\Field\ListField;
use Mindee\V2\Parsing\Inference\Field\ObjectField;
use Mindee\V2\Parsing\Inference\Field\SimpleField;

class MindeeOcrProvider implements OcrProviderInterface
{
    protected string $apiKey;
    protected string $modelId;

    public function __construct()
    {
        $this->apiKey = env('MINDEE_API_KEY', '');
        // Usamos el ID del modelo proporcionado por el usuario por defecto
        $this->modelId = env('MINDEE_MODEL_ID', 'f60a11a2-1c12-4654-9bd9-774cb51d229a');
    }

    public function extractInvoiceData(UploadedFile $file, string $proveedorNombre): array
    {
        if (empty($this->apiKey)) {
            throw new \Exception('La API Key de Mindee no está configurada en el archivo .env (MINDEE_API_KEY).');
        }

        try {
            $mindeeClient = new Client($this->apiKey);
            $modelParams = new ExtractionParameters($this->modelId);
            
            $tempPath = $file->getRealPath();
            $inputSource = new PathInput($tempPath);

            Log::debug("Enviando documento a Mindee SDK (Custom Model: {$this->modelId})...");

            // Aumentamos los tiempos de espera para evitar bloqueos del WAF de Mindee
            $pollingOptions = new PollingOptions(4.0, 3.0, 15);

            $response = $mindeeClient->enqueueAndGetResult(
                ExtractionResponse::class,
                $inputSource,
                $modelParams,
                $pollingOptions
            );

            Log::debug("Procesamiento de Mindee exitoso.");

            // =========================================================
            // ESTRATEGIA 1: Usar los objetos nativos del SDK V2
            // =========================================================
            $items = $this->parseFromSdkObjects($response);
            
            // =========================================================
            // ESTRATEGIA 2 (fallback): Parsear el JSON crudo si el SDK 
            // no devolvió resultados con la estrategia 1
            // =========================================================
            if (empty($items)) {
                Log::debug("Estrategia SDK no encontró productos. Intentando parse de JSON crudo...");
                $rawJson = $response->getRawHttp();
                Log::debug("=== MINDEE RAW JSON COMPLETO ===");
                Log::debug($rawJson);
                Log::debug("=== FIN MINDEE RAW JSON ===");
                
                $resultData = json_decode($rawJson, true) ?? [];
                $items = $this->parseFromRawJson($resultData);
            }

            if (empty($items)) {
                Log::warning("El parser de Mindee V2 retornó 0 productos válidos.");
            } else {
                Log::info("Mindee V2 extrajo " . count($items) . " productos exitosamente.");
            }

            return $items;

        } catch (\Exception $e) {
            Log::error("Excepción en MindeeOcrProvider (SDK): " . $e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Estrategia 1: Parsear usando los objetos nativos del SDK V2
     * 
     * Estructura esperada del SDK:
     *   ExtractionResponse
     *     ->inference (ExtractionInference)
     *       ->result (ExtractionResult)
     *         ->fields (InferenceFields extends ArrayObject)
     *           Cada campo puede ser:
     *             SimpleField (tiene ->value)
     *             ListField (tiene ->items[])  
     *             ObjectField (tiene ->fields (InferenceFields))
     *
     * Para facturas, esperamos un ListField que contenga ObjectField items,
     * donde cada ObjectField tiene SimpleFields con los datos del producto.
     */
    private function parseFromSdkObjects(ExtractionResponse $response): array
    {
        $items = [];

        try {
            $fields = $response->inference->result->fields;
            
            // Loguear todas las claves de campos disponibles para diagnóstico
            $fieldKeys = [];
            foreach ($fields->getArrayCopy() as $key => $field) {
                $type = get_class($field);
                $shortType = basename(str_replace('\\', '/', $type));
                $fieldKeys[] = "$key ($shortType)";
            }
            Log::debug("Campos encontrados en Mindee: " . implode(', ', $fieldKeys));

            // Buscar el primer ListField (que contiene las líneas de productos)
            $listFieldName = null;
            $listField = null;
            foreach ($fields->getArrayCopy() as $key => $field) {
                if ($field instanceof ListField) {
                    $listFieldName = $key;
                    $listField = $field;
                    Log::debug("ListField encontrado: '$key' con " . count($field->items) . " items");
                    break;
                }
            }

            if (!$listField || empty($listField->items)) {
                Log::warning("No se encontró ningún ListField con items en la respuesta del SDK.");
                return [];
            }

            // Cada item del ListField puede ser un ObjectField (fila de producto)
            foreach ($listField->items as $index => $item) {
                if ($item instanceof ObjectField) {
                    $lineData = $this->extractObjectFieldValues($item);
                    Log::debug("Producto SDK #{$index}: " . json_encode($lineData));
                    
                    $codigo = $this->findValue($lineData, ['product_code', 'codigo', 'sku', 'code', 'item_code']);
                    $descripcion = $this->findValue($lineData, ['description', 'descripcion', 'name', 'nombre', 'item_description', 'product_name']);
                    $cantidad = $this->findNumericValue($lineData, ['quantity', 'cantidad', 'qty', 'item_quantity'], 1);
                    $precioUnitario = $this->findNumericValue($lineData, ['unit_price', 'precio_unitario', 'price', 'precio', 'item_price'], 0);

                    if ($descripcion !== 'N/A' || $codigo !== 'N/A') {
                        $items[] = [
                            'codigo' => $codigo,
                            'descripcion' => $descripcion !== 'N/A' ? $descripcion : $codigo,
                            'cantidad' => $cantidad,
                            'precio_unitario' => $precioUnitario,
                        ];
                    }
                } elseif ($item instanceof SimpleField) {
                    // Algunos modelos devuelven listas de valores simples
                    Log::debug("SimpleField en lista #{$index}: " . ($item->value ?? 'null'));
                }
            }
        } catch (\Exception $e) {
            Log::warning("Error al parsear con objetos SDK: " . $e->getMessage());
        }

        return $items;
    }

    /**
     * Extrae todos los valores de un ObjectField como un array asociativo plano.
     */
    private function extractObjectFieldValues(ObjectField $objectField): array
    {
        $values = [];
        foreach ($objectField->fields->getArrayCopy() as $key => $subField) {
            if ($subField instanceof SimpleField) {
                $values[$key] = $subField->value;
            } elseif ($subField instanceof ObjectField) {
                // Recursión para campos anidados
                $nested = $this->extractObjectFieldValues($subField);
                foreach ($nested as $nk => $nv) {
                    $values[$key . '_' . $nk] = $nv;
                }
            } elseif ($subField instanceof ListField) {
                // Para sub-listas, tomar el primer valor
                if (!empty($subField->items) && $subField->items[0] instanceof SimpleField) {
                    $values[$key] = $subField->items[0]->value;
                }
            }
        }
        return $values;
    }

    /**
     * Estrategia 2 (fallback): Parsear desde el JSON crudo.
     * Busca recursivamente cualquier array que parezca una lista de productos.
     */
    private function parseFromRawJson(array $data): array
    {
        $items = [];

        // Buscar recursivamente arrays que parezcan líneas de productos
        $candidateLists = $this->findProductLists($data);
        
        if (empty($candidateLists)) {
            Log::warning("Fallback JSON: No se encontró ninguna lista candidata de productos.");
            return [];
        }

        // Usar la lista con más elementos
        usort($candidateLists, function($a, $b) {
            return count($b) - count($a);
        });
        $bestList = $candidateLists[0];
        Log::debug("Fallback JSON: Usando lista con " . count($bestList) . " elementos");

        foreach ($bestList as $line) {
            if (!is_array($line)) continue;
            
            // Aplanar el objeto: si tiene subcampos con 'value', extraerlos
            $flat = $this->flattenMindeeObject($line);
            
            $codigo = $this->findValue($flat, ['product_code', 'codigo', 'sku', 'code', 'item_code']);
            $descripcion = $this->findValue($flat, ['description', 'descripcion', 'name', 'nombre', 'item_description', 'product_name']);
            $cantidad = $this->findNumericValue($flat, ['quantity', 'cantidad', 'qty', 'item_quantity'], 1);
            $precioUnitario = $this->findNumericValue($flat, ['unit_price', 'precio_unitario', 'price', 'precio', 'item_price'], 0);

            if ($descripcion !== 'N/A' || $codigo !== 'N/A') {
                $items[] = [
                    'codigo' => $codigo,
                    'descripcion' => $descripcion !== 'N/A' ? $descripcion : $codigo,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                ];
            }
        }

        return $items;
    }

    /**
     * Busca recursivamente en un array cualquier sub-array que parezca una lista de productos.
     * Un candidato es un array indexado (numérico) con al menos 1 elemento que sea un array asociativo.
     */
    private function findProductLists(array $data, int $depth = 0): array
    {
        $candidates = [];
        
        if ($depth > 10) return $candidates; // Evitar recursión infinita

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // ¿Es este un array indexado (lista) con al menos 1 elemento?
                if (isset($value[0]) && is_array($value[0])) {
                    $candidates[] = $value;
                }
                // También hay que buscar dentro de 'items' (estructura Mindee V2)
                if (isset($value['items']) && is_array($value['items'])) {
                    $candidates[] = $value['items'];
                }
                // Recursión
                $nested = $this->findProductLists($value, $depth + 1);
                $candidates = array_merge($candidates, $nested);
            }
        }

        return $candidates;
    }

    /**
     * Aplana un objeto Mindee V2 JSON. Convierte:
     *   {"campo": {"value": "X"}} → {"campo": "X"}
     *   {"campo": {"fields": {"sub": {"value": "Y"}}}} → {"campo_sub": "Y"}
     */
    private function flattenMindeeObject(array $obj): array
    {
        $flat = [];
        foreach ($obj as $key => $val) {
            if (is_array($val)) {
                if (array_key_exists('value', $val)) {
                    $flat[$key] = $val['value'];
                } elseif (isset($val['fields']) && is_array($val['fields'])) {
                    foreach ($val['fields'] as $subKey => $subVal) {
                        if (is_array($subVal) && array_key_exists('value', $subVal)) {
                            $flat[$key . '_' . $subKey] = $subVal['value'];
                        }
                    }
                } else {
                    // Intentar recursión simple
                    $nested = $this->flattenMindeeObject($val);
                    foreach ($nested as $nk => $nv) {
                        $flat[$key . '_' . $nk] = $nv;
                    }
                }
            } else {
                $flat[$key] = $val;
            }
        }
        return $flat;
    }

    /**
     * Busca un valor string en un array plano usando múltiples claves posibles.
     */
    private function findValue(array $data, array $possibleKeys): string
    {
        foreach ($possibleKeys as $key) {
            if (isset($data[$key]) && $data[$key] !== null && $data[$key] !== '') {
                return (string) $data[$key];
            }
        }
        // Intento con búsqueda parcial (el campo puede llamarse "item_description" y buscamos "description")
        foreach ($possibleKeys as $searchKey) {
            foreach ($data as $dataKey => $dataValue) {
                if (stripos($dataKey, $searchKey) !== false && $dataValue !== null && $dataValue !== '') {
                    return (string) $dataValue;
                }
            }
        }
        return 'N/A';
    }

    /**
     * Busca un valor numérico en un array plano usando múltiples claves posibles.
     */
    private function findNumericValue(array $data, array $possibleKeys, $default = 0)
    {
        foreach ($possibleKeys as $key) {
            if (isset($data[$key])) {
                $val = $data[$key];
                if (is_numeric($val)) return $val + 0; // Convertir a int o float
                // Intentar limpiar formato numérico (ej: "1.234,56" → 1234.56)
                $cleaned = preg_replace('/[^\d.,\-]/', '', (string) $val);
                $cleaned = str_replace(',', '.', $cleaned);
                if (is_numeric($cleaned)) return $cleaned + 0;
            }
        }
        // Búsqueda parcial
        foreach ($possibleKeys as $searchKey) {
            foreach ($data as $dataKey => $dataValue) {
                if (stripos($dataKey, $searchKey) !== false && $dataValue !== null) {
                    if (is_numeric($dataValue)) return $dataValue + 0;
                }
            }
        }
        return $default;
    }
}
