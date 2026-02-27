<?php

namespace App\Filament\Resources\Departments\Schemas;

use App\Models\Company;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->label('Empresa')
                    ->options(Company::active()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                TextInput::make('name')
                    ->label('Departamento')
                    ->required()
                    ->maxLength(255),
                Toggle::make('active')
                    ->label('Ativo')
                    ->default(true),
            ]);
    }
}
