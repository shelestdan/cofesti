<?php

namespace App\Models;

use App\StockStatus;
use Database\Factories\ProductVariantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'product_id',
    'source_id',
    'sku',
    'name',
    'attributes',
    'price_minor',
    'regular_price_minor',
    'stock_status',
    'is_purchasable',
    'is_active',
    'position',
])]
class ProductVariant extends Model
{
    /** @use HasFactory<ProductVariantFactory> */
    use HasFactory;

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function casts(): array
    {
        return [
            'attributes' => 'array',
            'stock_status' => StockStatus::class,
            'is_purchasable' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
