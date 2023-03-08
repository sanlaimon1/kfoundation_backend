<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    //关联项目分类
    public function projectcate() {
        
        return $this->hasOne('App\Models\ProjectCate', 'id', 'cid');
    }

    public function level() {
        
        return $this->hasOne('App\Models\Level', 'level_id', 'level_id');
    }
}
