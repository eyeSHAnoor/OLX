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
        Schema::table('ads', function (Blueprint $table) {
            // Drop index first (required before changing to TEXT)
            $table->dropIndex('ads_ad_title_index');

            // Change column type
            $table->text('ad_title')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            // Revert column back to string
            $table->string('ad_title', 255)->change();

            // Recreate index
            $table->index('ad_title');
        });
    }
};