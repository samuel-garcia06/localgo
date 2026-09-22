<?php

namespace App\Http\Requests;

use App\Support\InputSanitizer;
use Illuminate\Foundation\Http\FormRequest;

class CreateStripeCheckoutSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_public_id' => ['required', 'uuid'],
            'checkout_token' => ['required', 'string', 'min:32', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'order_public_id' => InputSanitizer::text($this->input('order_public_id'), 36),
            'checkout_token' => InputSanitizer::text($this->input('checkout_token'), 255),
        ]);
    }
}
