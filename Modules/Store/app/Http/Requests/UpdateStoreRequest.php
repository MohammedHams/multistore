<?php

namespace Modules\Store\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'domain' => [
                'sometimes', 
                'required', 
                'string', 
                'max:255',
                Rule::unique('stores', 'domain')->ignore($this->route('store'))
            ],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255'],
            'phone' => ['sometimes', 'required', 'string', 'max:20'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],
            'settings' => ['nullable', 'array'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes()
    {
        return [
            'name' => __('Store Name'),
            'domain' => __('Subdomain'),
            'email' => __('Email Address'),
            'phone' => __('Phone Number'),
            'logo' => __('Store Logo'),
            'is_active' => __('Active Status'),
            'settings' => __('Store Settings'),
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'domain.unique' => __('This subdomain is already in use. Please choose another one.'),
        ];
    }
}
