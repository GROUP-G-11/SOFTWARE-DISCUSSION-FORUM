<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Warning - automatic inactivity notice. Two unresolved warnings trigger a Blacklist.
 * See SDD 4.2 "Warning" table and 5.2 Moderation and Inactivity Management Module.
 */
class Warning extends Model
{
    protected $table = 'warnings';
    protected $primaryKey = 'warning_id';

    protected $fillable = ['user_id', 'group_id', 'sequence_number', 'issue_date', 'resolved'];

    protected $casts = ['issue_date' => 'datetime', 'resolved' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'group_id');
    }
}
