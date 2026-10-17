<?php

namespace App;

enum OrderStatus: string
{
    case New = 'new';
    case Processing = 'processing';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Новый',
            self::Processing => 'В работе',
            self::Completed => 'Завершён',
            self::Cancelled => 'Отменён',
        };
    }
}
