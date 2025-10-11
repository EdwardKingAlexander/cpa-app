<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['code', 'name'];


    // A Subject has many Questions
    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
