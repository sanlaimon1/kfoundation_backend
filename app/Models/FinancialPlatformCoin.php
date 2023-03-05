<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialPlatformCoin extends Model
{
    use HasFactory;
    public $table = 'financial_platform_coin';
    public $timestamps = FALSE;

    //关联
    public function customer() {
        
        return $this->hasOne('App\Models\Customer', 'id', 'userid');
    }
}
