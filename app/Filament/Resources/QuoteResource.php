<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuoteResource\Pages;
use App\Filament\Resources\QuoteResource\RelationManagers\PaymentsRelationManager;
use App\Models\Patient;
use App\Models\Product;
use App\Models\Quote;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Filament\Support\Enums\FontFamily;

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
            Forms\Components\Section::make()->schema([
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\Select::make('patient_id')
                        ->label('Paciente')
                        ->options(Patient::all()->pluck('full_name', 'id'))
                        ->searchable()
                        ->preload()
                        ->prefixIcon('heroicon-m-user')
                        ->placeholder('Buscar paciente...')
                        ->required()
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('quote_number')
                        ->label('Folio')
                        ->default('COT-' . strtoupper(uniqid()))
                        ->readOnly()
                        ->prefix('#'),

                    Forms\Components\Select::make('status')
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
                        ->selectablePlaceholder(false)
                        ->default('pending'),

                    Forms\Components\DatePicker::make('valid_until')
                        ->label('Válida hasta')
                        ->native(false)
                        ->default(now()->addDays(15)),
                ]),
            ]),

            Forms\Components\Section::make('Desglose de Procedimientos')
                ->description('Agregue los servicios médicos o quirúrgicos a cotizar.')
                ->schema([
                    Forms\Components\Repeater::make('quote_products')
                        ->hiddenLabel()
                        ->schema([
                            Forms\Components\Grid::make(12)->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Procedimiento')
                                    ->options(Product::all()->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpan(6)
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        $product = Product::find($state);
                                        if ($product) {
                                            $set('price', $product->price);
                                            $set('quantity', 1);
                                        }
                                    }),

                                Forms\Components\TextInput::make('quantity')->label('Cant.')->numeric()->default(1)->required()->columnSpan(2)->live(),

                                Forms\Components\TextInput::make('price')->label('Precio')->prefix('$')->numeric()->required()->columnSpan(2)->live(),

                                Forms\Components\Placeholder::make('subtotal_display')
                                    ->label('Subtotal')
                                    ->content(function (Forms\Get $get) {
                                        $q = (float) $get('quantity');
                                        $p = (float) $get('price');

                                        return '$' . number_format($q * $p, 2);
                                    })
                                    ->columnSpan(2)
                                    ->extraAttributes(['class' => 'text-right font-bold text-gray-600 self-center']),
                            ]),
                        ])
                        ->defaultItems(1)
                        ->addActionLabel('Agregar Procedimiento')
                        ->reorderableWithButtons()
                        ->collapsible()
                        ->cloneable(),
                ]),

            Forms\Components\Group::make()
                ->columnSpanFull()
                ->columns(1)
                ->schema([
                    Forms\Components\Textarea::make('notes')->label('Términos y Condiciones')->placeholder('Ej: Incluye honorarios, quirófano y primera consulta post-operatoria.')->rows(3)->autoSize(),

                    Forms\Components\Section::make()->schema([
                        Forms\Components\Placeholder::make('total_placeholder')
                            ->hiddenLabel()
                            ->content(function (Forms\Get $get) {
                                $total = 0;
                                $products = $get('quote_items') ?? [];

                                foreach ($products as $item) {
                                    $q = (float) ($item['quantity'] ?? 0);
                                    $p = (float) ($item['price'] ?? 0);
                                    $total += $q * $p;
                                }

                                return new HtmlString(
                                    '
                                            <div class="flex items-center justify-between">
                                                <span class="text-lg text-gray-500">Total Estimado:</span>
                                                <span class="text-primary-600 text-3xl font-bold">$' .
                                        number_format($total, 2) .
                                        '</span>
                                            </div>
                                        ',
                                );
                            }),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('quote_number')->label('Folio')->fontFamily(FontFamily::Mono)->color('gray')->searchable(),

                Tables\Columns\TextColumn::make('patient.full_name')->label('Paciente')->searchable()->sortable(),

                Tables\Columns\TextColumn::make('created_at')->label('Fecha')->date('d M Y')->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->label('Estado')
                    ->formatStateUsing(
                        fn(string $state): string => match ($state) {
                            'pending' => 'Pendiente',
                            'paid' => 'Pagado',
                            'cancelled' => 'Cancelado',
                            'approved' => 'Aprobado',
                            'rejected' => 'Rechazada',
                            default => $state,
                        },
                    )
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'cancelled',
                        'danger' => 'rejected',
                        'info' => 'approved',
                    ])
                    ->icon(
                        fn(string $state): string => match ($state) {
                            'paid' => 'heroicon-m-check-badge',
                            'cancelled' => 'heroicon-m-x-circle',
                            'rejected' => 'heroicon-m-x-circle',
                            'pending' => 'heroicon-m-clock',
                            'approved' => 'heroicon-m-document-check',
                            default => 'heroicon-m-document',
                        },
                    ),

                Tables\Columns\TextColumn::make('total_amount')->label('Total')->money('usd')->weight(FontWeight::Bold)->color('success')->alignEnd(),
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
