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
            Schema::create('ads', function (Blueprint $table) {
        $table->id();

        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
         $table->foreignId('brand_model_id')->nullable()->constrained('brand_models');
        $table->foreignId('category_id')->constrained();
        $table->foreignId('brand_id')->nullable()->constrained();

        $table->string('ad_title');

        $table->text('description');

        $table->decimal('price', 10, 2)->nullable();

        $table->string('location');
        $table->string('city');
        $table->string('region')->nullable();

        // Seller info (snapshot at posting time)
        $table->string('seller_name');
        $table->string('seller_phone');
        $table->string('status')->default('active');

        $table->boolean('is_active')->default(true);
        $table->boolean('is_featured')->default(false);
        $table->json('search_keywords')->nullable();

        $table->timestamps();

        $table->index(['category_id']);
        $table->index(['brand_id']);
        $table->index(['ad_title']);
        $table->index(['brand_model_id']);
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
