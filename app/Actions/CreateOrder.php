<?php

namespace App\Actions;

use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CreateOrder
{
    public function __construct(private ResolveCart $resolveCart) {}

    /**
     * @param  array{customer_name: string, customer_phone: string, customer_email?: string|null, delivery_method?: string|null, payment_method?: string|null, customer_comment?: string|null, idempotency_key: string}  $customer
     * @param  array<string, array{product_id: int, product_variant_id: int|null, quantity: int}>  $cart
     */
    public function handle(array $customer, array $cart): Order
    {
        $existingOrder = Order::query()->where('idempotency_key', $customer['idempotency_key'])->first();

        if ($existingOrder !== null) {
            return $existingOrder->load('items');
        }

        return DB::transaction(function () use ($customer, $cart): Order {
            $existingOrder = Order::query()->where('idempotency_key', $customer['idempotency_key'])->first();

            if ($existingOrder !== null) {
                return $existingOrder->load('items');
            }

            $lines = $this->resolveCart->handle($cart, lockProducts: true);

            if ($lines->isEmpty() || $lines->count() !== count($cart)) {
                throw new InvalidArgumentException('Состав корзины изменился. Обновите её и повторите оформление.');
            }

            $subtotalMinor = $lines->sum('line_total_minor');
            $order = Order::query()->firstOrCreate([
                'idempotency_key' => $customer['idempotency_key'],
            ], [
                'number' => 'CF-'.Str::upper(str_replace('-', '', $customer['idempotency_key'])),
                'idempotency_key' => $customer['idempotency_key'],
                'customer_name' => $customer['customer_name'],
                'customer_phone' => $customer['customer_phone'],
                'customer_email' => $customer['customer_email'] ?? null,
                'delivery_method' => $customer['delivery_method'] ?? null,
                'payment_method' => $customer['payment_method'] ?? null,
                'customer_comment' => $customer['customer_comment'] ?? null,
                'subtotal_minor' => $subtotalMinor,
                'total_minor' => $subtotalMinor,
                'currency' => 'RUB',
            ]);

            if (! $order->wasRecentlyCreated) {
                return $order->load('items');
            }

            foreach ($lines as $line) {
                $variant = $line['variant'];
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'product_variant_id' => $variant?->id,
                    'product_name' => $line['product']->name,
                    'product_sku' => $variant?->sku ?? $line['product']->sku,
                    'variant_name' => $this->variantName($variant),
                    'variant_attributes' => $variant?->attributes,
                    'unit_price_minor' => $line['unit_price_minor'],
                    'quantity' => $line['quantity'],
                    'line_total_minor' => $line['line_total_minor'],
                ]);
            }

            return $order->load('items');
        });
    }

    private function variantName(?ProductVariant $variant): ?string
    {
        if ($variant === null) {
            return null;
        }

        if ($variant->name !== null) {
            return $variant->name;
        }

        $values = array_filter($variant->attributes, fn (?string $value): bool => $value !== null);

        return $values === [] ? null : implode(', ', $values);
    }
}
