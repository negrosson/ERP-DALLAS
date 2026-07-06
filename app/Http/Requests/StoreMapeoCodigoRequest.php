<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMapeoCodigoRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'proveedor_id' => 'required|exists:proveedores,id',
            'catalogo_producto_id' => [
                'required',
                'exists:catalogo_productos,id',
                \Illuminate\Validation\Rule::unique('mapeo_codigos', 'catalogo_producto_id')
                    ->where('proveedor_id', $this->proveedor_id)
            ],
            'codigo_proveedor' => [
                'required',
                'string',
                'max:50',
                \Illuminate\Validation\Rule::unique('mapeo_codigos', 'codigo_proveedor')
                    ->where('proveedor_id', $this->proveedor_id)
            ],
            'factor_conversion' => 'required|numeric|min:0.01|max:9999.99',
            'descripcion_proveedor' => 'nullable|string|max:200',
        ];
    }
}
