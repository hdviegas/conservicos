<?php

namespace App\Filament\Resources\Exams\Schemas;

use App\Enums\ExamCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ExamForm
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
                ->options(collect(ExamCategory::cases())->mapWithKeys(
                    fn (ExamCategory $c) => [$c->value => $c->label()]
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
                ->hint('0 = sem validade'),
            Toggle::make('is_mandatory')
                ->label('Obrigatório')
                ->default(false),
            Toggle::make('requires_attachment')
                ->label('Requer Anexo (ASO)')
                ->default(false),
            Toggle::make('active')
                ->label('Ativo')
                ->default(true),
        ])->columns(2);
    }
}
