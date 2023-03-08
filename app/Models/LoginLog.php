<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    use HasFactory;
    public $timestamps = FALSE;
    public $table = 'login_logs';
	
	//关联客户
    public function customer() {
        
        return $this->hasOne('App\Models\Customer', 'id', 'userid');
    }
}
