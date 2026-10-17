<?php

namespace Tests\Feature;

use App\Actions\SyncPublicCatalog;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SyncPublicCatalogTest extends TestCase
{
    public function test_public_catalog_import_is_repeatable_without_duplicates(): void
    {
        config(['services.cofesti_import.base_url' => 'https://catalog.example.test/wp-json/wc/store/v1']);
        Http::preventStrayRequests();
        Http::fake([
            'https://catalog.example.test/wp-json/wc/store/v1/products/categories*' => Http::response([
                ['id' => 33, 'name' => 'Кофе', 'slug' => 'kofe', 'parent' => 0],
            ]),
            'https://catalog.example.test/wp-json/wc/store/v1/products*' => Http::response([
                [
                    'id' => 5230,
                    'name' => 'Бразилия Сантос',
                    'permalink' => 'https://source.example.test/shop/kofe/brazilija-santos/',
                    'sku' => 'BR-250',
                    'type' => 'simple',
                    'short_description' => '<p>Свежеобжаренный кофе</p>',
                    'description' => '<p>Шоколад и карамель.</p>',
                    'prices' => ['price' => '37500', 'currency_code' => 'RUB'],
                    'categories' => [['id' => 33]],
                    'attributes' => [],
                    'variations' => [],
                    'images' => [],
                    'has_options' => false,
                    'is_purchasable' => true,
                    'is_in_stock' => true,
                ],
            ], 200, ['X-WP-TotalPages' => '1']),
        ]);

        $firstReport = app(SyncPublicCatalog::class)->handle(false, 50, 0);
        $secondReport = app(SyncPublicCatalog::class)->handle(false, 50, 0);

        $this->assertSame(1, $firstReport['products']);
        $this->assertSame(1, $secondReport['products']);
        $this->assertDatabaseCount('categories', 1);
        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseHas('products', [
            'source_id' => 5230,
            'slug' => 'brazilija-santos',
            'price_minor' => 37500,
        ]);
    }
}
