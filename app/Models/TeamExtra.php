<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamExtra extends Model
{
    use HasFactory;
    public $table = 'team_extra';
    public $timestamps = FALSE;

    //关联客户
    public function customer() {
        
        return $this->hasOne('App\Models\Customer', 'id', 'userid');
    }
}
