<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_requests', function (Blueprint $table) {
            $table->foreignId('owner_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            
            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('pending')
                ->change();
                
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            
            $table->dropIndex(['status']);
            $table->index(['user_id', 'status']);
            $table->index(['owner_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('contact_requests', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn(['owner_id', 'name', 'phone', 'rejection_reason', 'reviewed_by', 'reviewed_at']);
            $table->enum('status', ['new', 'viewed', 'closed'])->default('new')->change();
        });
    }
};
