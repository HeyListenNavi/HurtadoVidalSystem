<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentQuestionResource\Pages;
use App\Filament\Resources\AppointmentQuestionResource\RelationManagers;
use App\Models\AppointmentQuestion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppointmentQuestionResource extends Resource
{
    protected static ?string $model = AppointmentQuestion::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationGroup = 'Appointments';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('question_text')
                    ->label('Texto de la Pregunta')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('approval_criteria')
                    ->label('Criterios de Aprobación (JSON)')
                    ->helperText('Ej: {"type": "text", "min_length": 5} o {"type": "options", "values": ["si", "no"]}')
                    ->rows(5)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('order')
                    ->label('Orden')
                    ->numeric()
                    ->default(0)
                    ->hiddenOn('edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('order')
            ->defaultSort('order', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('question_text')
                    ->label('Pregunta')
                    ->searchable(),
                Tables\Columns\TextColumn::make('order')
                    ->label('Orden')
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
            'index' => Pages\ListAppointmentQuestions::route('/'),
            'create' => Pages\CreateAppointmentQuestion::route('/create'),
            'edit' => Pages\EditAppointmentQuestion::route('/{record}/edit'),
        ];
    }
}
