<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('company_name')->nullable();
            $table->string('address')->nullable();

            $table->string('phone_1')->nullable();
            $table->string('phone_2')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('company_email')->nullable();

            $table->timestamp('company_verified_at')->nullable();

            // ✅ SQLite-safe version of foreign key:
            $table->unsignedBigInteger('verified_by')->nullable();
            // Foreign keys can still be added after table creation if needed
            // but this avoids SQLite migration errors

            $table->timestamps();
        });

        // Add foreign key separately for better SQLite support
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
