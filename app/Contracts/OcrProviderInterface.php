<?php

namespace App\Contracts;

use Illuminate\Http\UploadedFile;

interface OcrProviderInterface
{
    /**
     * Extrae los datos de una factura y devuelve un array estructurado de ítems.
     *
     * @param UploadedFile $file El archivo de la factura (imagen o PDF).
     * @param string $proveedorNombre Nombre del proveedor para contexto (opcional).
     * @return array Arreglo de ítems con 'codigo', 'descripcion', 'cantidad', 'precio_unitario'.
     */
    public function extractInvoiceData(UploadedFile $file, string $proveedorNombre): array;
}
