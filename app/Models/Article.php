<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;
    
    public function category(Type $var = null)
    {
        return $this->belongsTo('App\Models\Category','categoryid', 'id' );
    }

    public function admin(Type $var = null)
    {
        return $this->belongsTo('App\Models\User','adminid', 'id' );
    }
}
