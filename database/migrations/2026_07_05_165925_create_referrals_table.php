// database/migrations/xxxx_create_referrals_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referred_user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending, completed
            $table->integer('points_awarded')->default(0);
            $table->string('link_code')->nullable(); // tracks which link was used
            $table->timestamp('visited_at')->nullable(); // tracks visit
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};