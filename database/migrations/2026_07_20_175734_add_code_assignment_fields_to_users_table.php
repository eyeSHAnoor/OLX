<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('code_assigned_by')
                ->nullable()
                ->after('points_balance')
                ->constrained('users')
                ->nullOnDelete();

            $table->boolean('can_assign_code')
                ->default(false)
                ->after('code_assigned_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['code_assigned_by']);
            $table->dropColumn([
                'code_assigned_by',
                'can_assign_code',
            ]);
        });
    }
};