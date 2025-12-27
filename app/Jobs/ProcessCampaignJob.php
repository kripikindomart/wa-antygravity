<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $campaignId;
    public $timeout = 3600; // 1 hour timeout for large campaigns

    public function __construct($campaignId)
    {
        $this->campaignId = $campaignId;
    }

    public function handle()
    {
        $campaign = Campaign::with('device')->find($this->campaignId);

        if (!$campaign || !$campaign->device || $campaign->status !== 'running') {
            return; // Invalid state
        }

        // Get Pending Recipients
        $recipients = $campaign->recipients()->where('status', 'pending')->get();

        foreach ($recipients as $recipient) {
            // Re-check campaign status (in case user paused it)
            if ($campaign->fresh()->status === 'paused') {
                break;
            }

            $currentRecipient = $recipient;
            $currentRecipient->update(['status' => 'processing']);

            try {
                // Prepare Message
                $preparedMessage = $this->prepareMessage($campaign->message, $recipient);

                // Send via API
                $nodeUrl = config('services.whatsapp.url', 'http://127.0.0.1:3001');
                $response = Http::timeout(30)->post("{$nodeUrl}/messages/send", [
                    'sessionId' => $campaign->device->token,
                    'to' => $recipient->phone_number,
                    'message' => $preparedMessage,
                ]);

                // Create Message Record (History)
                $msg = Message::create([
                    'user_id' => $campaign->user_id,
                    'device_id' => $campaign->device_id,
                    'campaign_id' => $campaign->id,
                    'to' => $recipient->phone_number,
                    'message' => $preparedMessage,
                    'type' => 'text',
                    'status' => $response->successful() ? 'sent' : 'failed',
                    'wa_message_id' => $response->json('messageId'),
                    'error_message' => $response->body()
                ]);

                // Update Recipient Status
                if ($response->successful()) {
                    $recipient->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                        'wa_message_id' => $response->json('messageId')
                    ]);
                    $campaign->increment('sent_count');
                } else {
                    throw new \Exception('API Error: ' . $response->body());
                }

            } catch (\Exception $e) {
                $recipient->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
                $campaign->increment('failed_count');

                // Stop on Error Logic
                if ($campaign->error_mode === 'stop') {
                    $campaign->update(['status' => 'paused']);
                    Log::error("Campaign {$campaign->id} paused due to error: " . $e->getMessage());
                    break;
                }
            }

            // Delay
            if ($campaign->delay_seconds > 0) {
                sleep($campaign->delay_seconds);
            }
        }

        // Check completion
        $pendingCount = $campaign->recipients()->where('status', 'pending')->count();
        if ($pendingCount === 0 && $campaign->status === 'running') {
            $campaign->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }
    }

    private function prepareMessage($template, $recipient)
    {
        $message = $template;

        // Replace Standard Variables
        $message = str_replace('[name]', $recipient->name, $message);
        $message = str_replace('[phone]', $recipient->phone_number, $message);

        // Replace Custom Variables
        if ($recipient->custom_data) {
            foreach ($recipient->custom_data as $key => $value) {
                $message = str_replace('[' . $key . ']', $value, $message);
            }
        }

        // Auto-format Rupiah: "Rp. 250000,-" -> "Rp. 250.000,-"
        $message = preg_replace_callback('/Rp\.\s*(\d+)\s*,-/', function ($matches) {
            return 'Rp. ' . number_format((int) $matches[1], 0, ',', '.') . ',-';
        }, $message);

        return $message;
    }
}
