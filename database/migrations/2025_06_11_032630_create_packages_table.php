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
    Schema::create('packages', function (Blueprint $table) {
        $table->id();
        $table->string('name')->nullable(); // Tên gói (ví dụ: 5 USD, 10 USD)
        $table->decimal('price', 8, 2)->nullable(); // Giá gói
        $table->integer('reward_points')->nullable(); // Số điểm thưởng (🎟️)
        $table->integer('bonus')->nullable(); // Số bonus (🍀)
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
