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
        Schema::create('ad_feature', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ad_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('feature_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('feature_value_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('custom_value')->nullable();

            $table->timestamps();

            $table->unique(['ad_id', 'feature_id']); // one feature per ad
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_feature');
    }
};
