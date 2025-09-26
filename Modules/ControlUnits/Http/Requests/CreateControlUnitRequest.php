<?php

namespace Modules\ControlUnits\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateControlUnitRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'serial_code'       => ['required','string','max:255'],
            'model'             => ['required','string','max:255'],
            'installed_at'      => ['sometimes','nullable','date'], // mejor que string
            'status'            => ['required','string','in:online,offline,maintenance'],
            'parcel_id'         => ['required','integer','exists:parcels,id'],
            'mqtt_client_id'    => ['required','string','max:255'],
            'mqtt_username'     => ['sometimes','nullable','string','max:255'],
            'mqtt_password_enc' => ['sometimes','nullable','string','max:255'],
            'status_topic'      => ['sometimes','nullable','string','max:255'],
            'lwt_topic'         => ['sometimes','nullable','string','max:255'],
            'last_seen_at'      => ['sometimes','nullable','date'], // mejor que string
            'active'            => ['required','boolean'],
        ];
    }

    // Nombres “bonitos” de atributos
    public function attributes(): array
    {
        return [
            'serial_code'       => 'código de serie',
            'installed_at'      => 'fecha de instalación',
            'mqtt_client_id'    => 'ID de cliente MQTT',
            'mqtt_password_enc' => 'contraseña MQTT (encriptada)',
            'status_topic'      => 'tópico de estado',
            'lwt_topic'         => 'tópico LWT',
            'last_seen_at'      => 'última conexión',
            'parcel_id'         => 'parcela',
        ];
    }

    // Mensajes personalizados
    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'string'   => 'El campo :attribute debe ser texto.',
            'max'      => 'El campo :attribute no debe exceder :max caracteres.',
            'integer'  => 'El campo :attribute debe ser un número entero.',
            'exists'   => 'El :attribute seleccionado no existe.',
            'boolean'  => 'El campo :attribute debe ser verdadero o falso.',
            'date'     => 'El campo :attribute debe ser una fecha válida.',
            'in'       => 'El campo :attribute debe ser uno de: :values.',
        ];
    }

    // Forzar respuesta JSON uniforme (útil para APIs)
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validación fallida',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }

    // Normaliza inputs antes de validar (opcional)
    protected function prepareForValidation(): void
    {
        $this->merge([
            'active' => filter_var($this->input('active', true), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
        ]);
    }
}
