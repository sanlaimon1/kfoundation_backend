<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Role;

class Admin extends Model
{
    use HasFactory;
    public $timestamps = FALSE;

    //关联角色
    public function role() {
        
        return $this->hasOne('App\Models\Role', 'rid', 'rid');
    }

}
