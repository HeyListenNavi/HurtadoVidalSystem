<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentResource\Pages;
use App\Filament\Resources\AppointmentResource\RelationManagers;
use App\Models\Appointment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Appointments';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('chat_id')
                    ->label('Chat ID')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('patient_name')
                    ->label('Nombre del Paciente')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('appointment_date')
                    ->label('Fecha de la Cita'),
                Forms\Components\TimePicker::make('appointment_time')
                    ->label('Hora de la Cita'),
                Forms\Components\Textarea::make('reason_for_visit')
                    ->label('Motivo de la Visita')
                    ->columnSpanFull(),
                Forms\Components\Select::make('current_question_id')
                    ->relationship('currentQuestion', 'question_text')
                    ->label('Pregunta Actual'),
                Forms\Components\TextInput::make('process_status')
                    ->label('Estado del Proceso')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('rejection_reason')
                    ->label('Razón de Rechazo')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('chat_id')
                    ->label('Chat ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('patient_name')
                    ->label('Nombre del Paciente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('appointment_date')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('appointment_time')
                    ->label('Hora'),
                Tables\Columns\TextColumn::make('process_status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in_progress' => 'info',
                        'completed' => 'success',
                        'rejected' => 'danger',
                        'cancelled' => 'warning',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_confirmed')
                    ->label('Confirmada')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
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
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
