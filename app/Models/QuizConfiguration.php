<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizConfiguration extends Model
{
    protected $table = 'quiz_configurations';
    protected $primaryKey = 'config_id';

    protected $fillable = ['quiz_id', 'scheduled_date', 'start_time', 'duration_minutes'];

    protected $casts = ['scheduled_date' => 'date'];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quiz_id', 'quiz_id');
    }
}
