<?php

namespace App\Livewire\Campaigns;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\ContactGroup;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.tenant')]
#[Title('New Broadcast')]
class BroadcastWizard extends Component
{
    use WithFileUploads;

    // Wizard Steps
    public int $step = 1;
    public int $totalSteps = 4;

    // Step 1: Setup
    public string $name = '';
    public ?int $device_id = null;

    // Step 2: Audience
    public string $audience_type = 'group'; // group, import
    public array $selected_groups = [];
    public $csv_file;
    public array $csv_headers = [];
    public array $csv_preview = []; // First 5 rows
    public array $column_mapping = []; // header -> variable_name

    // Step 3: Compose
    public string $message = '';
    public ?int $selected_template_id = null;
    public $attachment = null; // For image/document attachment
    public ?string $attachment_type = null; // image, document

    // Step 4: Settings & Schedule
    public string $schedule_date = '';
    public string $schedule_time = '';
    public int $delay_seconds = 10;
    public string $error_mode = 'continue'; // continue, stop

    // Preview Data (Computed from Audience)
    public array $preview_recipients = [];
    public int $preview_index = 0;

    public function mount()
    {
        $this->name = 'Broadcast ' . now()->format('Y-m-d H:i');
        // Auto-select first connected device
        $device = Auth::user()->devices()->where('status', 'connected')->first();
        if ($device) {
            $this->device_id = $device->id;
        }
    }

    public function nextStep()
    {
        $this->validateStep();
        if ($this->step < $this->totalSteps) {
            $this->step++;

            if ($this->step === 3 && $this->audience_type === 'import') {
                // Prepare preview data for compose step
                $this->preparePreviewData();
            }
        }
    }

    public function prevStep()
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function validateStep()
    {
        if ($this->step === 1) {
            $this->validate([
                'name' => 'required|min:3',
                'device_id' => 'required|exists:devices,id',
            ]);
        } elseif ($this->step === 2) {
            if ($this->audience_type === 'group') {
                $this->validate(['selected_groups' => 'required|array|min:1']);
            } else {
                $this->validate([
                    'csv_file' => 'required|file|mimes:csv,txt,xlsx,xls',
                    'column_mapping' => 'required|array',
                ]);
                // Check if Phone is mapped
                if (!in_array('phone', $this->column_mapping)) {
                    $this->addError('column_mapping', 'You must map a column to the Phone Number field.');
                    throw new \Illuminate\Validation\ValidationException($this->validator);
                }
            }
        } elseif ($this->step === 3) {
            $this->validate(['message' => 'required|min:1']);
        }
    }

    public function downloadTemplate()
    {
        return response()->streamDownload(function () {
            $options = new \OpenSpout\Writer\XLSX\Options();
            $writer = new \OpenSpout\Writer\XLSX\Writer($options);
            $writer->openToFile('php://output');

            // Header row
            $headerRow = \OpenSpout\Common\Entity\Row::fromValues(['Phone', 'Name', 'Tagihan', 'Tanggal_Jatuh_Tempo']);
            $writer->addRow($headerRow);

            // Sample data rows
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues(['6281234567890', 'John Doe', '150000', '2025-01-20']));
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues(['6289876543210', 'Jane Smith', '250000', '2025-01-25']));

            $writer->close();
        }, 'broadcast_template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // File Handling (CSV/Excel)
    public function updatedCsvFile()
    {
        $this->validate(['csv_file' => 'file|max:10240|mimes:csv,txt,xlsx,xls']); // 10MB
        $this->parseFileHeaders();
    }

    public function parseFileHeaders()
    {
        $path = $this->csv_file->getRealPath();
        $extension = strtolower($this->csv_file->getClientOriginalExtension());

        $headers = [];

        if (in_array($extension, ['xlsx', 'xls'])) {
            // Excel file
            $reader = new \OpenSpout\Reader\XLSX\Reader();
            $reader->open($path);
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $headers = array_map(fn($cell) => (string) $cell->getValue(), $row->getCells());
                    break; // Only first row
                }
                break; // Only first sheet
            }
            $reader->close();
        } else {
            // CSV file
            $file = fopen($path, 'r');
            $headers = fgetcsv($file);
            fclose($file);
        }

        if ($headers) {
            $this->csv_headers = $headers;
            // Auto-guess mapping
            foreach ($headers as $header) {
                $lower = strtolower($header);
                if (in_array($lower, ['phone', 'mobile', 'wa', 'whatsapp', 'no_hp'])) {
                    $this->column_mapping[$header] = 'phone';
                } elseif (in_array($lower, ['name', 'nama', 'fullname'])) {
                    $this->column_mapping[$header] = 'variable:name';
                } else {
                    $this->column_mapping[$header] = 'variable:' . \Illuminate\Support\Str::slug($header, '_');
                }
            }

            // Generate preview
            $this->generateFilePreview();
        }
    }

    public function generateFilePreview()
    {
        $path = $this->csv_file->getRealPath();
        $extension = strtolower($this->csv_file->getClientOriginalExtension());

        $this->csv_preview = [];

        if (in_array($extension, ['xlsx', 'xls'])) {
            // Excel file
            $reader = new \OpenSpout\Reader\XLSX\Reader();
            $reader->open($path);
            $rowIndex = 0;
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    if ($rowIndex === 0) {
                        $rowIndex++;
                        continue; // Skip header
                    }
                    if ($rowIndex > 5)
                        break;

                    $cells = array_map(fn($cell) => (string) $cell->getValue(), $row->getCells());
                    $item = [];
                    foreach ($this->csv_headers as $index => $header) {
                        $item[$header] = $cells[$index] ?? '';
                    }
                    $this->csv_preview[] = $item;
                    $rowIndex++;
                }
                break; // Only first sheet
            }
            $reader->close();
        } else {
            // CSV file
            $file = fopen($path, 'r');
            fgetcsv($file); // Skip header

            for ($i = 0; $i < 5; $i++) {
                $row = fgetcsv($file);
                if ($row) {
                    $item = [];
                    foreach ($this->csv_headers as $index => $header) {
                        $item[$header] = $row[$index] ?? '';
                    }
                    $this->csv_preview[] = $item;
                } else {
                    break;
                }
            }
            fclose($file);
        }
    }

    public function nextPreview()
    {
        if ($this->preview_index < count($this->preview_recipients) - 1) {
            $this->preview_index++;
        }
    }

    public function prevPreview()
    {
        if ($this->preview_index > 0) {
            $this->preview_index--;
        }
    }

    public function preparePreviewData()
    {
        $this->preview_recipients = [];

        if ($this->audience_type === 'group') {
            $contacts = \App\Models\Contact::whereIn('contact_group_id', $this->selected_groups)
                ->inRandomOrder()
                ->limit(5)
                ->get();

            foreach ($contacts as $contact) {
                $this->preview_recipients[] = [
                    'name' => $contact->name,
                    'phone' => $contact->phone_number,
                ];
            }
        } elseif ($this->audience_type === 'import') {
            // Transform CSV preview rows based on mapping
            foreach ($this->csv_preview as $row) {
                $recipient = [];
                foreach ($this->column_mapping as $header => $mapTarget) {
                    if ($mapTarget === 'ignore')
                        continue;

                    $value = $row[$header] ?? '';

                    if ($mapTarget === 'phone') {
                        $recipient['phone'] = $value;
                    } elseif ($mapTarget === 'variable:name') {
                        $recipient['name'] = $value;
                    } elseif (str_starts_with($mapTarget, 'variable:')) {
                        $varName = str_replace('variable:', '', $mapTarget);
                        $recipient[$varName] = $value;
                    }
                }

                // Ensure required fields
                $recipient['phone'] = $recipient['phone'] ?? '';
                $recipient['name'] = $recipient['name'] ?? $recipient['phone'];

                $this->preview_recipients[] = $recipient;
            }
        }
    }

    public function send()
    {
        $this->validate([
            'delay_seconds' => 'required|integer|min:5',
        ]);

        $this->saveCampaign('running');
    }

    public function saveCampaign($status = 'draft')
    {
        $campaign = Campaign::create([
            'user_id' => Auth::id(),
            'device_id' => $this->device_id,
            'name' => $this->name,
            'message' => $this->message,
            'audience_type' => $this->audience_type,
            'target_groups' => $this->audience_type === 'group' ? $this->selected_groups : null,
            'status' => $status,
            'scheduled_at' => $this->schedule_date ? ($this->schedule_date . ' ' . ($this->schedule_time ?: '00:00')) : null,
            'error_mode' => $this->error_mode,
            'delay_seconds' => $this->delay_seconds,
            'mapping_config' => $this->column_mapping,
            // 'file_path' => ... if we store file
        ]);

        // Process Recipients
        if ($this->audience_type === 'group') {
            $contacts = \App\Models\Contact::whereIn('contact_group_id', $this->selected_groups)->get();
            foreach ($contacts as $contact) {
                CampaignRecipient::create([
                    'campaign_id' => $campaign->id,
                    'contact_id' => $contact->id,
                    'phone_number' => $contact->phone_number,
                    'name' => $contact->name,
                    'status' => 'pending',
                ]);
            }
        } elseif ($this->audience_type === 'import') {
            // Read file (CSV or Excel) and insert recipients
            $path = $this->csv_file->getRealPath();
            $extension = strtolower($this->csv_file->getClientOriginalExtension());

            $rows = [];

            if (in_array($extension, ['xlsx', 'xls'])) {
                // Excel file
                $reader = new \OpenSpout\Reader\XLSX\Reader();
                $reader->open($path);
                $isHeader = true;
                foreach ($reader->getSheetIterator() as $sheet) {
                    foreach ($sheet->getRowIterator() as $row) {
                        if ($isHeader) {
                            $isHeader = false;
                            continue; // Skip header
                        }
                        $rows[] = array_map(fn($cell) => (string) $cell->getValue(), $row->getCells());
                    }
                    break; // Only first sheet
                }
                $reader->close();
            } else {
                // CSV file
                $file = fopen($path, 'r');
                fgetcsv($file); // skip header
                while (($row = fgetcsv($file)) !== false) {
                    $rows[] = $row;
                }
                fclose($file);
            }

            foreach ($rows as $row) {
                // Map data using column_mapping and csv_headers
                $assocRow = [];
                foreach ($this->csv_headers as $idx => $h) {
                    $assocRow[$h] = $row[$idx] ?? '';
                }

                $phone = '';
                $name = '';
                $custom_data = [];

                foreach ($this->column_mapping as $header => $target) {
                    $val = $assocRow[$header] ?? '';
                    if ($target === 'phone')
                        $phone = $val;
                    elseif ($target === 'variable:name')
                        $name = $val;
                    elseif ($target !== 'ignore') {
                        $varName = str_replace('variable:', '', $target);
                        $custom_data[$varName] = $val;
                    }
                }

                if ($phone) {
                    CampaignRecipient::create([
                        'campaign_id' => $campaign->id,
                        'phone_number' => $phone,
                        'name' => $name ?: $phone,
                        'custom_data' => $custom_data,
                        'status' => 'pending',
                    ]);
                }
            }
        }

        $campaign->update(['total_recipients' => $campaign->recipients()->count()]);

        if ($status === 'running' && !$campaign->schedule_at) {
            // Trigger Processing Immediately (Dispatch Job or Command)
            // For now, we will notify user to run processor or auto-run if we have a runner
            // Dispatch job here if configured
            \App\Jobs\ProcessCampaignJob::dispatch($campaign->id);
        }

        return redirect()->route('campaigns.index')->with('message', 'Campaign ' . ($status === 'draft' ? 'saved' : 'launched') . '!');
    }

    public function render()
    {
        return view('livewire.campaigns.broadcast-wizard', [
            'devices' => Auth::user()->devices()->where('status', 'connected')->get(),
            'groups' => Auth::user()->contactGroups()->withCount('contacts')->get(),
            'templates' => Auth::user()->messageTemplates()->get(),
        ]);
    }
}
