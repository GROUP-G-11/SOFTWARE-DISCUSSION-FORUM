<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Group - one discussion forum/group (e.g. a class group). See SDD 4.2 "Group" table.
 */
class Group extends Model
{
    protected $table = 'groups';
    protected $primaryKey = 'group_id';

    protected $fillable = [
        'name', 'description', 'admin_id',
        'inactivity_warning_period', 'blacklist_duration_days',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id', 'user_id');
    }

    public function groupAdmins(): HasMany
    {
        return $this->hasMany(GroupAdmin::class, 'group_id', 'group_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class, 'group_id', 'group_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'memberships', 'group_id', 'user_id');
    }

    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class, 'group_id', 'group_id');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class, 'group_id', 'group_id');
    }

    public function warnings(): HasMany
    {
        return $this->hasMany(Warning::class, 'group_id', 'group_id');
    }

    public function blacklists(): HasMany
    {
        return $this->hasMany(Blacklist::class, 'group_id', 'group_id');
    }

    public function scoringCriteria(): HasMany
    {
        return $this->hasMany(ScoringCriteria::class, 'group_id', 'group_id');
    }
}
