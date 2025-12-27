<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    protected $fillable = [
        'user_id',
        'device_id',
        'name',
        'message',
        'type', // message content type
        'audience_type', // group or import
        'attachment_path',
        'target_contacts',
        'target_groups',
        'status',
        'scheduled_at',
        'started_at',
        'completed_at',
        'total_recipients',
        'sent_count',
        'failed_count',
        'delay_seconds',
        'error_mode',
        'mapping_config',
    ];

    protected $casts = [
        'target_contacts' => 'array',
        'target_groups' => 'array',
        'mapping_config' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(CampaignRecipient::class);
    }

    public function getProgressPercentageAttribute(): float
    {
        if ($this->total_recipients === 0) {
            return 0;
        }

        return round(($this->sent_count / $this->total_recipients) * 100, 1);
    }

    public function getSuccessRateAttribute(): float
    {
        $total = $this->sent_count + $this->failed_count;
        if ($total === 0) {
            return 0;
        }

        return round(($this->sent_count / $total) * 100, 1);
    }

    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
