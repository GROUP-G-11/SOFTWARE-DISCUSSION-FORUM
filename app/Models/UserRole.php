<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UserRole - many-to-many link between User and Role. See SDD 4.2 "UserRole" table.
 */
class UserRole extends Model
{
    protected $table = 'user_roles';
    protected $primaryKey = 'user_role_id';

    protected $fillable = ['user_id', 'role_id', 'assigned_at', 'assigned_by'];

    protected $casts = ['assigned_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by', 'user_id');
    }
}
