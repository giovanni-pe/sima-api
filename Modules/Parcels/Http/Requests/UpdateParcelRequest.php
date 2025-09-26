<?php

namespace Modules\Parcels\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateParcelRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'      => ['sometimes','string','max:120'],
            'location'  => ['sometimes','nullable','string','max:255'],
            'area_m2'   => ['sometimes','numeric','min:0'],
            'user_id'   => ['sometimes','nullable','integer','exists:users,id'],
            'latitude'  => ['sometimes','nullable','numeric','between:-90,90'],
            'longitude' => ['sometimes','nullable','numeric','between:-180,180'],
            'crop_type' => ['sometimes','nullable','string','max:100'],
            'active'    => ['sometimes','boolean'],
        ];
    }
}
