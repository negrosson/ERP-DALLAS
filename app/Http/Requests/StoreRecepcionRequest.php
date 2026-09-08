<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRecepcionRequest extends FormRequest
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
            'proveedor_id' => 'required',
            'bodega_id' => 'required|exists:bodegas,id',
            'numero_factura' => [
                'nullable',
                'string',
                'max:50',
                // Si viene un número de factura, debe ser único en la BD (excepto 'S/N')
                function ($attribute, $value, $fail) {
                    if ($value && strtoupper($value) !== 'S/N') {
                        $existe = \App\Models\Recepcion::whereRaw('LOWER(numero_factura) = ?', [strtolower($value)])
                            ->withTrashed() // Busca incluso en papelera
                            ->exists();
                        if ($existe) {
                            $fail('Esta factura (Nº ' . $value . ') ya fue registrada en el sistema. Verifica si ya fue ingresada para evitar duplicados.');
                        }
                    }
                }
            ],
            'observaciones' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'proveedor_id.required' => 'Debe seleccionar un proveedor.',
            'bodega_id.required' => 'Debe seleccionar una bodega de destino.',
            'bodega_id.exists' => 'La bodega seleccionada no es válida.',
        ];
    }
}
