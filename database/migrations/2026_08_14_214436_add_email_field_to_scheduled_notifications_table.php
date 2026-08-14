<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('scheduled_notifications', function (Blueprint $table) {
            $table->boolean('is_email')->default(false)->after('url');
        });
    }

    public function down()
    {
        Schema::table('scheduled_notifications', function (Blueprint $table) {
            $table->dropColumn('is_email');
        });
    }
};