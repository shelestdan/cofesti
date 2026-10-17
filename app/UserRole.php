<?php

namespace App;

enum UserRole: string
{
    case Administrator = 'administrator';
    case Manager = 'manager';

    public function label(): string
    {
        return match ($this) {
            self::Administrator => 'Администратор',
            self::Manager => 'Менеджер',
        };
    }
}
