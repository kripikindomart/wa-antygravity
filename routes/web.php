<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard;
use App\Livewire\Devices\DeviceIndex;
use App\Livewire\Contacts\ContactIndex;
use App\Livewire\Messages\MessageIndex;
use App\Livewire\Campaigns\CampaignIndex;
use App\Livewire\AutoReplies\AutoReplyIndex;
use App\Livewire\ApiTokens\ApiTokenIndex;
use App\Livewire\Settings;

Route::view('/', 'welcome');

// Tenant Routes (Protected by Auth)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    // Devices
    Route::get('/devices', DeviceIndex::class)->name('devices.index');

    // Contacts
    Route::get('/contacts', ContactIndex::class)->name('contacts.index');
    Route::get('/contacts/all', ContactIndex::class)->name('contacts.all');
    Route::get('/contacts/group/{groupId}', ContactIndex::class)->name('contacts.group');

    // Messages
    Route::get('/messages', MessageIndex::class)->name('messages.index');

    // Campaigns / Broadcast
    Route::get('/campaigns', CampaignIndex::class)->name('campaigns.index');
    Route::get('/campaigns/create', \App\Livewire\Campaigns\BroadcastWizard::class)->name('campaigns.create');
    Route::get('/campaigns/{campaign}', \App\Livewire\Campaigns\CampaignShow::class)->name('campaigns.show');

    // Auto Replies
    Route::get('/auto-replies', AutoReplyIndex::class)->name('auto-replies.index');

    // Message Templates
    Route::get('/templates', \App\Livewire\Templates\TemplateIndex::class)->name('templates.index');

    // Daily Leads
    Route::get('/leads', App\Livewire\Leads\LeadIndex::class)->name('leads.index');

    // API Tokens
    Route::get('/api-tokens', ApiTokenIndex::class)->name('api-tokens.index');

    // Contact Groups
    Route::get('/contact-groups', \App\Livewire\ContactGroups\ContactGroupIndex::class)->name('contact-groups.index');

    // Settings
    Route::get('/settings', Settings::class)->name('settings');
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__ . '/auth.php';
