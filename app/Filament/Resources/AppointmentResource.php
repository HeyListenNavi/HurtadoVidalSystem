<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentResource\Pages;
use App\Models\Appointment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Gestión Clínica';

    protected static ?string $modelLabel = 'Cita';
    protected static ?string $pluralModelLabel = 'Agenda de Citas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Ficha de la Cita')
                            ->description('Detalles de programación y contacto del paciente.')
                            ->icon('heroicon-o-calendar')
                            ->schema([
                                Forms\Components\TextInput::make('patient_name')
                                    ->label('Nombre del Paciente')
                                    ->prefixIcon('heroicon-m-user')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('chat_id')
                                    ->label('Teléfono / WhatsApp')
                                    ->helperText('Haga clic en el icono para abrir el chat.')
                                    ->prefixIcon('heroicon-m-device-phone-mobile')
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('openWhatsapp')
                                            ->icon('heroicon-m-chat-bubble-left-right')
                                            ->color('success')
                                            ->url(fn ($state) => "https://wa.me/{$state}", true)
                                            ->tooltip('Abrir en WhatsApp')
                                    )
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Forms\Components\Split::make([
                                    Forms\Components\DatePicker::make('appointment_date')
                                        ->label('Fecha Agendada')
                                        ->native(false)
                                        ->prefixIcon('heroicon-m-calendar-days')
                                        ->displayFormat('d/m/Y')
                                        ->required(),

                                    Forms\Components\TimePicker::make('appointment_time')
                                        ->label('Hora')
                                        ->native(false)
                                        ->prefixIcon('heroicon-m-clock')
                                        ->seconds(false)
                                        ->required(),
                                ]),

                                Forms\Components\Textarea::make('reason_for_visit')
                                    ->label('Motivo de Consulta')
                                    ->placeholder('Describa el procedimiento o control solicitado...')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),
                    ])->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Gestión de Estatus')
                            ->icon('heroicon-o-adjustments-horizontal')
                            ->schema([
                                Forms\Components\Select::make('process_status')
                                    ->label('Estado Actual')
                                    ->options([
                                        'in_progress' => 'En Proceso',
                                        'completed' => 'Completada',
                                        'rejected' => 'Rechazada',
                                        'cancelled' => 'Cancelada',
                                    ])
                                    ->required()
                                    ->native(false)
                                    ->selectablePlaceholder(false)
                                    ->live()
                                    ->afterStateUpdated(fn (Forms\Set $set) => $set('rejection_reason', null)),

                                Forms\Components\Textarea::make('rejection_reason')
                                    ->label('Motivo de Rechazo')
                                    ->placeholder('Indique por qué no procede la cita...')
                                    ->required(fn (Get $get) => $get('process_status') === 'rejected')
                                    ->visible(fn (Get $get) => $get('process_status') === 'rejected')
                                    ->rows(2),
                            ]),

                        Forms\Components\Section::make('Rastreo (Bot)')
                            ->icon('heroicon-o-cpu-chip')
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                Forms\Components\Select::make('current_question_id')
                                    ->relationship('currentQuestion', 'question_text')
                                    ->label('Última Pregunta')
                                    ->disabled(),
                            ]),
                    ])->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll("5s")
            ->defaultSort('appointment_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('appointment_date')
                    ->label('Fecha')
                    ->date('d M, Y')
                    ->sortable()
                    ->icon('heroicon-m-calendar'),

                Tables\Columns\TextColumn::make('appointment_time')
                    ->label('Hora')
                    ->time('H:i A')
                    ->sortable()
                    ->icon('heroicon-m-clock'),

                Tables\Columns\TextColumn::make('patient_name')
                    ->label('Paciente')
                    ->searchable()
                    ->description(fn (Appointment $record) => \Illuminate\Support\Str::limit($record->reason_for_visit, 30)),

                Tables\Columns\TextColumn::make('chat_id')
                    ->label('Contacto')
                    ->url(fn (string $state) => "https://wa.me/{$state}")
                    ->openUrlInNewTab()
                    ->searchable(),

                Tables\Columns\TextColumn::make('process_status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in_progress' => 'En Proceso',
                        'completed' => 'Completada',
                        'rejected' => 'Rechazada',
                        'cancelled' => 'Cancelada',
                        default => $state,
                    })
                    ->colors([
                        'info' => 'in_progress',
                        'success' => 'completed',
                        'danger' => 'rejected',
                        'warning' => 'cancelled',
                    ])
                    ->icon(fn (string $state): string => match ($state) {
                        'in_progress' => 'heroicon-m-arrow-path',
                        'completed' => 'heroicon-m-check-badge',
                        'rejected' => 'heroicon-m-x-circle',
                        'cancelled' => 'heroicon-m-no-symbol',
                        default => 'heroicon-m-question-mark-circle',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Solicitado')
                    ->since()
                    ->color('gray')
                    ->size('xs')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('process_status')
                    ->label('Filtrar por Estado')
                    ->options([
                        'in_progress' => 'En Proceso',
                        'completed' => 'Completada',
                        'rejected' => 'Rechazada',
                        'cancelled' => 'Cancelada',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('whatsapp')
                    ->icon('heroicon-m-chat-bubble-left-right')
                    ->color('success')
                    ->url(fn (Appointment $record) => "https://wa.me/{$record->chat_id}")
                    ->openUrlInNewTab()
                    ->label('Chat'),

                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Agendar Nueva Cita')
                    ->icon('heroicon-m-plus'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
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
