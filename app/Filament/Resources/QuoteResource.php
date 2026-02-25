<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuoteResource\Pages;
use App\Filament\Resources\QuoteResource\RelationManagers\PaymentsRelationManager;
use App\Models\Patient;
use App\Models\Product;
use App\Models\Quote;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\FontFamily;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Support\HtmlString;

class QuoteResource extends Resource
{
    protected static ?string $model = Quote::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Gestión Clínica';
    protected static ?string $modelLabel = 'Cotización';
    protected static ?string $pluralModelLabel = 'Cotizaciones';

    public static function form(Form $form): Form
    {
        return $form->schema([

            /*
            |--------------------------------------------------------------------------
            | Datos Generales
            |--------------------------------------------------------------------------
            */

            Section::make()
                ->schema([
                    Grid::make(3)->schema([

                        Select::make('patient_id')
                            ->label('Paciente')
                            ->relationship('patient', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) =>
                                $record->first_name . ' ' . $record->last_name
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('quote_number')
                            ->label('Folio')
                            ->readOnly()
                            ->prefix('#'),

                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                'pending' => 'Pendiente',
                                'paid' => 'Pagado',
                                'cancelled' => 'Cancelado',
                                'approved' => 'Aprobado',
                                'rejected' => 'Rechazada',
                            ])
                            ->required()
                            ->native(false)
                            ->default('pending'),

                        DatePicker::make('valid_until')
                            ->label('Válida hasta')
                            ->native(false)
                            ->default(now()->addDays(15)),
                    ]),
                ]),

            /*
            |--------------------------------------------------------------------------
            | Productos (Many-to-Many con Pivot)
            |--------------------------------------------------------------------------
            */

            Section::make('Desglose de Procedimientos')
                ->description('Agregue los servicios médicos o quirúrgicos.')
                ->schema([
                    // ... dentro de tu Section 'Desglose de Procedimientos' ...
                    Repeater::make('quote_products_data')
                        ->label('Productos')
                        ->live() 
                        ->schema([
                            Select::make('product_id')
                                ->label('Producto')
                                ->options(Product::pluck('name', 'id'))
                                ->searchable()
                                ->required()
                                // 1. live() sin onBlur aquí para que el cambio de precio sea instantáneo al seleccionar
                                ->live() 
                                // 2. Aquí interceptamos el valor seleccionado para buscar el precio
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    if (!blank($state)) {
                                        $product = Product::find($state);
                                        if ($product) {
                                            // Llenamos el campo 'price' de esta misma fila
                                            $set('price', $product->price); 
                                        }
                                    }
                                }),

                            TextInput::make('quantity')
                                ->numeric()
                                ->required()
                                ->default(1)
                                // 3. Cambiamos debounce por onBlur
                                ->live(onBlur: true), 

                            TextInput::make('price')
                                ->numeric()
                                ->required()
                                // 4. Cambiamos debounce por onBlur. Si el médico edita el precio, se recalcula al salir del campo.
                                ->live(onBlur: true), 
                        ])
                        ->columns(3),

                    // ... tu Section con el Placeholder se queda igual ...
                ]),

            /*
            |--------------------------------------------------------------------------
            | Total Dinámico
            |--------------------------------------------------------------------------
            */

            Section::make()
                ->schema([
                    Placeholder::make('total_placeholder')
                    ->hiddenLabel()
                    ->content(function (Forms\Get $get) {
                        $total = 0;
                        // 3. Actualizamos el nombre aquí también para que calcule bien
                        $products = $get('quote_products_data') ?? []; 

                        foreach ($products as $item) {
                            $q = (float) ($item['quantity'] ?? 0);
                            $p = (float) ($item['price'] ?? 0);
                            $total += $q * $p;
                        }

                            return new HtmlString(
                                '<div class="flex items-center justify-between">
                                    <span class="text-lg text-gray-500">Total Estimado:</span>
                                    <span class="text-primary-600 text-3xl font-bold">$'
                                    . number_format($total, 2) .
                                '</span></div>'
                            );
                        }),
                ]),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    */

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('quote_number')
                    ->label('Folio')
                    ->fontFamily(FontFamily::Mono)
                    ->searchable(),

                Tables\Columns\TextColumn::make('patient.full_name')
                    ->label('Paciente')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->label('Estado')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'cancelled',
                        'danger' => 'rejected',
                        'info' => 'approved',
                    ]),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('usd')
                    ->weight(FontWeight::Bold)
                    ->color('success')
                    ->alignEnd(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\Action::make('downloadPDF')->label('PDF')->icon('heroicon-o-arrow-down-tray')->url(fn(Quote $record) => route('quote.generate.pdf', $record))->openUrlInNewTab(),
                Tables\Actions\Action::make('viewOnline')->label('Online')->icon('heroicon-o-globe-alt')->url(fn(Quote $record) => route('quote.generate.html', $record))->openUrlInNewTab(),
                Tables\Actions\Action::make('addPayment')
                    ->label('Abonar')
                    ->icon('heroicon-s-credit-card')
                    ->color('success')
                    ->button()
                    ->hidden(fn(Quote $record) => $record->status === 'paid')
                    ->form([
                        Forms\Components\Placeholder::make('balance')
                            ->label('Saldo Pendiente')
                            ->content(fn(Quote $record) => '$' . number_format($record->remaining_balance, 2)),
                        Forms\Components\TextInput::make('amount')
                            ->label('Monto a Pagar')
                            ->numeric()
                            ->required()
                            ->default(fn(Quote $record) => $record->remaining_balance)
                            ->maxValue(fn(Quote $record) => $record->remaining_balance),
                    ])
                    ->action(function (Quote $record, array $data) {
                        $record->payments()->create([
                            'amount' => $data['amount'],
                            'stripe_link' => 'https://checkout.stripe.com/pay/demo_' . \Illuminate\Support\Str::random(12),
                            'status' => 'completed',
                        ]);

                        // Fill up logic: if full amount is reached, update status
                        if ($record->payments()->sum('amount') >= $record->total_amount) {
                            $record->update(['status' => 'paid']);
                        }
                    }),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [
            PaymentsRelationManager::class,
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