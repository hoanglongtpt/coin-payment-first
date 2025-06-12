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
        Schema::create('vip_cards', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount_usd', 8, 2)->nullable();        // 19, 49, 69, 119
            $table->unsignedInteger('ticket_count')->nullable();    // 29, 49, 69, 119
            $table->string('description')->nullable();              // e.g. Daily for 142 Days 7
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vip_cards');
    }
};
