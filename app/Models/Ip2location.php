<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ip2location extends Model
{
    use HasFactory;
    public $timestamps = FALSE;
    public $table = 'ip2location';
}
