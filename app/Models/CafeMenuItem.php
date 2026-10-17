<?php

namespace App\Models;

use Database\Factories\CafeMenuItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'name',
    'slug',
    'category',
    'description',
    'price_minor',
    'currency',
    'media_id',
    'is_available',
    'is_published',
    'position',
])]
class CafeMenuItem extends Model
{
    /** @use HasFactory<CafeMenuItemFactory> */
    use HasFactory;

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
