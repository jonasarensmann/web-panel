<?php

namespace App\Http\Requests\Domains;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCaddyconfigRequest extends FormRequest
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
            'port' => 'required|integer',
            'file_server' => 'boolean',
            'browse' => 'required_if:file_server,true',
            'php' => 'required_if:file_server,true',
            'compression' => 'boolean',
            'reverse_proxy' => 'boolean',
            'reverse_proxy_location' => 'required_if:reverse_proxy,true',
            'static_response' => 'boolean',
            'static_response_text' => 'required_if:static_response,true',
        ];
    }
}
