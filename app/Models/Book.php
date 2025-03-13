<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'author_id'];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function collaborators()
    {
        return $this->belongsToMany(User::class);
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }
}
