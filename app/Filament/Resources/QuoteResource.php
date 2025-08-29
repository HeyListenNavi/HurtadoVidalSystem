<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuoteResource\Pages;
use App\Models\Quote;
use App\Models\Product;
use App\Models\Patient;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Filament\Tables\Actions\Action;

class QuoteResource extends Resource
{
    protected static ?string $model = Quote::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('patient_id')
                    ->label('Paciente')
                    ->options(Patient::all()->pluck('full_name', 'id'))
                    ->searchable()
                    ->required()
                    ->columnSpan('full'),

                Section::make('Productos y Servicios')
                    ->schema([
                        Repeater::make('products') // El nombre 'products' debe coincidir con la relación
                            // ->relationship() // <--- ELIMINA ESTA LÍNEA
                            ->label('Lista de Productos')
                            ->schema([
                                Select::make('product_id')
                                    // ... el resto de tu schema del repeater está perfecto
                                    ->label('Producto/Servicio')
                                    ->options(Product::all()->pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set) {
                                        $product = Product::find($state);
                                        if ($product) {
                                            $set('price', $product->price);
                                        }
                                    }),
                                TextInput::make('quantity')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->required()
                                    ->live()
                                    ->default(1),
                                TextInput::make('price')
                                    ->label('Precio Unitario')
                                    ->prefix('$')
                                    ->numeric()
                                    ->required()
                                    ->live(),
                                Placeholder::make('subtotal')
                                    ->label('Subtotal')
                                    ->content(function ($get) {
                                        $quantity = $get('quantity') ?? 0;
                                        $price = $get('price') ?? 0;
                                        return '$' . number_format($quantity * $price, 2);
                                    }),
                            ])
                            ->columns(4)
                            ->columnSpan('full')
                            ->addActionLabel('Agregar Producto')
                            ->reorderable()
                            ->orderColumn('order') // Esto requiere la lógica manual para guardar el orden
                            ->itemLabel(fn (array $state): ?string => $state['product_id'] ? Product::find($state['product_id'])?->name : null),
                    ])
                    ->collapsible(),

                Placeholder::make('total_placeholder')
                    ->label('Total de la Cotización')
                    ->content(function ($get) {
                        $total = 0;
                        foreach ($get('products') as $item) {
                            $total += ($item['quantity'] ?? 0) * ($item['price'] ?? 0);
                        }
                        return new HtmlString('<h2 style="font-weight: bold;">Total: $' . number_format($total, 2) . '</h2>');
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('quote_number')
                    ->label('No. de Cotización')
                    ->searchable(),
                Tables\Columns\TextColumn::make('patient.first_name')
                    ->label('Paciente')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Monto Total')
                    ->money('usd', true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make("downloadPDF")
                    ->url(function( Quote $quote ){
                        return route("quote.generate.pdf", ["quote" => $quote]);
                    })
                    ->openUrlInNewTab()
                    ->label("PDF"),
                Action::make("generateHTML")
                    ->url(function( Quote $quote ){
                        return route("quote.generate.html", ["quote" => $quote]);
                    })
                    ->openUrlInNewTab()
                    ->label("Online"),
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
            'index' => Pages\ListQuotes::route('/'),
            'create' => Pages\CreateQuote::route('/create'),
            'edit' => Pages\EditQuote::route('/{record}/edit'),
        ];
    }
}
