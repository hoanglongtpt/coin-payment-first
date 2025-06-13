<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('package_sku')->nullable(); // hoặc after cột nào phù hợp
        });
    }

    /**
     * Reverse the migrations.
     */
     public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('package_sku');
        });
    }
};
