<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('telegram_id')->nullable();      // Cho phép null
            $table->string('promotion')->nullable();         // Cho phép null
            $table->decimal('account_balance', 15, 2)->nullable(); // Cho phép null, lưu số tiền
            $table->timestamps();                            // created_at và updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
