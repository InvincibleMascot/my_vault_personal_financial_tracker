<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserType extends Model
{
    protected $table = 'user_types';

    protected $fillable = [
        'user_type',
        'access_to',
    ];

    public const SUPER_ADMIN = 'super_admin';
    public const ADMIN = 'admin';
    public const CUSTOMER = 'customer';

    public const ACCESS_OVERVIEW = 'overview';
    public const ACCESS_TRANSACTIONS = 'transactions';
    public const ACCESS_PLANNING = 'planning';
    public const ACCESS_USER_MANAGEMENT = 'user_management';

    public static function labels(): array
    {
        return [
            self::SUPER_ADMIN => 'Super Admin',
            self::ADMIN => 'Admin',
            self::CUSTOMER => 'Customer',
        ];
    }

    public static function defaultAccessFor(string $userType): array
    {
        return match (self::normalize($userType)) {
            self::SUPER_ADMIN => [
                self::ACCESS_OVERVIEW,
                self::ACCESS_TRANSACTIONS,
                self::ACCESS_PLANNING,
                self::ACCESS_USER_MANAGEMENT,
            ],
            self::ADMIN => [
                self::ACCESS_OVERVIEW,
                self::ACCESS_TRANSACTIONS,
                self::ACCESS_PLANNING,
            ],
            self::CUSTOMER => [
                self::ACCESS_TRANSACTIONS,
            ],
            default => [],
        };
    }

    public static function defaultRows(): array
    {
        return [
            1 => self::SUPER_ADMIN,
            2 => self::ADMIN,
            3 => self::CUSTOMER,
        ];
    }

    public static function normalize(?string $userType): string
    {
        $userType = strtolower(trim((string) $userType));
        $userType = str_replace([' ', '-'], '_', $userType);

        return array_key_exists($userType, self::labels())
            ? $userType
            : self::CUSTOMER;
    }

    public function accessList(): array
    {
        if ($this->access_to) {
            return array_values(array_filter(array_map('trim', explode(',', $this->access_to))));
        }

        return self::defaultAccessFor((string) $this->user_type);
    }
}