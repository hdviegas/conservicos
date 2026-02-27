<?php

namespace App\Filament\Pages;

use App\Models\Company;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class Reports extends Page
{
    protected static ?string $navigationLabel = 'Relatórios';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?int $navigationSort = 100;

    protected static ?string $title = 'Relatórios';

    public static function getNavigationGroup(): ?string
    {
        return 'Relatórios';
    }


    public function getCompanyOptions(): array
    {
        return Company::where('active', true)->orderBy('name')->pluck('name', 'id')->toArray();
    }

    public function getMonthOptions(): array
    {
        return [
            1  => 'Janeiro',
            2  => 'Fevereiro',
            3  => 'Março',
            4  => 'Abril',
            5  => 'Maio',
            6  => 'Junho',
            7  => 'Julho',
            8  => 'Agosto',
            9  => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro',
        ];
    }

    public function getYearOptions(): array
    {
        $currentYear = now()->year;
        $years = [];
        for ($y = $currentYear - 2; $y <= $currentYear + 1; $y++) {
            $years[$y] = (string) $y;
        }
        return $years;
    }

    public function getCurrentMonth(): int
    {
        return now()->month;
    }

    public function getCurrentYear(): int
    {
        return now()->year;
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            // ===== 1. FOLHA DE PAGAMENTO MENSAL =====
            Section::make('Folha de Pagamento Mensal')
                ->description('Exporta todos os cálculos de folha para uma empresa e período. Formato XLSX para o contador.')
                ->icon('heroicon-o-document-text')
                ->aside()
                ->schema([
                    Select::make('company_id')
                        ->label('Empresa')
                        ->options($this->getCompanyOptions())
                        ->required()
                        ->placeholder('Selecione...')
                        ->native(false),
                    Select::make('month')
                        ->label('Mês')
                        ->options($this->getMonthOptions())
                        ->required()
                        ->default($this->getCurrentMonth())
                        ->native(false),
                    Select::make('year')
                        ->label('Ano')
                        ->options($this->getYearOptions())
                        ->required()
                        ->default($this->getCurrentYear())
                        ->native(false),
                ])
                ->columns(3)
                ->footerActions([
                    Action::make('export_payroll')
                        ->label('Exportar XLSX')
                        ->icon(Heroicon::OutlinedArrowDownTray)
                        ->color('primary')
                        ->url(fn () => route('reports.payroll', request()->query()))
                        ->openUrlInNewTab(),
                ]),

            // ===== 2. HORAS EXTRAS =====
            Section::make('Horas Extras por Funcionário')
                ->description('Resumo de horas extras 50% e 100% por funcionário no período.')
                ->icon('heroicon-o-clock')
                ->aside()
                ->schema([
                    Select::make('company_id')
                        ->label('Empresa')
                        ->options($this->getCompanyOptions())
                        ->required()
                        ->placeholder('Selecione...')
                        ->native(false),
                    Select::make('month')
                        ->label('Mês')
                        ->options($this->getMonthOptions())
                        ->required()
                        ->default($this->getCurrentMonth())
                        ->native(false),
                    Select::make('year')
                        ->label('Ano')
                        ->options($this->getYearOptions())
                        ->required()
                        ->default($this->getCurrentYear())
                        ->native(false),
                ])
                ->columns(3)
                ->footerActions([
                    Action::make('export_overtime')
                        ->label('Exportar XLSX')
                        ->icon(Heroicon::OutlinedArrowDownTray)
                        ->color('warning')
                        ->url(fn () => route('reports.overtime', request()->query()))
                        ->openUrlInNewTab(),
                ]),

            // ===== 3. BANCO DE HORAS =====
            Section::make('Banco de Horas')
                ->description('Saldos de banco de horas de todos os funcionários ativos.')
                ->icon('heroicon-o-banknotes')
                ->aside()
                ->schema([
                    Select::make('company_id')
                        ->label('Empresa')
                        ->options($this->getCompanyOptions())
                        ->placeholder('Todas as empresas')
                        ->native(false),
                    Select::make('month')
                        ->label('Mês')
                        ->options($this->getMonthOptions())
                        ->required()
                        ->default($this->getCurrentMonth())
                        ->native(false),
                    Select::make('year')
                        ->label('Ano')
                        ->options($this->getYearOptions())
                        ->required()
                        ->default($this->getCurrentYear())
                        ->native(false),
                ])
                ->columns(3)
                ->footerActions([
                    Action::make('export_hours_bank')
                        ->label('Exportar XLSX')
                        ->icon(Heroicon::OutlinedArrowDownTray)
                        ->color('info')
                        ->url(fn () => route('reports.hours-bank', request()->query()))
                        ->openUrlInNewTab(),
                ]),

            // ===== 4. FALTAS E ATESTADOS =====
            Section::make('Faltas e Atestados')
                ->description('Todas as faltas e atestados do período. Disponível em XLSX e PDF.')
                ->icon('heroicon-o-calendar-days')
                ->aside()
                ->schema([
                    Select::make('company_id')
                        ->label('Empresa')
                        ->options($this->getCompanyOptions())
                        ->placeholder('Todas as empresas')
                        ->native(false),
                    Select::make('month')
                        ->label('Mês')
                        ->options($this->getMonthOptions())
                        ->required()
                        ->default($this->getCurrentMonth())
                        ->native(false),
                    Select::make('year')
                        ->label('Ano')
                        ->options($this->getYearOptions())
                        ->required()
                        ->default($this->getCurrentYear())
                        ->native(false),
                ])
                ->columns(3)
                ->footerActions([
                    Action::make('export_absences_xlsx')
                        ->label('XLSX')
                        ->icon(Heroicon::OutlinedArrowDownTray)
                        ->color('danger')
                        ->url(fn () => route('reports.absences-xlsx', request()->query()))
                        ->openUrlInNewTab(),
                    Action::make('export_absences_pdf')
                        ->label('PDF')
                        ->icon(Heroicon::OutlinedDocument)
                        ->color('gray')
                        ->url(fn () => route('reports.absences-pdf', request()->query()))
                        ->openUrlInNewTab(),
                ]),

            // ===== 5. VALE TRANSPORTE =====
            Section::make('Vale Transporte Mensal')
                ->description('Resumo de vale transporte por funcionário para o período.')
                ->icon('heroicon-o-ticket')
                ->aside()
                ->schema([
                    Select::make('company_id')
                        ->label('Empresa')
                        ->options($this->getCompanyOptions())
                        ->required()
                        ->placeholder('Selecione...')
                        ->native(false),
                    Select::make('month')
                        ->label('Mês')
                        ->options($this->getMonthOptions())
                        ->required()
                        ->default($this->getCurrentMonth())
                        ->native(false),
                    Select::make('year')
                        ->label('Ano')
                        ->options($this->getYearOptions())
                        ->required()
                        ->default($this->getCurrentYear())
                        ->native(false),
                ])
                ->columns(3)
                ->footerActions([
                    Action::make('export_transport_voucher')
                        ->label('Exportar XLSX')
                        ->icon(Heroicon::OutlinedArrowDownTray)
                        ->color('success')
                        ->url(fn () => route('reports.transport-voucher', request()->query()))
                        ->openUrlInNewTab(),
                ]),

            // ===== 6. COMPLIANCE =====
            Section::make('Exames e Treinamentos Vencendo')
                ->description('Relatório de compliance com exames e treinamentos vencidos ou a vencer. Disponível em XLSX e PDF.')
                ->icon('heroicon-o-shield-check')
                ->aside()
                ->schema([
                    Select::make('company_id')
                        ->label('Empresa')
                        ->options($this->getCompanyOptions())
                        ->placeholder('Todas as empresas')
                        ->native(false),
                    Select::make('status_filter')
                        ->label('Status')
                        ->options([
                            '' => 'Todos os pendentes',
                            'expired' => 'Vencidos',
                            'expiring_15d' => 'Vencendo em 15 dias',
                            'expiring_30d' => 'Vencendo em 30 dias',
                        ])
                        ->placeholder('Todos os pendentes')
                        ->native(false),
                ])
                ->columns(2)
                ->footerActions([
                    Action::make('export_compliance_xlsx')
                        ->label('XLSX')
                        ->icon(Heroicon::OutlinedArrowDownTray)
                        ->color('primary')
                        ->url(fn () => route('reports.compliance-xlsx', request()->query()))
                        ->openUrlInNewTab(),
                    Action::make('export_compliance_pdf')
                        ->label('PDF')
                        ->icon(Heroicon::OutlinedDocument)
                        ->color('gray')
                        ->url(fn () => route('reports.compliance-pdf', request()->query()))
                        ->openUrlInNewTab(),
                ]),

            // ===== 7. PAGAMENTOS BANCÁRIOS =====
            Section::make('Pagamentos Bancários por Lote')
                ->description('Resumo de lotes de pagamento bancário por empresa e período.')
                ->icon('heroicon-o-building-library')
                ->aside()
                ->schema([
                    Select::make('company_id')
                        ->label('Empresa')
                        ->options($this->getCompanyOptions())
                        ->placeholder('Todas as empresas')
                        ->native(false),
                    Select::make('month')
                        ->label('Mês')
                        ->options($this->getMonthOptions())
                        ->required()
                        ->default($this->getCurrentMonth())
                        ->native(false),
                    Select::make('year')
                        ->label('Ano')
                        ->options($this->getYearOptions())
                        ->required()
                        ->default($this->getCurrentYear())
                        ->native(false),
                ])
                ->columns(3)
                ->footerActions([
                    Action::make('export_payments')
                        ->label('Exportar XLSX')
                        ->icon(Heroicon::OutlinedArrowDownTray)
                        ->color('primary')
                        ->url(fn () => route('reports.payments', request()->query()))
                        ->openUrlInNewTab(),
                ]),
        ]);
    }
}
