<?php

namespace App\Services\Ocr;

use App\Contracts\OcrProviderInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

/**
 * Proveedor de extracción de facturas mediante Google Gemini AI (Multimodal Vision).
 * 
 * Este servicio procesa imágenes o PDFs de facturas (CCU, Embonor Coca-Cola, etc.)
 * y utiliza la inteligencia artificial de Google para extraer con alta precisión:
 * - Número / Folio oficial de la factura
 * - Fecha de emisión
 * - Líneas de productos (código de proveedor, descripción, cantidad y precio unitario)
 * - Excluye automáticamente servicios no inventariables como "Flete de Mercaderías"
 * - Consolida productos duplicados dentro de la misma factura
 */
class GeminiOcrProvider implements OcrProviderInterface
{
    protected string $apiKey;
    protected string $model;

    public function __construct()
    {
        // Leemos la clave de API y el modelo configurado en config/services.php o .env
        $this->apiKey = config('services.ocr.gemini_api_key', env('GEMINI_API_KEY', ''));
        $this->model = config('services.ocr.gemini_model', env('GEMINI_MODEL', 'gemini-3.8-flash'));
    }

    /**
     * Procesa el archivo de la factura usando Google Gemini Vision.
     *
     * @param UploadedFile $file Archivo de imagen o PDF de la factura
     * @param string $proveedorNombre Nombre del proveedor (ej: CCU, Coca-Cola) para contexto
     * @return array Estructura con datos de factura e ítems consolidados
     */
    public function extractInvoiceData(UploadedFile $file, string $proveedorNombre): array
    {
        // 1. Validar que la API Key esté configurada
        if (empty($this->apiKey)) {
            Log::error('GeminiOcrProvider: No se ha configurado GEMINI_API_KEY en el archivo .env.');
            throw new \Exception('La API Key de Google Gemini no está configurada en el archivo .env (GEMINI_API_KEY).');
        }

        // 2. Determinar MIME Type y codificar imagen a Base64 para el payload multimodal
        $mimeType = $file->getMimeType() ?: 'image/jpeg';
        $fileBytes = file_get_contents($file->getRealPath());
        $base64Data = base64_encode($fileBytes);

        // 3. Prompt de Ingeniería especializado en Facturas Chilenas (DTE / SII) y Auditoría de Proveedor
        $prompt = <<<PROMPT
Eres un experto auditor de Facturas Electrónicas Chilenas (DTE) y extracción de datos para un sistema ERP.
El usuario indicó que el proveedor emisor debería ser: "{$proveedorNombre}".

Instrucciones estrictas de extracción y validación:
1. "emisor_razon_social": Extrae el nombre o razón social exacta de la empresa emisora que figura en el encabezado de la factura (ej: "COMPAÑIA CERVECERIAS UNIDAS S.A.", "CCU CHILE LTDA", "CERVECERIA CHILE S.A.", "COCA-COLA EMBONOR S.A.", "EMBOTELLADORA ANDINA S.A.", "DISTRIBUIDORA DIMAK S.A.").
2. "rut_emisor": RUT del proveedor emisor tal como figura en el encabezado o recuadro superior derecho (ej: "90.413.000-1", "96.809.310-K", "93.281.000-K", "99.554.560-8").
3. "marca_detectada": Identifica de forma precisa la marca o conglomerado emisor del documento:
   - "CCU": Si el emisor es Compañía Cervecerías Unidas, CCU Chile, Cervecería Chile, o si los productos/logos son Cristal, Escudo, Royal Guard, Heineken, Coors, Becker, Pepsi, Bilz, Pap, Kem Xtreme, Canada Dry, Cachantun, Gatorade, etc.
   - "COCA_COLA": Si el emisor es Coca-Cola Embonor, Embotelladora Andina, The Coca-Cola Company, o si los productos/logos son Coca-Cola, Fanta, Sprite, Nordic Mist, Monster Energy, Powerade, Benedictino, Schweppes, etc.
   - "DIMAK": Si el emisor es Distribuidora Dimak.
   - "OTRO": Si no corresponde con certeza a ninguna de las anteriores.
4. "numero_factura": Extrae el número o folio oficial de la factura (busca "FACTURA ELECTRONICA N°", "Folio" o número impreso arriba a la derecha). Devuelve solo los dígitos limpios sin puntos (ej: 177928645 o 104557266).
5. "fecha_emision": Fecha de emisión de la factura en formato YYYY-MM-DD.
6. "total_factura": Monto total final a pagar (número entero).
7. "items": Lista de productos detallados en la tabla de la factura:
   - "codigo": Código interno del proveedor (ej: "870400", "870402", "0166", "1963", "1448", "1745").
   - "descripcion": Nombre o descripción comercial del producto (ej: "RED BULL TRADIC 4PFx6-LAT250", "MONSTER ENERGY X06 473 CC").
   - "cantidad": Cantidad vendida. Si viene en formato caja/fracción (ej: "1/0", "2/0", "4/0" en Embonor), extrae el número principal de cajas (1, 2, 4). Debe ser un número entero o decimal positivo.
   - "precio_unitario": Precio unitario neto de compra (número).

REGLAS CRÍTICAS DE NEGOCIO:
- EXCLUYE Y OMITE cualquier línea que corresponda a "Flete de Mercaderías", "Flete", "Transporte" o código "9999", ya que no es un producto físico de inventario.
- Si un mismo código de producto aparece repetido en dos filas de la factura, SUMA sus cantidades en un solo elemento para evitar duplicados.

Responde ÚNICAMENTE con un objeto JSON válido con la siguiente estructura:
{
  "emisor_razon_social": "COMPAÑIA CERVECERIAS UNIDAS S.A.",
  "rut_emisor": "90.413.000-1",
  "marca_detectada": "CCU",
  "numero_factura": "177928645",
  "fecha_emision": "2026-05-16",
  "total_factura": 191344,
  "items": [
    {
      "codigo": "870400",
      "descripcion": "RED BULL TRADIC 4PFx6-LAT250",
      "cantidad": 3,
      "precio_unitario": 24844
    }
  ]
}
PROMPT;

        // 4. Preparar la llamada a la API de Google Gemini
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data' => $base64Data,
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'response_mime_type' => 'application/json',
                'temperature' => 0.1, // Baja temperatura para máxima precisión determinista
            ]
        ];

        try {
            Log::info("GeminiOcrProvider: Enviando factura a Google Gemini ({$this->model})...");

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_TIMEOUT, 60); // 60 segundos máx para imágenes de alta resolución
            // Evitar error OpenSSL verify result (20) en entornos Windows sin bundle CA local
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($httpCode !== 200 || !empty($curlError)) {
                Log::error("GeminiOcrProvider Error HTTP {$httpCode}: {$curlError} - Respuesta: " . substr($response, 0, 500));
                throw new \Exception("Error al conectar con Google Gemini API (Código {$httpCode}): " . ($curlError ?: 'Verifique la clave y cuotas.'));
            }

            $responseData = json_decode($response, true);
            $rawText = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '';

            if (empty($rawText)) {
                Log::warning('GeminiOcrProvider: Gemini devolvió una respuesta vacía.');
                return [];
            }

            $parsedJson = json_decode($rawText, true);

            if (!is_array($parsedJson)) {
                Log::error('GeminiOcrProvider: No se pudo parsear el JSON retornado por Gemini: ' . $rawText);
                return [];
            }

            Log::info("GeminiOcrProvider: Factura N° " . ($parsedJson['numero_factura'] ?? 'S/N') . " extraída con éxito (" . count($parsedJson['items'] ?? []) . " productos).");

            return $parsedJson;

        } catch (\Exception $e) {
            Log::error('GeminiOcrProvider Excepción: ' . $e->getMessage());
            throw $e;
        }
    }
}
