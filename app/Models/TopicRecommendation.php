<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * TopicRecommendation - ML-generated suggestion pointing a user to an unseen relevant topic.
 * See SDD 4.2 "TopicRecommendation" table and 5.8 ML Classification and Recommendation.
 */
class TopicRecommendation extends Model
{
    protected $table = 'topic_recommendations';
    protected $primaryKey = 'recommendation_id';

    protected $fillable = ['user_id', 'topic_id', 'relevance_score', 'generated_at'];

    protected $casts = ['relevance_score' => 'decimal:3', 'generated_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class, 'topic_id', 'topic_id');
    }
}
