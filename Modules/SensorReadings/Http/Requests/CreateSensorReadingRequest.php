<?php

namespace Modules\SensorReadings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateSensorReadingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'sensor_id' => ['required','integer','exists:sensors,id'],
            'timestamp' => ['required','date'],
            'value' => ['required','numeric'],
            '2)' => ['required','sometimes'],
            'unit' => ['sometimes','string','max:255','nullable'],
        ];
    }
}