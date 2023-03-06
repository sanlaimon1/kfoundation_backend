<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interest extends Model
{
    use HasFactory;
    public $table = 'interest';

    //关联客户
    public function customer() {
        
        return $this->hasOne('App\Models\Customer', 'id', 'cid');
    }

    //关联项目
    public function project() {
        
        return $this->hasOne('App\Models\Project', 'id', 'pid');
    }
}
