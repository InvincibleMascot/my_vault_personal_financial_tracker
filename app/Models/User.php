<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'email',
        'date_of_birth',
        'gender',
        'designation',
        'department',
        'company',
        'salary',
        'address',
        'city',
        'state',
        'country',
        'bank_name',
        'account_number',
        'ifsc_code',
        'shift_id',
        'password',
        'user_type_id',
    ];

    public function userTypeMaster()
    {
        return $this->belongsTo(UserType::class, 'user_type_id');
    }

    public function userTypeRecord(): ?UserType
    {
        if (!Schema::hasTable('user_types')) {
            return null;
        }

        if (Schema::hasColumn('users', 'user_type_id') && $this->user_type_id) {
            return UserType::query()->where('id', $this->user_type_id)->first();
        }

        if (Schema::hasColumn('user_types', 'user_id')) {
            return UserType::query()->where('user_id', $this->id)->latest('id')->first();
        }

        return null;
    }

    public function userType(): string
    {
        $record = $this->userTypeRecord();

        if ($record) {
            return UserType::normalize($record->user_type);
        }

        if (!Schema::hasTable('user_types') || !UserType::query()->exists()) {
            return (int) $this->id === 1 ? UserType::SUPER_ADMIN : UserType::CUSTOMER;
        }

        return UserType::CUSTOMER;
    }

    public function isSuperAdmin(): bool
    {
        return $this->userType() === UserType::SUPER_ADMIN;
    }

    public function canAccessArea(string $area): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $record = $this->userTypeRecord();
        $access = $record ? $record->accessList() : UserType::defaultAccessFor($this->userType());

        return in_array($area, $access, true);
    }

    public function defaultRouteName(): string
    {
        return $this->canAccessArea(UserType::ACCESS_OVERVIEW)
            ? 'dashboard'
            : 'transactions.index';
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
