<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentResponseResource\Pages;
use App\Models\Appointment;
use App\Models\AppointmentQuestion;
use App\Models\AppointmentResponse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\HtmlString;

class AppointmentResponseResource extends Resource
{
    protected static ?string $model = AppointmentResponse::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-oval-left-ellipsis';
    protected static ?string $navigationGroup = 'Auditoría de Chat';

    protected static ?string $modelLabel = 'Respuesta de Paciente';
    protected static ?string $pluralModelLabel = 'Bitácora de Respuestas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\Section::make('Origen de la Respuesta')
                            ->description('Cita y paciente asociado.')
                            ->icon('heroicon-o-link')
                            ->schema([Forms\Components\Select::make('appointment_id')->label('Paciente / Cita')->relationship('appointment', 'patient_name')->getOptionLabelFromRecordUsing(fn($record) => "{$record->patient_name} ({$record->appointment_date})")->searchable()->preload()->disabled()->prefixIcon('heroicon-m-user'), Forms\Components\Placeholder::make('created_at')->label('Recibido')->disabled()->content(fn(?AppointmentResponse $record) => $record ? $record->created_at->format('d/m/Y H:i A') : '-')]),
                    ]),

                Forms\Components\Group::make()
                    ->columnSpanFull()
                    ->columns(1)
                    ->schema([
                        Forms\Components\Section::make('Intercambio')
                            ->description('Pregunta realizada por el Bot y respuesta del paciente.')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                Forms\Components\Select::make('question_id')
                                    ->label('Pregunta Realizada')
                                    ->options(AppointmentQuestion::all()->pluck('question_text', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->disabled(),

                                Forms\Components\Textarea::make('user_response')
                                    ->label('Texto de la Respuesta')
                                    ->required()
                                    ->extraInputAttributes(['class' => 'font-medium'])
                                    ->disabled()
                                    ->autoSize(),

                                Forms\Components\Placeholder::make('chat_view')->hiddenLabel()->content(
                                    fn(Forms\Get $get) => new HtmlString(
                                        '
                                        <div style="padding: 1rem; background-color: #f9fafb; border-radius: 0.5rem; border: 1px solid #e5e7eb; display: flex; flex-direction: column; gap: 1rem;">

                                            <div style="display: flex; flex-direction: column; align-items: flex-start;">
                                                <span style="font-size: 0.75rem; color: #6b7280; margin-bottom: 0.25rem; margin-left: 0.25rem;">Bot (Pregunta)</span>
                                                <div style="background-color: #ffffff; border: 1px solid #e5e7eb; color: #1f2937; padding: 0.5rem 1rem; border-top-right-radius: 0.75rem; border-bottom-right-radius: 0.75rem; border-bottom-left-radius: 0.75rem; font-size: 0.875rem; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); max-width: 90%;">
                                                    ' .
                                            (AppointmentQuestion::find($get('question_id'))?->question_text ?? 'Seleccione una pregunta...') .
                                            '
                                                </div>
                                            </div>

                                            <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                                <span style="font-size: 0.75rem; color: #6b7280; margin-bottom: 0.25rem; margin-right: 0.25rem;">Paciente (Respuesta)</span>
                                                <div style="background-color: #dcfce7; color: #14532d; border: 1px solid #bbf7d0; padding: 0.5rem 1rem; border-top-left-radius: 0.75rem; border-bottom-left-radius: 0.75rem; border-bottom-right-radius: 0.75rem; font-size: 0.875rem; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); max-width: 90%; font-weight: 500;">
                                                    ' .
                                            ($get('user_response') ?: 'Esperando respuesta...') .
                                            '
                                                </div>
                                            </div>

                                        </div>
                                    ',
                                    ),
                                ),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([Tables\Columns\TextColumn::make('appointment.patient_name')->label('Paciente')->searchable()->icon('heroicon-m-user')->sortable(), Tables\Columns\TextColumn::make('question.question_text')->label('Pregunta')->limit(50)->tooltip(fn($record) => $record->question?->question_text), Tables\Columns\TextColumn::make('user_response')->label('Respuesta')->limit(20)->tooltip(fn($record) => $record->user_response)->searchable(), Tables\Columns\TextColumn::make('created_at')->label('Hora')->dateTime('d/m H:i')->sortable()->color('gray')->size('xs')->toggleable()])
            ->filters([Tables\Filters\SelectFilter::make('appointment_id')->label('Filtrar por Paciente')->relationship('appointment', 'patient_name')->searchable()->preload()])
            ->actions([Tables\Actions\ViewAction::make()->iconButton()])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppointmentResponses::route('/'),
        ];
    }
}
