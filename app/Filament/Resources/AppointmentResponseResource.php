<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentResponseResource\Pages;
use App\Filament\Resources\AppointmentResponseResource\RelationManagers;
use App\Models\Appointment;
use App\Models\AppointmentQuestion;
use App\Models\AppointmentResponse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppointmentResponseResource extends Resource
{
    protected static ?string $model = AppointmentResponse::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationGroup = 'Citas';

    protected static ?string $modelLabel = 'Respuesta';

    protected static ?string $pluralModelLabel = 'Respuestas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Cita')
                    ->schema([
                        Forms\Components\Select::make('appointment_id')
                            ->options(Appointment::all()->pluck('patient_name', 'id'))
                            ->label('Cita')
                            ->required()
                            ->searchable(),
                    ]),

                Forms\Components\Section::make('Pregunta y Respuesta')
                    ->schema([
                        Forms\Components\Select::make('question_id')
                            ->options(AppointmentQuestion::all()->pluck('question_text', 'id'))
                            ->label('Pregunta')
                            ->required()
                            ->searchable(),

                        Forms\Components\Textarea::make('user_response')
                            ->label('Respuesta del Usuario')
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Decisión AI')
                    ->schema([
                        Forms\Components\Textarea::make('ai_decision')
                            ->label('Decisión del AI')
                            ->rows(6)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('appointment.patient_name')
                    ->label('Paciente')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('question.question_text')
                    ->label('Pregunta')
                    ->limit(50)
                    ->tooltip(fn($record) => $record->question?->question_text),

                Tables\Columns\TextColumn::make('user_response')
                    ->label('Respuesta')
                    ->limit(20)
                    ->tooltip(fn($record) => $record->user_response)
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creada')
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
            'index' => Pages\ListAppointmentResponses::route('/'),
            'create' => Pages\CreateAppointmentResponse::route('/create'),
            'edit' => Pages\EditAppointmentResponse::route('/{record}/edit'),
        ];
    }
}
