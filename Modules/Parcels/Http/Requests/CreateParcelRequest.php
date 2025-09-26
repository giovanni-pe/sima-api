<?php

namespace Modules\Parcels\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateParcelRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'      => ['required','string','max:120'],
            'location'  => ['nullable','string','max:255'],
            'area_m2'   => ['required','numeric','min:0'],
            'user_id'   => ['nullable','integer','exists:users,id'],
            'latitude'  => ['nullable','numeric','between:-90,90'],
            'longitude' => ['nullable','numeric','between:-180,180'],
            'crop_type' => ['nullable','string','max:100'],
            'active'    => ['sometimes','boolean'],
        ];
    }
}
