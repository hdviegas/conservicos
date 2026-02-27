<?php

namespace App\Jobs;

use App\Services\GenerateTransportVouchersService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateTransportVouchersJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Carbon $periodStart,
        public readonly Carbon $periodEnd,
    ) {}

    public function handle(GenerateTransportVouchersService $service): void
    {
        $result = $service->generateForPeriod($this->periodStart, $this->periodEnd);

        \Illuminate\Support\Facades\Log::info('GenerateTransportVouchersJob completed', [
            'generated' => $result['vouchers']->count(),
            'skipped' => $result['skipped'],
        ]);
    }
}
