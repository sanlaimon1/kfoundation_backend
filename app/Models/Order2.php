<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order2 extends Model
{
    use HasFactory;
    public $table = 'order2';

    //关联客户
    public function customer() {
        
        return $this->hasOne('App\Models\Customer', 'cid', 'id');
    }

    //关联商品
    public function goods() {
        
        return $this->hasOne('App\Models\Goods', 'production_id', 'id');
    }
}
