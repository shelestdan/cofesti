<?php

namespace Tests\Feature;

use App\Actions\CreateOrder;
use App\Models\Order;
use App\Models\Product;
use App\StockStatus;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    public function test_valid_checkout_creates_order_with_server_calculated_snapshot(): void
    {
        $product = Product::factory()->create([
            'name' => 'Кофе для теста',
            'price_minor' => 42500,
            'stock_status' => StockStatus::Available,
            'is_purchasable' => true,
            'is_published' => true,
            'metadata' => [],
        ]);

        $response = $this->withSession(['cart' => $this->cart($product)])
            ->post(route('checkout.store'), $this->customer('39163cb9-44a6-485b-8d34-cd2a3baf1b12'));

        $response->assertRedirect(route('checkout.success'));
        $this->assertDatabaseHas('orders', [
            'idempotency_key' => '39163cb9-44a6-485b-8d34-cd2a3baf1b12',
            'customer_name' => 'Анна Тестова',
            'subtotal_minor' => 85000,
            'total_minor' => 85000,
        ]);
        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'product_name' => 'Кофе для теста',
            'quantity' => 2,
            'unit_price_minor' => 42500,
            'line_total_minor' => 85000,
        ]);
    }

    public function test_repeated_idempotency_key_returns_existing_order_without_creating_duplicate(): void
    {
        $product = Product::factory()->create([
            'price_minor' => 38000,
            'stock_status' => StockStatus::Available,
            'is_purchasable' => true,
            'is_published' => true,
            'metadata' => [],
        ]);
        $customer = $this->customer('e75e60a0-0c37-41a6-b05e-55fc92fc6067');

        app(CreateOrder::class)->handle($customer, $this->cart($product));

        $response = $this->withSession(['cart' => $this->cart($product)])
            ->post(route('checkout.store'), $customer);

        $response->assertRedirect(route('checkout.success'));
        $this->assertSame(1, Order::query()->where('idempotency_key', $customer['idempotency_key'])->count());
    }

    /**
     * @return array<string, array{product_id: int, product_variant_id: null, quantity: int}>
     */
    private function cart(Product $product): array
    {
        return [
            $product->id.':base' => [
                'product_id' => $product->id,
                'product_variant_id' => null,
                'quantity' => 2,
            ],
        ];
    }

    /**
     * @return array{customer_name: string, customer_phone: string, customer_email: string, idempotency_key: string, privacy_accepted: bool}
     */
    private function customer(string $idempotencyKey): array
    {
        return [
            'customer_name' => 'Анна Тестова',
            'customer_phone' => '+79990000000',
            'customer_email' => 'anna@example.test',
            'idempotency_key' => $idempotencyKey,
            'privacy_accepted' => true,
        ];
    }
}
