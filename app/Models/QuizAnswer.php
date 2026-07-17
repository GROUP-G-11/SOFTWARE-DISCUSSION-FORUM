<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * QuizAnswer - the answer a student gave for one question in one attempt.
 * See SDD 4.2 "Quiz Answer" table.
 */
class QuizAnswer extends Model
{
    protected $table = 'quiz_answers';
    protected $primaryKey = 'result_id';

    protected $fillable = ['attempt_id', 'question_id', 'selected_option', 'is_correct', 'marks_awarded'];

    protected $casts = ['is_correct' => 'boolean'];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class, 'attempt_id', 'attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id', 'question_id');
    }
}
