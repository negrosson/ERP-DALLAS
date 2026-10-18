<?php

namespace App\Services;

use App\Models\CatalogoProducto;
use App\Models\MapeoCodigo;

class ProductMatcherService
{
    /**
     * Intenta resolver el producto del catálogo para un ítem escaneado.
     */
    public function match(string $codigoProveedor, string $descripcion, ?int $proveedorId = null): ?CatalogoProducto
    {
        $codigoProveedor = trim($codigoProveedor);
        $descripcion = trim($descripcion);

        // 1. Mapeo existente por proveedor y código
        if ($proveedorId && !empty($codigoProveedor)) {
            $mapeo = MapeoCodigo::where('proveedor_id', $proveedorId)
                ->where('codigo_proveedor', $codigoProveedor)
                ->first();
            if ($mapeo && $mapeo->catalogoProducto && $mapeo->catalogoProducto->activo) {
                return $mapeo->catalogoProducto;
            }
        }

        // 2. Coincidencia exacta por SKU
        if (!empty($codigoProveedor)) {
            $prodSku = CatalogoProducto::where('activo', true)
                ->where('sku', $codigoProveedor)
                ->first();
            if ($prodSku) {
                return $prodSku;
            }
        }

        // 3. Coincidencia inteligente por Atributos y Nombre
        return $this->fuzzyMatch($descripcion);
    }

    /**
     * Búsqueda por similitud semántica y coincidencia de atributos clave
     */
    public function fuzzyMatch(string $descripcion): ?CatalogoProducto
    {
        $parsed = $this->parseAttributes($descripcion);
        $descLower = strtolower($descripcion);

        $candidatos = CatalogoProducto::where('activo', true)->get();
        $mejorCoincidencia = null;
        $mejorScore = 0;

        foreach ($candidatos as $prod) {
            $score = 0;
            $prodNombreLower = strtolower($prod->nombre);

            // Coincidencia de marca principal
            if (!empty($parsed['marca'])) {
                if (stripos($prodNombreLower, $parsed['marca']) !== false) {
                    $score += 35;
                }
            }

            // Coincidencia de variante / sabor
            if (!empty($parsed['variante'])) {
                if (stripos($prodNombreLower, $parsed['variante']) !== false) {
                    $score += 25;
                }
            }

            // Coincidencia de capacidad / volumen
            if (!empty($parsed['capacidad_norm'])) {
                $prodCapNorm = $this->normalizarCapacidad($prod->capacidad ?: $prod->nombre);
                if ($prodCapNorm === $parsed['capacidad_norm']) {
                    $score += 30;
                }
            }

            // Coincidencia de formato (lata, vidrio, pet)
            if (!empty($parsed['formato'])) {
                if ($prod->formato === $parsed['formato'] || stripos($prodNombreLower, strtolower($parsed['formato'])) !== false) {
                    $score += 10;
                }
            }

            // Similitud general de texto
            similar_text($prodNombreLower, $descLower, $textSimilarity);
            $score += ($textSimilarity * 0.15);

            if ($score > $mejorScore && $score >= 80) { // Umbral de confianza alta
                $mejorScore = $score;
                $mejorCoincidencia = $prod;
            }
        }

        return $mejorCoincidencia;
    }

    /**
     * Extrae formato, capacidad, empaque y nombre limpio de la descripción escaneada
     */
    public function parseAttributes(string $descripcion): array
    {
        $desc = strtoupper(trim($descripcion));

        // Empaque (x24, x12, x6, x06, etc.)
        $empaque = 1;
        if (preg_match('/X\s*(\d{1,2})/i', $desc, $m)) {
            $empaque = (int)$m[1];
        }

        // Formato de envase
        $formato = 'Lata';
        if (preg_match('/VIDRIO|VID/i', $desc)) {
            $formato = 'Botella Vidrio';
        } elseif (preg_match('/RETORNABLE|RET/i', $desc)) {
            $formato = 'Botella Retornable';
        } elseif (preg_match('/PET|PLASTICO/i', $desc)) {
            $formato = 'Botella PET';
        } elseif (preg_match('/LATA/i', $desc)) {
            $formato = 'Lata';
        } elseif (preg_match('/TETRA/i', $desc)) {
            $formato = 'Tetrapak';
        } elseif (preg_match('/BARPIL|KEG/i', $desc)) {
            $formato = 'Barril / Keg';
        }

        // Capacidad / Volumen
        $capacidad = '350 cc';
        $capacidadNorm = '350';
        if (preg_match('/(\d+(?:[.,]\d+)?)\s*(CC|ML|L|LT|LTS)/i', $desc, $m)) {
            $val = str_replace(',', '.', $m[1]);
            $unit = strtoupper($m[2]);
            if (in_array($unit, ['L', 'LT', 'LTS'])) {
                $capacidad = "{$val} L";
                $capacidadNorm = (string)((float)$val * 1000);
            } else {
                $capacidad = "{$val} {$unit}";
                $capacidadNorm = (string)(int)$val;
            }
        } elseif (preg_match('/(\d{3,4})\b/', $desc, $m)) {
            // Caso como PET 1750 o 1000
            $val = (int)$m[1];
            if ($val >= 200 && $val <= 3000) {
                if ($val >= 1000) {
                    $capacidad = ($val / 1000) . " L";
                } else {
                    $capacidad = "{$val} cc";
                }
                $capacidadNorm = (string)$val;
            }
        }

        // Marca
        $marca = '';
        if (str_contains($desc, 'COCA') || str_contains($desc, 'COCA-COLA')) {
            $marca = 'coca-cola';
        } elseif (str_contains($desc, 'FANTA')) {
            $marca = 'fanta';
        } elseif (str_contains($desc, 'SPRITE')) {
            $marca = 'sprite';
        } elseif (str_contains($desc, 'ANDINA') || str_contains($desc, 'ADV')) {
            $marca = 'andina del valle';
        } elseif (str_contains($desc, 'MONSTER')) {
            $marca = 'monster';
        } elseif (str_contains($desc, 'POWERADE')) {
            $marca = 'powerade';
        } elseif (str_contains($desc, 'CRISTAL')) {
            $marca = 'cristal';
        } elseif (str_contains($desc, 'ROYAL')) {
            $marca = 'royal guard';
        } elseif (str_contains($desc, 'ESCUDO')) {
            $marca = 'escudo';
        } elseif (str_contains($desc, 'KUNSTMANN')) {
            $marca = 'kunstmann';
        } elseif (str_contains($desc, 'CORONA')) {
            $marca = 'corona';
        }

        // Variante / Sabor
        $sabor = '';
        if (preg_match('/DURAZNO/i', $desc)) $sabor = 'Durazno';
        elseif (preg_match('/NARANJA/i', $desc)) $sabor = 'Naranja';
        elseif (preg_match('/PINA|PIÑA/i', $desc)) $sabor = 'Piña';
        elseif (preg_match('/FRUTILLA/i', $desc)) $sabor = 'Frutilla';
        elseif (preg_match('/MANZANA/i', $desc)) $sabor = 'Manzana';
        elseif (preg_match('/PERA/i', $desc)) $sabor = 'Pera';
        elseif (preg_match('/SOUR/i', $desc)) $sabor = 'Sour';
        elseif (preg_match('/FROZEN(?:\s*BLAST)?/i', $desc)) $sabor = 'Frozen Blast';
        elseif (preg_match('/LIMON|LIMA/i', $desc)) $sabor = 'Lima-Limón';

        $variante = '';
        if (preg_match('/SIN\s*AZUCAR|ZERO/i', $desc)) {
            $variante = 'Sin Azúcar';
        } elseif (preg_match('/LIGHT/i', $desc)) {
            $variante = 'Light';
        }

        // Nombre sugerido limpio para catálogo
        $nombreLimpio = $this->generarNombreLimpio($marca, $variante, $sabor, $formato, $capacidad, $desc);

        return [
            'marca' => $marca,
            'variante' => $variante,
            'sabor' => $sabor,
            'formato' => $formato,
            'capacidad' => $capacidad,
            'capacidad_norm' => $capacidadNorm,
            'empaque_factor' => $empaque,
            'nombre_sugerido' => $nombreLimpio,
        ];
    }

    public function normalizarCapacidad(string $text): string
    {
        $text = strtoupper($text);
        if (preg_match('/(\d+(?:[.,]\d+)?)\s*(L|LT|LTS)/', $text, $m)) {
            return (string)((float)str_replace(',', '.', $m[1]) * 1000);
        }
        if (preg_match('/(\d+)\s*(CC|ML)/', $text, $m)) {
            return (string)(int)$m[1];
        }
        if (preg_match('/(\d{3,4})/', $text, $m)) {
            return (string)(int)$m[1];
        }
        return '';
    }

    protected function generarNombreLimpio(string $marca, string $variante, string $sabor, string $formato, string $capacidad, string $descRaw): string
    {
        $partes = [];
        if ($marca === 'coca-cola') {
            $partes[] = 'Coca-Cola';
            if ($variante) {
                $partes[] = $variante;
            } else {
                $partes[] = 'Original';
            }
        } elseif ($marca === 'andina del valle') {
            $partes[] = 'Andina del Valle';
            if ($sabor) $partes[] = $sabor;
            if ($variante) $partes[] = $variante;
        } elseif ($marca === 'monster') {
            $partes[] = 'Monster Energy';
            if ($sabor) $partes[] = $sabor;
            elseif ($variante) $partes[] = $variante;
            else $partes[] = 'Clásica';
        } elseif ($marca === 'powerade') {
            $partes[] = 'Powerade';
            if ($sabor) $partes[] = $sabor;
            if ($variante) $partes[] = $variante;
        } else {
            // Capitalizar descripción cruda quitando empaques
            $limpio = preg_replace('/X\s*\d+/i', '', $descRaw);
            $limpio = preg_replace('/\s+/', ' ', trim($limpio));
            return ucwords(strtolower($limpio));
        }

        if ($formato === 'Botella Vidrio') {
            $partes[] = 'Vidrio';
        } elseif ($formato === 'Botella Retornable') {
            $partes[] = 'Retornable';
        }

        $partes[] = $capacidad;

        return implode(' ', $partes);
    }
}
