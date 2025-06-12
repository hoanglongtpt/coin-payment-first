<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VipCard extends Model
{
    use HasFactory;
    protected $table = 'vip_cards';
    protected $fillable = [
        'amount_usd',
        'ticket_count',
        'description',
    ];
}
