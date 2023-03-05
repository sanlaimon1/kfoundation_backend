<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order1 extends Model
{
    use HasFactory;
    public $table = 'order1';

    //关联客户
    public function customer() {
        
        return $this->hasOne('App\Models\Customer', 'id', 'cid');
    }

    //关联项目
    public function goods() {
        
        return $this->hasOne('App\Models\Project', 'id', 'pid');
    }
}
