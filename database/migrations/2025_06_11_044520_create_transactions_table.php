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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->nullable();  // Tham chiếu đến bảng members
            $table->foreignId('package_id')->nullable();  // Tham chiếu đến bảng packages
            $table->decimal('amount', 8, 2);  // Số tiền giao dịch
            $table->integer('status')->default(2);  // Status: 1 = Thành công, 2 = Đang chờ, 3 = Hủy
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
