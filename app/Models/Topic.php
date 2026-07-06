<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Topic - one discussion thread inside a group. See SDD 4.2 "Topic" table.
 */
class Topic extends Model
{
    protected $table = 'topics';
    protected $primaryKey = 'topic_id';

    protected $fillable = ['group_id', 'title', 'created_by', 'category'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'group_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'topic_id', 'topic_id');
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(TopicRecommendation::class, 'topic_id', 'topic_id');
    }
}
