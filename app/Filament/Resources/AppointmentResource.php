<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentResource\Pages;
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

    protected static ?string $navigationGroup = 'Citas';

    protected static ?string $modelLabel = 'Cita';

    protected static ?string $pluralModelLabel = 'Citas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Cita')
                    ->schema([
                        Forms\Components\TextInput::make('chat_id')
                            ->label('Chat ID')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('appointment_date')
                            ->label('Fecha de la Cita')
                            ->required(),
                        Forms\Components\TimePicker::make('appointment_time')
                            ->label('Hora de la Cita')
                            ->required(),
                        Forms\Components\Textarea::make('reason_for_visit')
                            ->label('Motivo de la Visita')
                            ->placeholder('Ej: Control general, revisión de síntomas...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detalles del Paciente')
                    ->schema([
                        Forms\Components\TextInput::make('patient_name')
                            ->label('Nombre del Paciente')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('current_question_id')
                            ->relationship('currentQuestion', 'question_text')
                            ->label('Pregunta Actual')
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Estado del Proceso')
                    ->schema([
                        Forms\Components\Select::make('process_status')
                            ->label('Estado del Proceso')
                            ->options([
                                'in_progress' => 'En Proceso',
                                'completed' => 'Completado',
                                'rejected' => 'Rechazado',
                                'cancelled' => 'Cancelado',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Razón de Rechazo')
                            ->placeholder('Ej: No cumple con los requisitos')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
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
                    ->badge()
                    ->label('Estado')
                    ->colors([
                        'info' => 'in_progress',
                        'success' => 'completed',
                        'danger' => 'rejected',
                        'warning' => 'cancelled',
                        'gray' => 'default',
                    ])
                    ->tooltip(fn($record) => $record->rejection_reason ? "Razón: " . $record->rejection_reason : null)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creada')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListAppointments::route('/'), // ¡CAMBIADO!
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
