<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('plan_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();

            $table->string('receipt_image')->nullable();
            $table->string('payment_method')->nullable();
            $table->enum('payment_status',['pending','completed','rejected','expired'])->default('pending');

            // pending | completed | failed

            $table->string('transaction_id')->nullable();

            $table->string('payment_gateway')->default('jazzcash');

            $table->decimal('amount_paid', 10, 2)->nullable();

            // JazzCash full response storage
            $table->json('payment_data')->nullable();

            $table->timestamps();

            $table->index(['user_id','payment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
