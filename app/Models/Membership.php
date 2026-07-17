<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Membership - connects a User to a Group. See SDD 4.2 "Membership" table.
 */
class Membership extends Model
{
    protected $table = 'memberships';
    protected $primaryKey = 'membership_id';

    protected $fillable = ['user_id', 'group_id', 'rules_accepted', 'joined_at', 'role'];

    protected $casts = ['rules_accepted' => 'boolean', 'joined_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'group_id');
    }
}
