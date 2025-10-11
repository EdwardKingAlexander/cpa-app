<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    // Fillable fields for mass assignment
    protected $fillable = ['user_id', 'question_id', 'due_at', 'interval', 'repetitions', 'lapses', 'ease', 'suspended', 'meta'];
    // Casts for date and JSON fields
    protected $casts = ['due_at' => 'datetime', 'meta' => 'array'];

    // A Card belongs to a Question
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    // A Card belongs to a User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // A Card has many ReviewLogs
    public function logs()
    {
        return $this->hasMany(ReviewLog::class);
    }
}
