<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetCheck extends Model
{
    use HasFactory;
    protected $table = 'asset_check';

    //关联
    public function customer() {
        
        return $this->hasOne('App\Models\Customer', 'id', 'userid');
    }

    //关联管理员
    public function admin() {
            
        return $this->hasOne('App\Models\Admin', 'id', 'adminid');
    }
}
