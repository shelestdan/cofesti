<?php

namespace App\Models;

use App\OrderStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'number',
    'idempotency_key',
    'status',
    'customer_name',
    'customer_phone',
    'customer_email',
    'delivery_method',
    'payment_method',
    'customer_comment',
    'internal_note',
    'subtotal_minor',
    'total_minor',
    'currency',
    'notification_sent_at',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'notification_sent_at' => 'datetime',
        ];
    }
}
