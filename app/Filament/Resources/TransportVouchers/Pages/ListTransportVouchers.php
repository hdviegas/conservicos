<?php

namespace App\Filament\Resources\TransportVouchers\Pages;

use App\Filament\Resources\TransportVouchers\TransportVoucherResource;
use App\Services\GenerateTransportVouchersService;
use App\Services\WorkingDaysCalculatorService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;

class ListTransportVouchers extends ListRecords
{
    protected static string $resource = TransportVoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateVouchers')
                ->label('Gerar Vales')
                ->icon(Heroicon::OutlinedBolt)
                ->color('success')
                ->form([
                    DatePicker::make('period_start')
                        ->label('Início do Período')
                        ->required()
                        ->displayFormat('d/m/Y')
                        ->default(now()->startOfMonth())
                        ->live()
                        ->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                            $start = $state ? Carbon::parse($state) : null;
                            $end   = $get('period_end') ? Carbon::parse($get('period_end')) : null;
                            if ($start && $end && $start->lte($end)) {
                                $set('workable_days', app(WorkingDaysCalculatorService::class)->calculateBaseline($start, $end));
                            }
                        }),
                    DatePicker::make('period_end')
                        ->label('Fim do Período')
                        ->required()
                        ->displayFormat('d/m/Y')
                        ->default(now()->endOfMonth())
                        ->live()
                        ->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                            $start = $get('period_start') ? Carbon::parse($get('period_start')) : null;
                            $end   = $state ? Carbon::parse($state) : null;
                            if ($start && $end && $start->lte($end)) {
                                $set('workable_days', app(WorkingDaysCalculatorService::class)->calculateBaseline($start, $end));
                            }
                        }),
                    TextInput::make('workable_days')
                        ->label('Dias Trabalháveis no Período')
                        ->helperText('Dias úteis (Seg–Sáb) menos feriados cadastrados. Na geração cada funcionário tem este valor ajustado pela sua escala (Seg–Sex recalcula; 12x36 usa ~50%; 6x2 usa ~75%), e então desconta faltas do período anterior.')
                        ->numeric()
                        ->integer()
                        ->minValue(1)
                        ->required()
                        ->default(fn () => app(WorkingDaysCalculatorService::class)->calculateBaseline(
                            now()->startOfMonth(),
                            now()->endOfMonth(),
                        ))
                        ->suffix('dias')
                ])
                ->modalHeading('Gerar Vales Transporte')
                ->modalDescription('Selecione o período e confirme a quantidade de dias trabalháveis.')
                ->modalSubmitActionLabel('Gerar Vales')
                ->action(function (array $data, GenerateTransportVouchersService $service): void {
                    $result = $service->generateForPeriod(
                        Carbon::parse($data['period_start']),
                        Carbon::parse($data['period_end']),
                        (int) $data['workable_days'],
                    );

                    $generated = $result['vouchers']->count();
                    $skipped = $result['skipped'];

                    if ($generated === 0) {
                        Notification::make()
                            ->title('Nenhum vale gerado')
                            ->body("Nenhum funcionário resultou em dias faturáveis para o período informado ({$skipped} ignorado(s)).")
                            ->warning()
                            ->send();

                        return;
                    }

                    $body = "{$generated} vale(s) gerado(s) com status Pendente.";
                    if ($skipped > 0) {
                        $body .= " {$skipped} funcionário(s) com 0 dias faturáveis ignorado(s).";
                    }

                    Notification::make()
                        ->title('Vales transporte gerados')
                        ->body($body)
                        ->success()
                        ->send();
                }),
            CreateAction::make()->label('Novo Vale Transporte'),
        ];
    }
}
