<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectCateLang extends Model
{
    use HasFactory;
    public $timestamps = FALSE;
    protected $table = 'project_categories_lang';
}
