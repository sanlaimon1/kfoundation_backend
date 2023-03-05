<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order3 extends Model
{
    use HasFactory;
    public $table = 'order3';

    //关联客户
    public function customer() {
        
        return $this->hasOne('App\Models\Customer', 'cid', 'id');
    }

    //关联生活缴费
    public function life() {
        
        return $this->hasOne('App\Models\Life', 'pid', 'id');
    }
}
