<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('subscription_permissions', function (Blueprint $table) {
            $table->id();

            $table->string('name'); // e.g. "create_invoice"
            $table->string('label')->nullable(); // Human readable

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_permissions');
    }
};