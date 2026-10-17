<?php

namespace App\Models;

use Database\Factories\PageBlockFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['page_id', 'type', 'data', 'position', 'is_visible'])]
class PageBlock extends Model
{
    /** @use HasFactory<PageBlockFactory> */
    use HasFactory;

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_visible' => 'boolean',
        ];
    }
}
