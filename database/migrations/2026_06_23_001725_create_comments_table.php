<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();

            // who commented
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // comment belongs to an ad
            $table->foreignId('ad_id')->constrained()->cascadeOnDelete();

            // reply system
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('comments')
                ->cascadeOnDelete();

            // content type
            $table->enum('type', ['text', 'image'])->default('text');

            $table->text('message')->nullable(); // text comment
            $table->string('image_path')->nullable(); // image comment

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
