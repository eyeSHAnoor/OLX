// database/migrations/xxxx_xx_xx_000002_create_regions_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->onDelete('cascade');
            $table->string('name');          // region name (e.g., 'Tehsil', 'District', 'Union Council')
            $table->timestamps();
            
            // composite index for fast lookup by city
            $table->index('city_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('regions');
    }
};