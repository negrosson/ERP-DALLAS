<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRecepcionDetalleRequest extends FormRequest
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
            'catalogo_producto_id' => 'required|exists:catalogo_productos,id',
            'cantidad_recibida' => 'required|numeric|min:0.01|max:9999999.99',
            'precio_unitario' => 'required|numeric|min:0|max:99999999.99',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:today',
        ];
    }
}
