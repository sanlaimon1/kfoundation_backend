<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialIntegration extends Model
{
    use HasFactory;
    public $table = 'financial_integration';
    public $timestamps = FALSE;

    //关联
    public function customer() {
        
        return $this->hasOne('App\Models\Customer', 'userid', 'id');
    }
}
