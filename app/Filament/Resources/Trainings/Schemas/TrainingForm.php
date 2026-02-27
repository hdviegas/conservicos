<?php

namespace App\Filament\Resources\Trainings\Schemas;

use App\Enums\TrainingCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TrainingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nome')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            Textarea::make('description')
                ->label('Descrição')
                ->maxLength(1000)
                ->columnSpanFull(),
            Select::make('category')
                ->label('Categoria')
                ->options(collect(TrainingCategory::cases())->mapWithKeys(
                    fn (TrainingCategory $c) => [$c->value => $c->label()]
                ))
                ->required(),
            TextInput::make('nr_reference')
                ->label('Referência NR')
                ->maxLength(10)
                ->placeholder('Ex: NR-11'),
            TextInput::make('validity_months')
                ->label('Validade (meses)')
                ->numeric()
                ->default(0)
                ->minValue(0)
                ->hint('0 = único/sem renovação'),
            TextInput::make('required_hours')
                ->label('Carga Horária (horas)')
                ->numeric()
                ->minValue(1),
            Toggle::make('is_mandatory')
                ->label('Obrigatório')
                ->default(false),
            Toggle::make('requires_certificate')
                ->label('Requer Certificado')
                ->default(false),
            Toggle::make('active')
                ->label('Ativo')
                ->default(true),
        ])->columns(2);
    }
}
