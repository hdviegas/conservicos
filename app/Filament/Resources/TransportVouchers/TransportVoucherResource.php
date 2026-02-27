<?php

namespace App\Filament\Resources\TransportVouchers;

use App\Filament\Resources\TransportVouchers\Pages\CreateTransportVoucher;
use App\Filament\Resources\TransportVouchers\Pages\EditTransportVoucher;
use App\Filament\Resources\TransportVouchers\Pages\ListTransportVouchers;
use App\Filament\Resources\TransportVouchers\Schemas\TransportVoucherForm;
use App\Filament\Resources\TransportVouchers\Tables\TransportVouchersTable;
use App\Models\TransportVoucher;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TransportVoucherResource extends Resource
{
    protected static ?string $model = TransportVoucher::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $navigationLabel = 'Vale Transporte';

    protected static ?string $modelLabel = 'Vale Transporte';

    protected static ?string $pluralModelLabel = 'Vales Transporte';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Benefícios';
    }

    public static function form(Schema $schema): Schema
    {
        return TransportVoucherForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TransportVouchersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTransportVouchers::route('/'),
            'create' => CreateTransportVoucher::route('/create'),
            'edit' => EditTransportVoucher::route('/{record}/edit'),
        ];
    }
}
