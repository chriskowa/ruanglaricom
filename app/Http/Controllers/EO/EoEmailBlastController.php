<?php

namespace App\Http\Controllers\EO;

use App\Http\Controllers\Controller;
use App\Models\EoEmailBlast;
use App\Models\EoEmailBlastDelivery;
use App\Models\Event;
use App\Jobs\ProcessEoEmailBlast;
use App\Mail\EoCustomBlastEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EoEmailBlastController extends Controller
{
    public function index(Request $request)
    {
        $eventId = $request->query('event');
        $event = $eventId ? Event::find($eventId) : null;

        $query = EoEmailBlast::where('eo_user_id', Auth::id());
        
        if ($event) {
            $query->where('event_id', $event->id);
        }

        $blasts = $query->latest()->paginate(15);

        return view('eo.blasts.index', compact('blasts', 'event'));
    }

    public function create(Request $request)
    {
        $eventId = $request->query('event');
        $event = $eventId ? Event::find($eventId) : null;

        return view('eo.blasts.create', compact('event'));
    }

    public function store(Request $request)
    {
        $eventId = $request->query('event');
        $event = $eventId ? Event::find($eventId) : null;

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'subject_template' => 'required|string|max:255',
            'html_template' => 'required|string',
            'source_type' => 'required|in:single,csv',
            'to_emails' => 'required_if:source_type,single|nullable|string|max:2000',
            'to_name' => 'nullable|string|max:255',
            'csv_file' => 'required_if:source_type,csv|nullable|file|mimes:csv,txt|max:10240',
            'email_column' => 'required_if:source_type,csv|nullable|string',
            'name_column' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $blast = new EoEmailBlast([
            'eo_user_id' => Auth::id(),
            'event_id' => $event ? $event->id : null,
            'name' => $request->name,
            'subject_template' => $request->subject_template,
            'html_template' => $request->html_template,
            'source_type' => $request->source_type,
            'status' => 'processing',
        ]);

        if ($request->source_type === 'csv') {
            $file = $request->file('csv_file');
            $path = $file->store('eo_email_blasts_csv');
            
            $blast->csv_original_name = $file->getClientOriginalName();
            $blast->csv_path = $path;
            $blast->email_column = $request->email_column;
            $blast->name_column = $request->name_column;
        }

        $blast->save();

        if ($blast->source_type === 'single') {
            $rawEmails = (string) $request->input('to_emails', '');
            $tokens = preg_split('/[,\r\n]+/', $rawEmails) ?: [];

            $emails = [];
            foreach ($tokens as $t) {
                $email = trim((string) $t);
                if ($email === '') {
                    continue;
                }
                $emails[] = $email;
            }

            $emails = array_values(array_unique($emails));

            $validEmails = [];
            $invalidCount = 0;
            foreach ($emails as $email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $validEmails[] = $email;
                } else {
                    $invalidCount++;
                }
            }

            $ignoredExtraCount = max(0, count($validEmails) - 10);
            $validEmails = array_slice($validEmails, 0, 10);

            if (count($validEmails) === 0) {
                $blast->update([
                    'status' => 'failed',
                    'target_count' => 0,
                    'sent_count' => 0,
                    'failed_count' => 0,
                ]);

                return redirect()->back()->withInput()->with('error', 'Tidak ada email valid untuk dikirim.');
            }

            $blast->update([
                'status' => 'processing',
                'target_count' => count($validEmails),
                'sent_count' => 0,
                'failed_count' => 0,
            ]);

            $sentCount = 0;
            $failedCount = 0;
            $defaultName = $request->input('to_name');

            foreach ($validEmails as $email) {
                $payload = [
                    'email' => $email,
                    'name' => $defaultName,
                ];

                $subject = $this->replacePlaceholders($blast->subject_template, $payload);
                $htmlBody = $this->replacePlaceholders($blast->html_template, $payload);

                $delivery = EoEmailBlastDelivery::create([
                    'eo_email_blast_id' => $blast->id,
                    'to_email' => $email,
                    'to_name' => $defaultName,
                    'payload' => $payload,
                    'status' => 'queued',
                ]);

                try {
                    Mail::to($delivery->to_email)->send(new EoCustomBlastEmail($subject, $htmlBody));

                    $delivery->update([
                        'status' => 'sent',
                        'rendered_subject' => $subject,
                        'sent_at' => now(),
                        'error_message' => null,
                    ]);

                    $sentCount++;
                } catch (\Throwable $e) {
                    report($e);

                    $delivery->update([
                        'status' => 'failed',
                        'rendered_subject' => $subject,
                        'error_message' => $e->getMessage(),
                    ]);

                    $failedCount++;
                }
            }

            $blast->update([
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'status' => $sentCount > 0 ? 'completed' : 'failed',
            ]);

            $msg = "Terkirim {$sentCount} dari {$blast->target_count}.";
            if ($failedCount > 0) {
                $msg .= " Gagal: {$failedCount}.";
            }
            if ($invalidCount > 0) {
                $msg .= " Email tidak valid diabaikan: {$invalidCount}.";
            }
            if ($ignoredExtraCount > 0) {
                $msg .= " Melebihi batas (10) diabaikan: {$ignoredExtraCount}.";
            }

            return redirect()->route('eo.blasts.show', ['blast' => $blast->id, 'event' => $event ? $event->id : null])
                ->with($sentCount > 0 ? 'success' : 'error', $msg);
        }

        ProcessEoEmailBlast::dispatch($blast);

        return redirect()->route('eo.blasts.show', ['blast' => $blast->id, 'event' => $event ? $event->id : null])
            ->with('success', 'Email blast has been queued for processing.');
    }

    public function show(Request $request, $blast_id)
    {
        // Parameter might be passed as (event, blast) or just (blast). Let's resolve it safely.
        $blast = EoEmailBlast::where('id', $blast_id)->where('eo_user_id', Auth::id())->firstOrFail();
        $event = $blast->event;

        $deliveries = $blast->deliveries()->paginate(50);

        return view('eo.blasts.show', compact('blast', 'deliveries', 'event'));
    }

    public function preview(Request $request)
    {
        $subjectTemplate = $request->input('subject_template', '');
        $htmlTemplate = $request->input('html_template', '');
        $payload = $request->input('payload', []);

        $renderedSubject = $this->replacePlaceholders($subjectTemplate, $payload);
        $renderedHtml = $this->replacePlaceholders($htmlTemplate, $payload);

        return response()->json([
            'subject' => $renderedSubject,
            'html' => $renderedHtml
        ]);
    }

    public function parseCsvHeader(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240'
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        $rows = [];
        $headers = [];
        
        if (($handle = fopen($path, "r")) !== FALSE) {
            $headers = fgetcsv($handle, 10000, ",");
            if ($headers) {
                // Normalize headers
                $headers = array_map(function($header) {
                    return trim($header);
                }, $headers);

                $rowCount = 0;
                while (($data = fgetcsv($handle, 10000, ",")) !== FALSE && $rowCount < 5) {
                    $row = [];
                    foreach ($headers as $index => $header) {
                        $key = Str::slug($header, '_');
                        $row[$key] = $data[$index] ?? '';
                    }
                    $rows[] = $row;
                    $rowCount++;
                }
            }
            fclose($handle);
        }

        $normalizedHeaders = array_map(function($header) {
            return Str::slug($header, '_');
        }, $headers);

        return response()->json([
            'headers' => $normalizedHeaders,
            'sample_rows' => $rows
        ]);
    }

    private function replacePlaceholders($template, $payload)
    {
        if (!$template) return '';
        
        return preg_replace_callback('/\{\{([a-zA-Z0-9_]+)\}\}/', function($matches) use ($payload) {
            $key = $matches[1];
            return isset($payload[$key]) ? e($payload[$key]) : '';
        }, $template);
    }
}
