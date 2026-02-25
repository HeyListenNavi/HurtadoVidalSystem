<?php

namespace App\Filament\Resources\QuoteResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Support\Str;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Historial de Pagos';

    protected static ?string $modelLabel = 'Pago';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(2)->schema([
                // Placeholder para mostrar cuánto falta antes de escribir el monto
                Forms\Components\Placeholder::make('remaining_balance')
                    ->label('Saldo Pendiente')
                    ->content(fn() => '$' . number_format($this->getOwnerRecord()->total_amount - $this->getOwnerRecord()->payments()->sum('amount'), 2))
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'text-primary-600 font-bold']),

                Forms\Components\TextInput::make('amount')
                    ->label('Monto del Abono')
                    ->numeric()
                    ->prefix('$')
                    ->required()
                    // Falla evitada: Validación para no pagar de más
                    ->maxValue(fn() => $this->getOwnerRecord()->total_amount - $this->getOwnerRecord()->payments()->sum('amount'))
                    ->helperText('No puede exceder el saldo pendiente.'),

                Forms\Components\TextInput::make('stripe_link')
                    ->label('Enlace de Pago')
                    ->default(fn() => 'https://checkout.stripe.com/pay/demo_' . Str::random(12))
                    ->readOnly()
                    ->prefixIcon('heroicon-m-link'),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('amount')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha y Hora')
                    ->dateTime('d M Y, h:i A')
                    ->description(fn($record) => $record->created_at->diffForHumans())
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->money('usd')
                    ->weight(FontWeight::Bold)
                    ->color('success')
                    ->alignEnd()
                    // Añadimos un sumatorio al final de la tabla
                    ->summarize(Sum::make()->label('Total Pagado')->money('usd')),

                Tables\Columns\TextColumn::make('stripe_link')
                    ->label('Stripe Link')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->color('gray')
                    ->limit(15)
                    ->copyable()
                    ->tooltip('Click para copiar enlace'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Registrar Abono')
                    ->icon('heroicon-m-plus')
                    ->modalHeading('Nuevo Abono a Cotización')
                    ->after(fn() => $this->updateQuoteStatus()),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->modalHeading('¿Eliminar registro de pago?')
                    ->after(fn() => $this->updateQuoteStatus()),
            ])
            ->emptyStateHeading('No hay pagos registrados')
            ->emptyStateDescription('Comience registrando el primer abono del paciente.')
            ->emptyStateIcon('heroicon-o-credit-card');
    }

    /**
     * Centralizamos la lógica de estado para evitar duplicar código y errores de lógica.
     */
    protected function updateQuoteStatus(): void
    {
        $quote = $this->getOwnerRecord();
        $totalPagado = $quote->payments()->sum('amount');

        // Falla de diseño corregida: usamos una comparación más robusta
        if ($totalPagado >= $quote->total_amount) {
            $quote->update(['status' => 'paid']);
        } elseif ($totalPagado > 0) {
            // Asumimos que tienes un estado de "Aprobado" o podrías crear "Pago Parcial"
            $quote->update(['status' => 'approved']);
        } else {
            $quote->update(['status' => 'pending']);
        }
    }
}
