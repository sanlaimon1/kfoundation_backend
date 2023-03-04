<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Goods extends Model
{
    use HasFactory;
    public $table = 'goods';
    public $timestamps = FALSE;

    //关联
    public function level() {
        
        return $this->hasOne('App\Models\Level', 'level_id', 'level_id');
    }
}
