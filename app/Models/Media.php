<?php

namespace App\Models;

use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'source_id',
    'source_url',
    'disk',
    'path',
    'filename',
    'alt_text',
    'mime_type',
    'byte_size',
    'width',
    'height',
    'metadata',
])]
class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory;

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_media')
            ->withPivot(['position', 'is_primary'])
            ->withTimestamps();
    }

    public function cafeMenuItems(): HasMany
    {
        return $this->hasMany(CafeMenuItem::class);
    }

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }
}
