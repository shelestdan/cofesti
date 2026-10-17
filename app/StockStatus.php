<?php

namespace App;

enum StockStatus: string
{
    case Available = 'available';
    case OutOfStock = 'out_of_stock';
    case Unknown = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'В наличии',
            self::OutOfStock => 'Нет в наличии',
            self::Unknown => 'Не подтверждено',
        };
    }
}
