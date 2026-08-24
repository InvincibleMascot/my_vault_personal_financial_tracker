<?php

use App\Models\UserType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_types')) {
            Schema::create('user_types', function (Blueprint $table) {
                $table->id();
                $table->string('user_type', 100)->nullable();
                $table->string('access_to', 500)->nullable();
                $table->timestamps();
            });
        }

        Schema::table('user_types', function (Blueprint $table) {
            if (!Schema::hasColumn('user_types', 'user_type')) {
                $table->string('user_type', 100)->nullable();
            }

            if (!Schema::hasColumn('user_types', 'access_to')) {
                $table->string('access_to', 500)->nullable();
            }

            if (!Schema::hasColumn('user_types', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (!Schema::hasColumn('user_types', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'user_type_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer('user_type_id')->nullable()->after('password');
            });
        }

        foreach (UserType::defaultRows() as $id => $userType) {
            $accessTo = implode(',', UserType::defaultAccessFor($userType));
            $existingById = DB::table('user_types')->where('id', $id)->first();
            $existingByType = DB::table('user_types')->where('user_type', $userType)->first();

            if ($existingById) {
                DB::table('user_types')->where('id', $id)->update([
                    'user_type' => $userType,
                    'access_to' => $accessTo,
                    'updated_at' => now(),
                ]);
                continue;
            }

            if ($existingByType) {
                DB::table('user_types')->where('id', $existingByType->id)->update([
                    'access_to' => $accessTo,
                    'updated_at' => now(),
                ]);
                continue;
            }

            DB::table('user_types')->insert([
                'id' => $id,
                'user_type' => $userType,
                'access_to' => $accessTo,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'user_type_id')) {
            DB::table('users')->whereNull('user_type_id')->update(['user_type_id' => 3]);
            DB::table('users')->where('id', 1)->update(['user_type_id' => 1]);
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('user_types', 'id'), (SELECT MAX(id) FROM user_types))");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'user_type_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('user_type_id');
            });
        }
    }
};