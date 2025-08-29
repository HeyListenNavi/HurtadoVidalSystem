<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentResponseResource\Pages;
use App\Filament\Resources\AppointmentResponseResource\RelationManagers;
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

    protected static ?string $navigationGroup = 'Appointments';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('appointment_id')
                    ->relationship('appointment', 'patient_name')
                    ->label('Cita')
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('question_id')
                    ->relationship('question', 'question_text')
                    ->label('Pregunta')
                    ->required()
                    ->searchable(),
                Forms\Components\Textarea::make('question_text_snapshot')
                    ->label('Pregunta (Snapshot)')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('user_response')
                    ->label('Respuesta del Usuario')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('ai_decision')
                    ->label('Decisión del AI (JSON)')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('appointment.patient_name')
                    ->label('Paciente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('question.question_text')
                    ->label('Pregunta'),
                Tables\Columns\TextColumn::make('user_response')
                    ->label('Respuesta')
                    ->searchable(),
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
            'index' => Pages\ListAppointmentResponses::route('/'),
            'create' => Pages\CreateAppointmentResponse::route('/create'),
            'edit' => Pages\EditAppointmentResponse::route('/{record}/edit'),
        ];
    }
}
