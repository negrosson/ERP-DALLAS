<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCatalogoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sku' => 'required|string|max:50|unique:catalogo_productos,sku',
            'nombre' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'unidad_medida' => 'required|string|max:10',
            'precio_compra_ref' => 'required|numeric|min:0|max:99999999.99',
            'precio_venta' => 'required|numeric|min:0|max:99999999.99',
            'constante_vencimiento_meses' => 'nullable|integer|min:1|max:120',
            'activo' => 'boolean',
        ];
    }
}
