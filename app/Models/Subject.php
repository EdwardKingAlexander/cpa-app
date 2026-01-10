<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = [
        'code',
        'name',
        'syllabus_code',
        'default_syllabus_version_id',
        'exam_question_count',
    ];

    protected function casts(): array
    {
        return [
            'exam_question_count' => 'integer',
        ];
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function syllabusTopics(): HasMany
    {
        return $this->hasMany(SyllabusTopic::class);
    }

    public function defaultSyllabusVersion(): BelongsTo
    {
        return $this->belongsTo(SyllabusVersion::class, 'default_syllabus_version_id');
    }
}
