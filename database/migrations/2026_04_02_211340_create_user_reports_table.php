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
        Schema::create('user_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('reported_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reported_by')->constrained('users')->cascadeOnDelete();

            $table->foreignId('ad_id')->nullable()->constrained()->nullOnDelete();

            $table->string('reason'); // scam, spam, abusive, fake listing etc
            $table->text('message')->nullable();

            $table->string('status')->default('pending'); 
            // pending | reviewed | resolved | rejected

            $table->text('admin_response')->nullable();

            $table->timestamp('responded_at')->nullable();

            $table->foreignId('responded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_reports');
    }
};
