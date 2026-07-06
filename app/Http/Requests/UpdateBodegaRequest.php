<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBodegaRequest extends FormRequest
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
        $bodegaId = $this->route('bodega')->id ?? $this->route('bodega');
        return [
            'codigo' => 'required|string|max:20|unique:bodegas,codigo,' . $bodegaId,
            'nombre' => 'required|string|max:100|unique:bodegas,nombre,' . $bodegaId,
            'descripcion' => 'nullable|string',
            'es_refrigerada' => 'boolean',
            'activa' => 'boolean',
        ];
    }
}
