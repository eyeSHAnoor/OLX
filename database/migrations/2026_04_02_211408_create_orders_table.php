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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();

            $table->foreignId('ad_id')->constrained()->cascadeOnDelete();

            $table->decimal('price', 12, 2);
            $table->integer('qty')->default(1);

            $table->enum('status', [
                'pending',
                'accepted',
                'rejected',
                'completed',
                'cancelled'
            ])->default('pending');

            $table->timestamps();

            // Helpful indexes
            $table->index(['buyer_id', 'status']);
            $table->index(['seller_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
