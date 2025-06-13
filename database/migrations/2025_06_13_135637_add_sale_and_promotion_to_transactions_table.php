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
            $table->decimal('sale', 8, 2)->nullable()->after('Tokens_first_time'); // hoặc sau cột phù hợp
            $table->integer('promotion')->nullable()->after('sale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['sale', 'promotion']);
        });
    }
};
