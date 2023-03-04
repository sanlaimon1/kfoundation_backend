<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectCate extends Model
{
    use HasFactory;

    public $timestamps = FALSE;
    protected $table = 'project_categories';
}
