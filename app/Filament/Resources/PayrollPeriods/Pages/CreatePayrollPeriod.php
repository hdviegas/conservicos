<?php

namespace App\Filament\Resources\PayrollPeriods\Pages;

use App\Enums\PayrollStatus;
use App\Filament\Resources\PayrollPeriods\PayrollPeriodResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePayrollPeriod extends CreateRecord
{
    protected static string $resource = PayrollPeriodResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = PayrollStatus::Draft->value;

        return $data;
    }
}
