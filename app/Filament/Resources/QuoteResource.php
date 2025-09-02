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

    protected static ?string $modelLabel = 'Cotización';

    protected static ?string $pluralModelLabel = 'Cotizaciones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('patient_id')
                    ->label('Paciente')
                    ->options(Patient::all()->pluck('full_name', 'id'))
                    ->searchable()
                    ->placeholder('Selecciona un paciente...')
                    ->required()
                    ->columnSpan('full'),

                Section::make('Productos y Servicios')
                    ->schema([
                        Repeater::make('products')
                            ->label('Lista de Productos')
                            ->schema([
                                Select::make('product_id')
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
                                    })
                                    ->columnSpan(2),

                                TextInput::make('quantity')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->live()
                                    ->columnSpan(1),

                                TextInput::make('price')
                                    ->label('Precio Unitario')
                                    ->prefix('$')
                                    ->numeric()
                                    ->required()
                                    ->live()
                                    ->columnSpan(1),

                                Placeholder::make('subtotal')
                                    ->label('Subtotal')
                                    ->content(function ($get) {
                                        $quantity = $get('quantity') ?? 0;
                                        $price = $get('price') ?? 0;
                                        return '$' . number_format($quantity * $price, 2);
                                    })
                                    ->columnSpan(1),
                            ])
                            ->columns(5)
                            ->columnSpan('full')
                            ->addActionLabel('Agregar Producto')
                            ->reorderable()
                            ->itemLabel(
                                fn(array $state): ?string =>
                                $state['product_id'] ? Product::find($state['product_id'])?->name : null
                            ),
                    ])
                    ->collapsible(),

                Section::make('Resumen')
                    ->schema([
                        Placeholder::make('total_placeholder')
                            ->label('Total de la Cotización')
                            ->content(function ($get) {
                                $total = 0;
                                foreach ($get('products') as $item) {
                                    $total += ($item['quantity'] ?? 0) * ($item['price'] ?? 0);
                                }
                                return new HtmlString(
                                    '<span style="font-size:1.2rem; font-weight:bold; color:#16a34a;">$' .
                                        number_format($total, 2) .
                                        '</span>'
                                );
                            }),
                    ])
                    ->collapsed(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('quote_number')
                    ->label('No. de Cotización')
                    ->copyable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('patient.full_name')
                    ->label('Paciente')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Monto Total')
                    ->money('usd', true)
                    ->color(fn($state) => $state > 0 ? 'success' : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'pendiente' => 'warning',
                        'pagado' => 'success',
                        'cancelado' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Action::make("downloadPDF")
                        ->url(
                            fn(Quote $quote) =>
                            route("quote.generate.pdf", ["quote" => $quote])
                        )
                        ->openUrlInNewTab()
                        ->label("PDF"),

                    Action::make("generateHTML")
                        ->url(
                            fn(Quote $quote) =>
                            route("quote.generate.html", ["quote" => $quote])
                        )
                        ->openUrlInNewTab()
                        ->label("Online"),
                ])->label('Más'),
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
