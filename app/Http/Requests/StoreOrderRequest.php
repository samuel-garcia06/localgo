<?php

namespace App\Http\Requests;

use App\Enums\DeliveryType;
use App\Enums\PaymentMethod;
use App\Support\InputSanitizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreOrderRequest extends FormRequest
{
    private const PHONE_RULE = 'regex:/^(?=(?:\D*\d){9,15}\D*$)\+?[0-9\s().-]+$/';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'min:2', 'max:120', 'regex:/^[\pL\pN\s.\'’-]+$/u'],
            'customer_phone' => ['required', 'string', 'max:30', self::PHONE_RULE],
            'customer_email' => ['required', 'email:rfc', 'max:160'],
            'delivery_type' => ['required', Rule::enum(DeliveryType::class)],
            'customer_address' => ['nullable', 'string', 'min:5', 'max:255'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1', 'max:25'],
            'items.*.product_id' => ['required', 'integer', 'min:1', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'items.*.drink_choice' => ['nullable', 'string', 'max:120'],
            'items.*.sauce_choice' => ['nullable', 'string', 'max:120'],
            'items.*.drink_choices' => ['nullable', 'array', 'max:5'],
            'items.*.drink_choices.*' => ['nullable', 'string', 'max:120'],
            'items.*.sauce_choices' => ['nullable', 'array', 'max:5'],
            'items.*.sauce_choices.*' => ['nullable', 'string', 'max:120'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_phone.regex' => 'Introduce un telefono valido, por ejemplo +34 612 345 678.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $items = collect($this->input('items', []))
            ->filter(fn ($item) => is_array($item))
            ->map(function (array $item): array {
                return [
                    'product_id' => isset($item['product_id']) ? (int) $item['product_id'] : null,
                    'quantity' => isset($item['quantity']) ? (int) $item['quantity'] : null,
                    'drink_choice' => InputSanitizer::choices($item['drink_choice'] ?? null),
                    'sauce_choice' => InputSanitizer::choices($item['sauce_choice'] ?? null),
                    'drink_choices' => InputSanitizer::choices($item['drink_choices'] ?? []),
                    'sauce_choices' => InputSanitizer::choices($item['sauce_choices'] ?? []),
                ];
            })
            ->values()
            ->all();

        $this->merge([
            'customer_name' => preg_replace(
                '/\s+/u',
                ' ',
                trim((string) preg_replace('/[^\pL\pN\s.\'’-]+/u', '', InputSanitizer::text($this->input('customer_name'), 120) ?? ''))
            ),
            'customer_phone' => InputSanitizer::phone($this->input('customer_phone'), 30),
            'customer_email' => mb_strtolower(InputSanitizer::text($this->input('customer_email'), 160) ?? ''),
            'customer_address' => InputSanitizer::text($this->input('customer_address'), 255),
            'notes' => InputSanitizer::text($this->input('notes'), 500),
            'items' => $items,
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (
                $this->input('delivery_type') === DeliveryType::Delivery->value
                && blank($this->input('customer_address'))
            ) {
                $validator->errors()->add('customer_address', 'La direccion es obligatoria para pedidos a domicilio.');
            }
        });
    }
}
