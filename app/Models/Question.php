<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['subject_id', 'stem', 'explanation', 'difficulty'];

    // A Question belongs to a Subject
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    // A Question has many Choices
    public function choices()
    {
        return $this->hasMany(Choice::class);
    }

    // A Question has many Cards
    public function cards()
    {
        return $this->hasMany(\App\Models\Card::class);
    }
}
