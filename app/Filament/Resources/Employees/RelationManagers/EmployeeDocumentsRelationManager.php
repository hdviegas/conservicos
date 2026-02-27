<?php

namespace App\Filament\Resources\Employees\RelationManagers;

use App\Enums\DocumentType;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class EmployeeDocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'employeeDocuments';

    protected static ?string $title = 'Documentos';

    public function form(Schema $schema): Schema
    {
        $employeeId = $this->getOwnerRecord()->id;

        return $schema->components([
            Select::make('type')
                ->label('Tipo de Documento')
                ->options(collect(DocumentType::cases())->mapWithKeys(
                    fn (DocumentType $t) => [$t->value => $t->label()]
                ))
                ->required()
                ->searchable(),
            TextInput::make('name')
                ->label('Descrição / Identificação')
                ->required()
                ->maxLength(255)
                ->helperText('Ex: RG frente, CNH 2024, Contrato assinado'),
            FileUpload::make('path')
                ->label('Arquivo')
                ->required()
                ->disk('local')
                ->directory("employees/{$employeeId}/documents")
                ->preserveFilenames()
                ->downloadable()
                ->openable()
                ->maxSize(20480)
                ->acceptedFileTypes([
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                ])
                ->helperText('PDF, imagens (JPG, PNG) ou Word. Máximo 20 MB.')
                ->columnSpanFull()
                ->afterStateHydrated(function (FileUpload $component, $state) {
                    if (is_string($state)) {
                        $component->state([$state]);
                    }
                })
                ->dehydrateStateUsing(function ($state) {
                    if (is_array($state)) {
                        return array_values($state)[0] ?? null;
                    }

                    return $state;
                }),
            Textarea::make('notes')
                ->label('Observações')
                ->maxLength(1000)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof DocumentType ? $state->label() : $state)
                    ->color(fn ($state) => $state instanceof DocumentType ? $state->color() : 'gray')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Descrição')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('size')
                    ->label('Tamanho')
                    ->formatStateUsing(function ($state) {
                        if (! $state) {
                            return '-';
                        }
                        $units = ['B', 'KB', 'MB', 'GB'];
                        $unit = 0;
                        while ($state >= 1024 && $unit < count($units) - 1) {
                            $state /= 1024;
                            $unit++;
                        }

                        return round($state, 1) . ' ' . $units[$unit];
                    }),
                TextColumn::make('created_at')
                    ->label('Enviado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()->label('Adicionar Documento'),
            ])
            ->recordActions([
                Action::make('download')
                    ->label('Baixar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->action(function ($record) {
                        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                        $disk = Storage::disk('local');

                        if (! $record->path || ! $disk->exists($record->path)) {
                            return;
                        }

                        return $disk->download($record->path, $record->name);
                    }),
                EditAction::make(),
                DeleteAction::make()
                    ->after(function ($record) {
                        if ($record->path) {
                            Storage::disk('local')->delete($record->path);
                        }
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
