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
        // Thêm cột wheel_status vào bảng members
        Schema::table('members', function (Blueprint $table) {
            $table->string('wheel_status')->nullable(); // Cột wheel_status có thể chứa giá trị null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Xóa cột wheel_status khi rollback
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('wheel_status');
        });
    }
};
