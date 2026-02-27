<?php

namespace App\Filament\Resources\PaymentBatches;

use App\Filament\Resources\PaymentBatches\Pages\CreatePaymentBatch;
use App\Filament\Resources\PaymentBatches\Pages\EditPaymentBatch;
use App\Filament\Resources\PaymentBatches\Pages\ListPaymentBatches;
use App\Filament\Resources\PaymentBatches\RelationManagers\PaymentBatchItemsRelationManager;
use App\Filament\Resources\PaymentBatches\Schemas\PaymentBatchForm;
use App\Filament\Resources\PaymentBatches\Tables\PaymentBatchesTable;
use App\Models\PaymentBatch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PaymentBatchResource extends Resource
{
    protected static ?string $model = PaymentBatch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Lotes de Pagamento';

    protected static ?string $modelLabel = 'Lote de Pagamento';

    protected static ?string $pluralModelLabel = 'Lotes de Pagamento';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Pagamentos';
    }

    public static function form(Schema $schema): Schema
    {
        return PaymentBatchForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentBatchesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PaymentBatchItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPaymentBatches::route('/'),
            'create' => CreatePaymentBatch::route('/create'),
            'edit'   => EditPaymentBatch::route('/{record}/edit'),
        ];
    }
}
