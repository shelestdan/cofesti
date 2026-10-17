<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Tests\TestCase;

class CatalogPresentationTest extends TestCase
{
    public function test_product_page_renders_related_product_cards_with_categories(): void
    {
        $category = Category::factory()->create(['name' => 'Кофе']);
        $product = Product::factory()->hasAttached($category)->create(['name' => 'Основной кофе']);
        Product::factory()->count(2)->hasAttached($category)->sequence(
            ['name' => 'Кофе для утра'],
            ['name' => 'Кофе для вечера'],
        )->create();

        $response = $this->get(route('catalog.show', $product));

        $response->assertOk()
            ->assertSeeText('Основной кофе')
            ->assertSeeText('Кофе для утра')
            ->assertSeeText('Кофе для вечера');
    }

    public function test_unknown_price_is_presented_as_pending_instead_of_free(): void
    {
        $product = Product::factory()->create(['price_minor' => 0, 'is_purchasable' => false]);

        $response = $this->get(route('catalog.show', $product));

        $response->assertSeeText('Цена уточняется')
            ->assertDontSeeText('0 ₽');
    }
}
