<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('plan_permission', function (Blueprint $table) {

            $table->id();

            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_permission_id')->constrained()->cascadeOnDelete();

            $table->unique(['plan_id', 'subscription_permission_id']); // prevent duplicates
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_permission');
    }
};
