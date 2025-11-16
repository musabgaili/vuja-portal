<?php

namespace App\Enums;

enum UserRole: string
{
    case CLIENT = 'client';
    case EMPLOYEE = 'employee';
    case MANAGER = 'manager';

    public function label(): string
    {
        return match($this) {
            self::CLIENT => 'Client',
            self::EMPLOYEE => 'Employee',
            self::MANAGER => 'Manager',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::CLIENT => 'External client using the platform',
            self::EMPLOYEE => 'Internal team member',
            self::MANAGER => 'Internal manager with full oversight',
        };
    }

    public function isInternal(): bool
    {
        return in_array($this, [self::EMPLOYEE, self::MANAGER]);
    }

    public function isClient(): bool
    {
        return $this === self::CLIENT;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
