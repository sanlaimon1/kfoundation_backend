<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;
    public $timestamps = FALSE;

    public function oneadmin()
    {
        return $this->belongsTo(Admin::class,'adminid','id');
    }
}
