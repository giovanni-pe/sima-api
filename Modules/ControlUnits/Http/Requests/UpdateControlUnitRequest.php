<?php

namespace Modules\ControlUnits\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateControlUnitRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'serial_code' => ['sometimes','string','max:255'],
            'model' => ['sometimes','string','max:255'],
            'installed_at' => ['sometimes','string','max:255','nullable'],
            'status' => ['sometimes','string','max:255'],
            'parcel_id' => ['sometimes','integer','exists:parcels,id'],
            'mqtt_client_id' => ['sometimes','string','max:255'],
            'mqtt_username' => ['sometimes','string','max:255','nullable'],
            'mqtt_password_enc' => ['sometimes','string','max:255','nullable'],
            'status_topic' => ['sometimes','string','max:255','nullable'],
            'lwt_topic' => ['sometimes','string','max:255','nullable'],
            'last_seen_at' => ['sometimes','string','max:255','nullable'],
            'active' => ['sometimes','boolean'],
        ];
    }
}