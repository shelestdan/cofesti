<?php

namespace App\Http\Requests;

use App\Models\ProductVariant;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['product_id', 'product_variant_id'])) {
                    return;
                }

                $variantId = $this->integer('product_variant_id');

                if ($variantId === 0) {
                    return;
                }

                $variantBelongsToProduct = ProductVariant::query()
                    ->whereKey($variantId)
                    ->where('product_id', $this->integer('product_id'))
                    ->exists();

                if (! $variantBelongsToProduct) {
                    $validator->errors()->add('product_variant_id', 'Выбранный вариант не относится к товару.');
                }
            },
        ];
    }
}
