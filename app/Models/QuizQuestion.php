<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizQuestion extends Model
{
    protected $table = 'quiz_questions';
    protected $primaryKey = 'question_id';

    protected $fillable = [
        'quiz_id', 'question_text', 'option_a', 'option_b', 'option_c', 'option_d',
        'correct_option', 'marks',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quiz_id', 'quiz_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class, 'question_id', 'question_id');
    }

    /** Hide the answer key when serializing to students. */
    protected $hidden = ['correct_option'];
}
