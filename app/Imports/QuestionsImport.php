<?php

namespace App\Imports;

use App\Models\Question;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\ImportFailed;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class QuestionsImport implements ToCollection, WithBatchInserts, WithStartRow, WithEvents, WithChunkReading, ShouldQueue
{
    use RegistersEventListeners;

    protected $jobId;
    protected $errors = [];
    protected $success_rows = 0; // Add a counter for processed rows
    protected const NOTIFICATION_EMAIL = 'bebo2xd@gmail.com';

    public function __construct(protected $category_id)
    {
        $this->jobId = uniqid('import_');
    }

    public function startRow(): int
    {
        return 2;
    }

    public function chunkSize(): int
    {
        return 250;
    }

    public function batchSize(): int
    {
        return 250;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                $this->success_rows = +1; // Increment the counter for successful imports
                Question::updateOrCreate(
                    [
                        'question' => $row[0],
                        'category_id' => $this->category_id,
                    ],
                    [
                        'answer' => $row[1],
                        'question_media_url' => $this->extractPath($row[2] ?? null),
                        'answer_media_url' => $this->extractPath($row[3] ?? null),
                        'level' => $row[4],
                        'diff' => $row[5],
                    ]
                );
            } catch (\Exception $e) {
                $this->errors[] = "Row " . ($index + $this->startRow()) . ": " . $e->getMessage();
                Log::error("Import error on row " . ($index + $this->startRow()) . ": " . json_encode($row) . " - Error: " . $e->getMessage());
            }
        }
    }

    protected function extractPath($url)
    {
        if (!$url) {
            return null;
        }
        return ltrim(parse_url($url, PHP_URL_PATH), '/');
    }

    public function registerEvents(): array
    {
        return [
            ImportFailed::class => function (ImportFailed $event) {
                $failureReason = $event->getException()->getMessage();
                $this->logNotification('failed', "The import process has failed. Reason:\n " . $failureReason);
            },
            AfterImport::class => function (AfterImport $event) {
                if (!empty($this->errors)) {
                    $message = "Import Job ID: {$this->jobId} - Failed, Errors encountered:\n" . implode("\n", $this->errors);
                    $this->logNotification('partial success', $message);
                } else {
                    $message = "Import Job ID: {$this->jobId} - Imported successfully";
                    $this->logNotification('success', $message);
                }
            },
        ];
    }

    protected function logNotification($status, $message)
    {
        Log::info("Questions Import Report - Job ID: {$this->jobId} - " . ucfirst($status) . "\n" . $message);

        try {
            Mail::raw($message, function ($mail) use ($status) {
                $mail->to(self::NOTIFICATION_EMAIL)
                    ->subject("Questions Import Report - Job ID: {$this->jobId} - " . ucfirst($status));
            });
        } catch (\Exception $e) {
            Log::error("Failed to send email notification: " . $e->getMessage());
        }
    }

    public function getJobId()
    {
        return $this->jobId;
    }
}
