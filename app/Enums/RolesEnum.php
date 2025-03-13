<?php

namespace App\Enums;

enum RolesEnum: string
{
    case Admin = 'Admin';
    case Vendor = 'Vendor';
    case User = 'User';

    public static function labels(): array
    {
        return [
            self::Admin->value => __('Admin'),
            self::Vendor->value => __('Vendor'),
            self::User->value => __('User'),
        ];
    }
}
