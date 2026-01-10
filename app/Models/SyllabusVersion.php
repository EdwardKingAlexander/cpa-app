<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SyllabusVersion extends Model
{
    /** @use HasFactory<\Database\Factories\SyllabusVersionFactory> */
    use HasFactory;

    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'effective_date',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
        ];
    }

    public function topics(): HasMany
    {
        return $this->hasMany(SyllabusTopic::class);
    }

    public function defaultSubjects(): HasMany
    {
        return $this->hasMany(Subject::class, 'default_syllabus_version_id');
    }
}
