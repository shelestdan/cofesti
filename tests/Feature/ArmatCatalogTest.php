<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use Database\Seeders\ArmatCatalogSeeder;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArmatCatalogTest extends TestCase
{
    public function test_brand_photographs_replace_primary_media_without_changing_prices_or_losing_previous_images(): void
    {
        Storage::fake('public');
        Category::factory()->create(['slug' => 'kofe']);
        $previous = Media::factory()->create(['path' => 'catalog/previous-assam.jpg']);
        $assam = Product::factory()->hasAttached($previous, ['position' => 0, 'is_primary' => true], 'media')->create([
            'slug' => 'chaj-chjornyj-assam-250-gr',
            'price_minor' => 43900,
        ]);
        Product::factory()->create(['slug' => 'sencha', 'price_minor' => 72000]);

        $this->seed(ArmatCatalogSeeder::class);
        $this->seed(ArmatCatalogSeeder::class);

        $this->get(route('catalog.show', $assam))->assertSee('storage/catalog/armat/armat-assam.jpg', false);
        $this->assertDatabaseHas('products', ['id' => $assam->id, 'price_minor' => 43900]);
        $this->assertDatabaseHas('products', ['slug' => 'sencha', 'price_minor' => 72000]);
        $this->assertDatabaseHas('product_media', ['product_id' => $assam->id, 'media_id' => $previous->id, 'is_primary' => false, 'position' => 1]);
        $this->assertDatabaseCount('products', 3);
        $this->assertDatabaseCount('media', 4);
        Storage::disk('public')->assertExists([
            'catalog/armat/armat-assam.jpg',
            'catalog/armat/armat-sencha.jpg',
            'catalog/armat/armat-coffee.jpg',
        ]);
    }
}
