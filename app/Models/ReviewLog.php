<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewLog extends Model
{
    // Fillable fields for mass assignment
    protected $fillable = ['card_id', 'user_id', 'question_id', 'grade', 'duration_ms', 'interval_before', 'interval_after', 'ease_before', 'ease_after'];

    // Relationships

    // A ReviewLog belongs to a Card
    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    // A ReviewLog belongs to a User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // A ReviewLog belongs to a Question
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
