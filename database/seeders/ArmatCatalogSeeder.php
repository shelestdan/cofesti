<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\StockStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ArmatCatalogSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $assam = Product::query()->where('slug', 'chaj-chjornyj-assam-250-gr')->firstOrFail();
            $sencha = Product::query()->where('slug', 'sencha')->firstOrFail();
            $coffeeCategory = Category::query()->where('slug', 'kofe')->firstOrFail();
            $mogiana = Product::query()->firstOrCreate(
                ['slug' => 'armat-brazil-mogiana-espresso-1kg'],
                [
                    'name' => 'Бразилия Можиана Эспрессо ARMAT, 1 кг',
                    'price_minor' => 0,
                    'stock_status' => StockStatus::Unknown,
                    'is_purchasable' => false,
                    'is_published' => true,
                    'metadata' => ['has_options' => false],
                ],
            );
            $mogiana->categories()->syncWithoutDetaching([$coffeeCategory->id]);

            $this->attachPhotograph($assam, 'armat-assam.jpg');
            $this->attachPhotograph($sencha, 'armat-sencha.jpg');
            $this->attachPhotograph($mogiana, 'armat-coffee.jpg');
        });
    }

    private function attachPhotograph(Product $product, string $filename): void
    {
        $source = public_path('images/catalog/'.$filename);
        $path = 'catalog/armat/'.$filename;

        if (! Storage::disk('public')->put($path, File::get($source))) {
            throw new \RuntimeException('Не удалось сохранить фотографию '.$filename);
        }

        $media = Media::query()->updateOrCreate(['path' => $path], [
            'disk' => 'public',
            'filename' => $filename,
            'alt_text' => $product->name,
            'mime_type' => 'image/jpeg',
            'byte_size' => File::size($source),
            'width' => 1024,
            'height' => 1280,
            'metadata' => ['presentation' => 'photograph'],
        ]);

        foreach ($product->media()->where('media.id', '!=', $media->id)->get() as $position => $previous) {
            $product->media()->updateExistingPivot($previous->id, ['position' => $position + 1, 'is_primary' => false]);
        }

        $product->media()->syncWithoutDetaching([$media->id => ['position' => 0, 'is_primary' => true]]);
    }
}
