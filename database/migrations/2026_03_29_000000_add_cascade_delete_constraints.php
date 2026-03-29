<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->foreignId('owner_id')
                ->nullable()
                ->change();
        });

        Schema::table('contact_requests', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->foreignId('owner_id')->change();
        });

        Schema::table('contact_requests', function (Blueprint $table) {
            $table->foreignId('user_id')->change();
        });
    }
};
