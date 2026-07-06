<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SyncRecord - tracks how up to date a member's desktop app is vs. the server.
 * See SDD 4.2 "SyncRecord" table and 5.4 Messaging and Synchronization Module.
 */
class SyncRecord extends Model
{
    protected $table = 'sync_records';
    protected $primaryKey = 'sync_id';

    protected $fillable = ['user_id', 'last_synced_at', 'pending_actions', 'device_type'];

    protected $casts = ['last_synced_at' => 'datetime', 'pending_actions' => 'array'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
