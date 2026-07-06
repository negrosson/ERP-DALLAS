<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBodegaRequest extends FormRequest
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
            'codigo' => 'required|string|max:20|unique:bodegas,codigo',
            'nombre' => 'required|string|max:100|unique:bodegas,nombre',
            'descripcion' => 'nullable|string',
            'es_refrigerada' => 'boolean',
            'activa' => 'boolean',
        ];
    }
}
