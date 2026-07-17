<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SocialShare - tracking record for a post forwarded to an external platform.
 * See SDD 5.9 Social Media Sharing Module.
 */
class SocialShare extends Model
{
    protected $table = 'social_shares';
    protected $primaryKey = 'share_id';

    protected $fillable = ['post_id', 'user_id', 'platform', 'shared_url', 'shared_at'];

    protected $casts = ['shared_at' => 'datetime'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id', 'post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
