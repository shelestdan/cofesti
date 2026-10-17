<?php

namespace App\Models;

use App\InquiryStatus;
use Database\Factories\InquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'type',
    'status',
    'name',
    'phone',
    'email',
    'message',
    'internal_note',
    'processed_at',
])]
class Inquiry extends Model
{
    /** @use HasFactory<InquiryFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => InquiryStatus::class,
            'processed_at' => 'datetime',
        ];
    }
}
