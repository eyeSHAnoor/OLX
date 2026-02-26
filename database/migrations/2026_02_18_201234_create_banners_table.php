<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Banner title
            $table->string('image_url'); // Path to banner image
            $table->string('link')->nullable(); // URL to redirect when clicked
            $table->enum('position', ['homepage', 'category', 'sidebar', 'floating'])->default('homepage');
            $table->unsignedBigInteger('target_category_id')->nullable(); // If banner targets a category
            $table->dateTime('start_date')->nullable(); // Start showing banner
            $table->dateTime('end_date')->nullable();   // End showing banner
            $table->boolean('status')->default(true); // Active or inactive
            $table->timestamps();

            // Foreign key if using categories
            $table->foreign('target_category_id')
                  ->references('id')
                  ->on('categories')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('banners');
    }
};
