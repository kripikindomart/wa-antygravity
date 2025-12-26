<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    protected $fillable = [
        'user_id',
        'contact_group_id',
        'name',
        'phone_number',
        'email',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ContactGroup::class, 'contact_group_id');
    }

    // Format phone number for WhatsApp (remove + and spaces)
    public function getFormattedPhoneAttribute(): string
    {
        return preg_replace('/[^0-9]/', '', $this->phone_number);
    }
}
