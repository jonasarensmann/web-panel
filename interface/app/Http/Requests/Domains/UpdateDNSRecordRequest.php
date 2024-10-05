<?php

namespace App\Http\Requests\Domains;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDNSRecordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->route('domain')->user_id === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['string', 'required'],
            'name' => ['string', 'required', 'max:50'],
            'value' => ['string', 'required'],
        ];
    }
}
