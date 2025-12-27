<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageTemplate extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'content',
        'category',
        'is_favorite',
    ];

    protected $casts = [
        'is_favorite' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Parse template content and replace variables with actual data
     */
    public function parseVariables(array $data): string
    {
        $content = $this->content;

        // Default variables
        $variables = [
            '[name]' => $data['name'] ?? '',
            '[phone]' => $data['phone'] ?? '',
            '[email]' => $data['email'] ?? '',
            '[company]' => $data['company'] ?? '',
            '[custom1]' => $data['custom1'] ?? '',
            '[custom2]' => $data['custom2'] ?? '',
            '[date]' => now()->format('d M Y'),
            '[time]' => now()->format('H:i'),
        ];

        return str_replace(array_keys($variables), array_values($variables), $content);
    }

    /**
     * Get available variable placeholders
     */
    public static function getAvailableVariables(): array
    {
        return [
            ['key' => '[name]', 'label' => 'Contact Name', 'icon' => '👤'],
            ['key' => '[phone]', 'label' => 'Phone Number', 'icon' => '📱'],
            ['key' => '[email]', 'label' => 'Email', 'icon' => '📧'],
            ['key' => '[company]', 'label' => 'Company', 'icon' => '🏢'],
            ['key' => '[custom1]', 'label' => 'Custom Field 1', 'icon' => '📝'],
            ['key' => '[custom2]', 'label' => 'Custom Field 2', 'icon' => '📝'],
            ['key' => '[date]', 'label' => 'Current Date', 'icon' => '📅'],
            ['key' => '[time]', 'label' => 'Current Time', 'icon' => '⏰'],
        ];
    }
}
