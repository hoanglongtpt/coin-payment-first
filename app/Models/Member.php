<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;
    protected $table = 'members'; // Tên bảng trong cơ sở dữ liệu
    protected $fillable = [
        'telegram_id',
        'promotion',
        'account_balance',
        'wheel_status',
    ];
}
