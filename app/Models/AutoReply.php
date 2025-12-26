<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoReply extends Model
{
    protected $fillable = [
        'user_id',
        'device_id',
        'name',
        'keywords',
        'match_type',
        'reply_message',
        'reply_type',
        'attachment_path',
        'is_active',
        'priority',
        'hit_count',
    ];

    protected $casts = [
        'keywords' => 'array',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Check if a message matches this auto-reply rule
     */
    public function matchesMessage(string $message): bool
    {
        $message = strtolower(trim($message));

        foreach ($this->keywords as $keyword) {
            $keyword = strtolower(trim($keyword));

            switch ($this->match_type) {
                case 'exact':
                    if ($message === $keyword) {
                        return true;
                    }
                    break;
                case 'contains':
                    if (str_contains($message, $keyword)) {
                        return true;
                    }
                    break;
                case 'starts_with':
                    if (str_starts_with($message, $keyword)) {
                        return true;
                    }
                    break;
                case 'regex':
                    if (preg_match($keyword, $message)) {
                        return true;
                    }
                    break;
            }
        }

        return false;
    }
}
