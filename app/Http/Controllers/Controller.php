<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

abstract class Controller
{
    protected function scopeCreatedByCurrentUser(Builder $query, string $table, ?string $column = null): Builder
    {
        $user = Auth::user();

        if (!$user || $user->isSuperAdmin() || !Schema::hasColumn($table, 'created_by')) {
            return $query;
        }

        return $query->where($column ?? $table . '.created_by', Auth::id());
    }
}
