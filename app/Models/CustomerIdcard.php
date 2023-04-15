<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerIdcard extends Model
{
    use HasFactory;
    protected $table = 'customer_idcards';
    public $timestamps = FALSE;
}
