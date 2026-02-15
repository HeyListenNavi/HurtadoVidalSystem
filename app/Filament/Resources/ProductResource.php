<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    // CAMBIO 1: Ícono de navegación más clínico (Lista de procedimientos/Portapapeles)
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Administración';
    protected static ?string $activeNavigationIcon = 'heroicon-s-clipboard-document-list';

    protected static ?string $modelLabel = 'Procedimiento';
    protected static ?string $pluralModelLabel = 'Catálogo de Servicios';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Ficha Técnica')
                            ->description('Detalles del procedimiento quirúrgico o servicio.')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre del Procedimiento')
                                    ->required()
                                    ->prefixIcon('heroicon-m-pencil-square')
                                    ->placeholder('Ej: Rinoplastia Ultrasónica, Lipoescultura...')
                                    ->maxLength(255),

                                Forms\Components\RichEditor::make('description')
                                    ->label('Alcance / Incluye')
                                    ->toolbarButtons(['bold', 'italic', 'bulletList', 'redo', 'undo'])
                                    ->placeholder('Describa los detalles clínicos, tiempos de quirófano, etc...')
                                    ->columnSpanFull(),
                            ]),
                    ])->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Costos')
                            ->description('Valoración económica.')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                Forms\Components\TextInput::make('price')
                                    ->label('Honorarios / Precio')
                                    ->prefix('$')
                                    ->suffix('MXN')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->extraInputAttributes(['class' => 'text-lg font-bold']),
                            ]),

                        Forms\Components\Section::make('Registro')
                            ->schema([
                                Forms\Components\Placeholder::make('created_at')
                                    ->label('Alta en sistema')
                                    ->content(fn (?Product $record): string => $record ? $record->created_at->format('d/m/Y') : '-'),

                                Forms\Components\Placeholder::make('updated_at')
                                    ->label('Última modificación')
                                    ->content(fn (?Product $record): string => $record ? $record->updated_at->diffForHumans() : '-'),
                            ])->hidden(fn (?Product $record) => $record === null),
                    ])->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Procedimiento')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->description(fn(Product $record) => \Illuminate\Support\Str::limit(strip_tags($record->description), 110))
                    ->wrap(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Costo Base')
                    ->money('usd')
                    ->weight(FontWeight::Bold)
                    ->color('primary')
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->since()
                    ->color('gray')
                    ->size('xs')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
