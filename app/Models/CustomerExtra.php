<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerExtra extends Model
{
    use HasFactory;
    protected $table = 'customer_extra';
    public $timestamps = FALSE;
}
