<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    public $table = "permission";
    public $timestamps = FALSE;

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id' , 'rid');
    }

}
