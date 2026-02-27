<?php

namespace App\Jobs;

use App\Enums\ImportStatus;
use App\Models\TimeImport;
use App\Services\TimeReportImporterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessTimeReportImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public int $tries = 3;

    public function __construct(
        public readonly int $timeImportId
    ) {}

    public function handle(TimeReportImporterService $service): void
    {
        $timeImport = TimeImport::find($this->timeImportId);

        if (! $timeImport) {
            return;
        }

        $timeImport->update(['status' => ImportStatus::Processing]);

        try {
            $filePath = Storage::disk('local')->path($timeImport->filename);

            if (! file_exists($filePath)) {
                $timeImport->update([
                    'status' => ImportStatus::Failed,
                    'errors' => ['Arquivo não encontrado: ' . $timeImport->filename],
                ]);

                return;
            }

            $result = $service->process($timeImport, $filePath);

            $timeImport->update([
                'status'        => ImportStatus::Completed,
                'records_count' => $result['records_count'],
                'errors'        => ! empty($result['errors']) ? $result['errors'] : null,
                'imported_at'   => now(),
            ]);
        } catch (\Throwable $e) {
            $timeImport->update([
                'status' => ImportStatus::Failed,
                'errors' => [$e->getMessage()],
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        TimeImport::find($this->timeImportId)?->update([
            'status' => ImportStatus::Failed,
            'errors' => [$exception->getMessage()],
        ]);
    }
}
