<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class FinancialAsset extends Model
{
    use HasFactory;
    public $table = 'financial_asset';
    public $timestamps = FALSE;

    //关联
    public function customer() {
        
        return $this->hasOne('App\Models\Customer', 'id', 'userid');
    }
}
