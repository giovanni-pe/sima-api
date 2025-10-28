<?php

namespace Modules\Sensors\Http\Requests;

use Modules\Core\Http\BaseApiRequest;

class UpdateSensorRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $payload = [];

        if ($this->has('name') && is_string($this->name)) {
            $payload['name'] = trim($this->name);
        }

        if ($this->has('type') && is_string($this->type)) {
            $payload['type'] = trim($this->type);
        }

        if ($this->has('control_unit_id')) {
            $payload['control_unit_id'] = $this->filled('control_unit_id')
                ? (int) $this->control_unit_id
                : null;
        }

        if ($this->has('active')) {
            $payload['active'] = filter_var($this->active, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        if (!empty($payload)) {
            $this->merge($payload);
        }
    }

    public function rules(): array
    {
        return [
            'name'            => ['bail','sometimes','string','max:255'],
            'type'            => ['bail','sometimes','string','max:255'],
            'active'          => ['bail','sometimes','boolean'],
            'control_unit_id' => ['bail','sometimes','nullable','integer','exists:control_units,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name'            => 'nombre',
            'type'            => 'tipo',
            'active'          => 'activo',
            'control_unit_id' => 'unidad de control',
        ];
    }

    public function messages(): array
    {
        return [
            // genéricos
            'required'    => 'El :attribute es obligatorio.',
            'string'      => 'El :attribute debe ser un texto.',
            'max.string'  => 'El :attribute no debe superar :max caracteres.',
            'boolean'     => 'El :attribute debe ser verdadero o falso.',
            'integer'     => 'El :attribute debe ser un número entero.',
            'exists'      => 'La :attribute seleccionada no existe.',

            // específicos
            'control_unit_id.integer' => 'La unidad de control debe ser un ID válido.',
            'control_unit_id.exists'  => 'La unidad de control seleccionada no existe o fue eliminada.',
        ];
    }
}
