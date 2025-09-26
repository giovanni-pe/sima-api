<?php

namespace Modules\SensorReadings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSensorReadingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'sensor_id' => ['sometimes','integer','exists:sensors,id'],
            'timestamp' => ['sometimes','date'],
            'value' => ['sometimes','numeric'],
            '2)' => ['sometimes','sometimes'],
            'unit' => ['sometimes','string','max:255','nullable'],
        ];
    }
}