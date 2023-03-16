<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blockip extends Model
{
    use HasFactory;
    protected $table = 'blockip';
    public $timestamps = FALSE;
}
