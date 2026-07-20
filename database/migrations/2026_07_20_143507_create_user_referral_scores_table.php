<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_referral_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Points tracking
            $table->integer('total_earned')->default(0)->comment('Total points earned from referrals');
            $table->integer('total_withdrawn')->default(0)->comment('Total points withdrawn');
            $table->integer('available')->default(0)->comment('Available points to withdraw');
            $table->integer('pending')->default(0)->comment('Points in pending withdrawal');
            
            // Current withdrawal request
              $table->decimal('requested_amount', 10, 2)->nullable()->comment('Amount requested');
            
            // Status
            $table->enum('status', ['active', 'pending', 'approved', 'completed', 'rejected'])
                ->default('active')->comment('Current withdrawal status');
            
            // Payment details
            $table->string('payment_method', 50)->nullable();
            $table->json('payment_details')->nullable();
            
            // Admin proof & tracking
            $table->json('proof_images')->nullable()->comment('Payment proof screenshots');
            $table->string('transaction_id', 255)->nullable();
            $table->text('admin_notes')->nullable();
            
            // Timestamps for tracking
            $table->timestamp('last_earning_at')->nullable();
            $table->timestamp('last_withdrawal_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index('available');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_referral_scores');
    }
};