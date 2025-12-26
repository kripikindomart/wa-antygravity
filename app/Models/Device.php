<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Device extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'name',
        'phone_number',
        'status',
        'webhook_url',
        'session_data',
        'last_connected_at',
    ];

    protected $casts = [
        'session_data' => 'array',
        'last_connected_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($device) {
            if (empty($device->token)) {
                $device->token = Str::uuid()->toString();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function inboxes(): HasMany
    {
        return $this->hasMany(Inbox::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function autoReplies(): HasMany
    {
        return $this->hasMany(AutoReply::class);
    }

    public function isConnected(): bool
    {
        return $this->status === 'connected';
    }

    public function isScanning(): bool
    {
        return $this->status === 'scanning';
    }
}
