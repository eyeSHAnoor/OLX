// database/migrations/xxxx_xx_xx_000001_create_cities_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('country', 2);      // e.g., 'PK'
            $table->decimal('lat', 10, 6);
            $table->decimal('lng', 10, 6);
            $table->timestamps();
            
            // optional: unique constraint to avoid duplicates
            $table->unique(['name', 'country']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cities');
    }
};