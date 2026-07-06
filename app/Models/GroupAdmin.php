<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * GroupAdmin - records which User administers a specific Group. See SDD 4.2 "GroupAdmin" table.
 */
class GroupAdmin extends Model
{
    protected $table = 'group_admins';
    protected $primaryKey = 'group_admin_id';

    protected $fillable = ['user_id', 'group_id', 'appointed_at', 'appointed_by', 'is_active'];

    protected $casts = ['appointed_at' => 'datetime', 'is_active' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'group_id');
    }

    public function appointedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'appointed_by', 'user_id');
    }
}
