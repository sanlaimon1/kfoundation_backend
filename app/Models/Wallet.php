<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;
    public $timestamps = FALSE;

    //关联客户
    public function customer() {
        
        return $this->hasOne('App\Models\Customer', 'id', 'userid');
    }
}
