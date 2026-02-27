<?php

namespace App\Filament\Pages;

use App\Models\Company;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use Filament\Widgets\WidgetConfiguration;
use Livewire\Attributes\Url;

class Dashboard extends BaseDashboard
{
    #[Url(as: 'empresa')]
    public int|string|null $companyFilter = null;

    public function mount(): void
    {
        if ($this->companyFilter === null) {
            $this->companyFilter = Company::orderBy('id')->value('id');
        }
    }

    /**
     * @param  array<class-string<Widget> | WidgetConfiguration>  $widgets
     */
    public function getWidgetsSchemaComponents(array $widgets, array $data = []): array
    {
        return parent::getWidgetsSchemaComponents($widgets, array_merge($data, [
            'pageFilters' => ['company_id' => $this->companyFilter],
        ]));
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->schema([
                    Select::make('companyFilter')
                        ->label('Empresa')
                        ->options(Company::orderBy('name')->pluck('name', 'id'))
                        ->placeholder('Selecione uma empresa')
                        ->native(false)
                        ->live(),
                ])
                ->columns(3)
                ->columnSpanFull(),
            $this->getWidgetsContentComponent(),
        ]);
    }
}
