<?php

namespace App\Filament\Resources\Positions\RelationManagers;

use App\Models\Exam;
use App\Models\Training;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ComplianceRequirementsRelationManager extends RelationManager
{
    protected static string $relationship = 'complianceRequirements';

    protected static ?string $title = 'Exigências de Compliance';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('requireable_type')
                ->label('Tipo')
                ->options([
                    Exam::class => 'Exame',
                    Training::class => 'Treinamento',
                ])
                ->required()
                ->live(),
            Select::make('requireable_id')
                ->label('Item')
                ->options(fn (Get $get) => match ($get('requireable_type')) {
                    Exam::class => Exam::active()->orderBy('name')->pluck('name', 'id'),
                    Training::class => Training::active()->orderBy('name')->pluck('name', 'id'),
                    default => [],
                })
                ->required()
                ->searchable(),
            Toggle::make('is_mandatory')
                ->label('Obrigatório')
                ->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('requireable_type')
                    ->label('Tipo')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        Exam::class => 'Exame',
                        Training::class => 'Treinamento',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        Exam::class => 'info',
                        Training::class => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('requireable.name')
                    ->label('Nome')
                    ->searchable(),
                IconColumn::make('is_mandatory')
                    ->label('Obrigatório')
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make()->label('Adicionar Exigência'),
            ])
            ->recordActions([
                DeleteAction::make(),
            ]);
    }
}
