<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_types')) {
            return;
        }

        Schema::create('user_types', function (Blueprint $table) {
            $table->id();
            $table->string('user_type', 100)->nullable();
            $table->string('access_to', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_types');
    }
};