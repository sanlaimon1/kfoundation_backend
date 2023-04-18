<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialProductions extends Model
{
    use HasFactory;
    public $table = 'financial_productions';
    public $timestamps = FALSE;
}
