<?php

namespace App\Filament\Resources\TransportVoucherTypes;

use App\Filament\Resources\TransportVoucherTypes\Pages\CreateTransportVoucherType;
use App\Filament\Resources\TransportVoucherTypes\Pages\EditTransportVoucherType;
use App\Filament\Resources\TransportVoucherTypes\Pages\ListTransportVoucherTypes;
use App\Filament\Resources\TransportVoucherTypes\Schemas\TransportVoucherTypeForm;
use App\Filament\Resources\TransportVoucherTypes\Tables\TransportVoucherTypesTable;
use App\Models\TransportVoucherType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TransportVoucherTypeResource extends Resource
{
    protected static ?string $model = TransportVoucherType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $navigationLabel = 'Tipos de Vale Transporte';

    protected static ?string $modelLabel = 'Tipo de Vale Transporte';

    protected static ?string $pluralModelLabel = 'Tipos de Vale Transporte';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'Configurações';
    }

    public static function form(Schema $schema): Schema
    {
        return TransportVoucherTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TransportVoucherTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTransportVoucherTypes::route('/'),
            'create' => CreateTransportVoucherType::route('/create'),
            'edit' => EditTransportVoucherType::route('/{record}/edit'),
        ];
    }
}
