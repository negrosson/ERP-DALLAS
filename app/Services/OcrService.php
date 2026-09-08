<?php

namespace App\Services;

use App\Contracts\OcrProviderInterface;
use Illuminate\Http\UploadedFile;

class OcrService
{
    protected OcrProviderInterface $provider;

    /**
     * Inyectamos la interfaz del proveedor OCR.
     * Laravel resolverá qué clase instanciar basado en AppServiceProvider.
     */
    public function __construct(OcrProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Procesa una imagen de factura usando el proveedor OCR configurado.
     */
    public function processInvoiceImage(UploadedFile $file, $proveedorId): array
    {
        // Obtener el proveedor para enviarlo como contexto a la interfaz
        $proveedor = \App\Models\Proveedor::find($proveedorId);
        $proveedorNombre = $proveedor ? $proveedor->nombre : '';

        // Delegar la extracción de datos al proveedor configurado (Mindee, Mock, etc.)
        return $this->provider->extractInvoiceData($file, $proveedorNombre);
    }
}
