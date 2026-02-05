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
        Schema::create('categories', function (Blueprint $table) {
        $table->id();

        $table->string('name');
        $table->string('slug')->unique();

        // parent category (nullable for root categories)
        $table->foreignId('parent_id')->nullable()->constrained('categories')->cascadeOnDelete();

        $table->boolean('is_active')->default(true);

        // for fast tree queries
        $table->integer('position')->default(0);

        $table->timestamps();

        $table->index(['parent_id']);
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
