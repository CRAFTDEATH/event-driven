<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // product is an array of items
            'product' => 'required|array',
            'product.*.name' => 'required|string',
            'product.*.quantity' => 'required|numeric',
            'product.*.price' => 'required|numeric',

            // client is an object
            'client' => 'required|array',
            'client.name' => 'required|string',
            'client.cpf' => 'required|string',
            'client.email' => 'required|string',
            'client.phone' => 'required|string',

            // address inside client
            'client.address' => 'required|array',
            'client.address.neighborhood' => 'required|string',
            'client.address.street' => 'required|string',
            'client.address.number' => 'required|string',
            'client.address.complement' => 'nullable|string',
            'client.address.city' => 'required|string',
            'client.address.state' => 'required|string',
            'client.address.zipcode' => 'required|string',
        ];
    }
}
