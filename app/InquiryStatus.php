<?php

namespace App;

enum InquiryStatus: string
{
    case New = 'new';
    case Processing = 'processing';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Новая',
            self::Processing => 'В работе',
            self::Closed => 'Закрыта',
        };
    }
}
