<?php

namespace Modules\Sensors\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\Http\BaseApiRequest;

class CreateSensorRequest extends BaseApiRequest
{


    protected function prepareForValidation(): void
    {
        $this->merge([
            'name'            => is_string($this->name) ? trim($this->name) : $this->name,
            'type'            => is_string($this->type) ? trim($this->type) : $this->type,
            'control_unit_id' => $this->filled('control_unit_id')
                ? (int) $this->control_unit_id
                : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name'            => ['bail', 'required', 'string', 'max:255'],
            'type'            => ['bail', 'required', 'string', 'max:255'],
            'control_unit_id' => ['bail', 'nullable', 'integer', 'exists:control_units,id'],
        ];
    }
    public function attributes(): array
    {
        return [
            'name'            => 'nombre',
            'type'            => 'tipo',
            'control_unit_id' => 'unidad de control',
        ];
    }
    public function messages(): array
    {
        return [
            // Genéricos
            'required'    => 'El :attribute es obligatorio.',
            'string'      => 'El :attribute debe ser un texto.',
            'max.string'  => 'El :attribute no debe superar :max caracteres.',
            'integer'     => 'El :attribute debe ser un número entero.',
            'exists'      => 'La :attribute seleccionada no existe.',
            'name.required' => 'Ingresa un nombre.',
            'type.required' => 'Ingresa el tipo del sensor.',

            'control_unit_id.integer' => 'La unidad de control debe ser un ID válido.',
            'control_unit_id.exists'  => 'La unidad de control seleccionada no existe o fue eliminada.',
        ];
    }
}
