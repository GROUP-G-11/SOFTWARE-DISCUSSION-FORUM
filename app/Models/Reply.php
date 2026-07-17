<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Reply - a threaded response to a specific Post. See SDD 4.2 "Reply" table.
 */
class Reply extends Model
{
    protected $table = 'replies';
    protected $primaryKey = 'reply_id';

    protected $fillable = ['post_id', 'author_id', 'content', 'replied_at', 'is_flagged'];

    protected $casts = ['replied_at' => 'datetime', 'is_flagged' => 'boolean'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id', 'post_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id', 'user_id');
    }
}
