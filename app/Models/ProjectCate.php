<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectCate extends Model
{
    use HasFactory;

    public $timestamps = FALSE;
    protected $table = 'project_categories';

    public function projectCateLang()
    {
        return $this->hasOne('App\Models\ProjectCateLang', 'project_cate_id', 'id');
    }
}
