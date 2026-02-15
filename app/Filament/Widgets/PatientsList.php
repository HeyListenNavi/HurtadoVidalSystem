<?php

namespace App\Filament\Widgets;

use App\Models\Patient;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\HtmlString;

class PatientsList extends BaseWidget
{
    protected static ?string $heading = 'Pacientes Recientes';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Patient::query()->latest()->limit(10))
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Paciente')
                    ->getStateUsing(fn($record) => "{$record->first_name} {$record->last_name}")
                    ->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall)
                    ->searchable(['first_name', 'last_name'])
                    ->icon('heroicon-m-user-circle')
                    ->iconColor('gray'),

                Tables\Columns\TextColumn::make('birth_date')->label('Edad')->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->age . ' años')->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall)->color('gray'),

                Tables\Columns\TextColumn::make('email')->label('Correo Electrónico')->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall)->color('gray')->icon('heroicon-m-at-symbol')->searchable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('WhatsApp')
                    ->icon('heroicon-m-chat-bubble-left-right')
                    ->color('success') // Emerald
                    ->weight(FontWeight::SemiBold)
                    ->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall)
                    ->url(fn($state) => "https://wa.me/{$state}", true)
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')->label('Registrado')->since()->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall)->color('gray')->alignEnd(),
            ])
            ->paginated([5])
            ->striped();
    }
}
