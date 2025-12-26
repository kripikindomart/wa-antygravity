<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inbox extends Model
{
    protected $fillable = [
        'device_id',
        'from_number',
        'from_name',
        'message',
        'type',
        'media_url',
        'wa_message_id',
        'is_read',
        'received_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'received_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }
}
