<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\AbsencesChartWidget;
use App\Filament\Widgets\ComplianceAlertTable;
use App\Filament\Widgets\ComplianceOverviewStats;
use App\Filament\Widgets\MonthlyOvertimeChartWidget;
use App\Filament\Widgets\PendingPaymentsWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\UpcomingVacationsWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use App\Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Blue,
                'gray' => Color::Slate,
                'info' => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger' => Color::Rose,
            ])
            ->brandName('CONSERVICOS')
            ->darkMode(false)
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                NavigationGroup::make()->label('Cadastros'),
                NavigationGroup::make()->label('Gestão de Pessoas'),
                NavigationGroup::make()->label('Ponto'),
                NavigationGroup::make()->label('Folha de Pagamento'),
                NavigationGroup::make()->label('Benefícios'),
                NavigationGroup::make()->label('Compliance'),
                NavigationGroup::make()->label('Pagamentos'),
                NavigationGroup::make()->label('Relatórios'),
                NavigationGroup::make()->label('Configurações'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                StatsOverviewWidget::class,
                MonthlyOvertimeChartWidget::class,
                AbsencesChartWidget::class,
                ComplianceOverviewStats::class,
                UpcomingVacationsWidget::class,
                PendingPaymentsWidget::class,
                ComplianceAlertTable::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                \Illuminate\View\Middleware\ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                'panels::head.start',
                fn () => view('filament.hooks.custom-assets'),
            );
    }
}
