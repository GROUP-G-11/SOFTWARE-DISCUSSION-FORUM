<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncRecord extends Model
{
    use HasFactory;

    protected $table = 'sync_records';

    protected $primaryKey = 'sync_id';

    protected $fillable = [
        'user_id',
        'last_synced_at',
        'pending_actions',
        'device_type',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
        'pending_actions' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}