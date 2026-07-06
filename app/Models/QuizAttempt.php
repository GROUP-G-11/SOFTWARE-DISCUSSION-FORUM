<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * QuizAttempt - one student's try at a quiz. See SDD 4.2 "QuizAttempt" table.
 */
class QuizAttempt extends Model
{
    protected $table = 'quiz_attempts';
    protected $primaryKey = 'attempt_id';

    protected $fillable = ['quiz_id', 'user_id', 'started_at', 'submitted_at', 'auto_submitted', 'score'];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'auto_submitted' => 'boolean',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quiz_id', 'quiz_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class, 'attempt_id', 'attempt_id');
    }
}
