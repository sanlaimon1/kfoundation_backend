<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradeRecords extends Model
{
    use HasFactory;
    protected $table = 'trade_records';
    public $timestamps = FALSE;
}
