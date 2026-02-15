<?php

namespace App\Filament\Resources\PatientResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Actions\Action;

class PatientObservationsRelationManager extends RelationManager
{
    protected static string $relationship = 'observations';

    protected static ?string $recordTitleAttribute = 'notes';

    protected static ?string $modelLabel = 'Observación';
    protected static ?string $pluralModelLabel = 'Observaciones';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detalles de la Observación')
                    ->description('Observaciones privadas del paciente. Solo personal autorizado puede verlas.')
                    ->schema([
                        Forms\Components\DatePicker::make('observation_date')
                            ->label('Fecha de la Observación')
                            ->required()
                            ->default(now())
                            ->maxDate(now()),

                        Forms\Components\RichEditor::make('notes')
                            ->label('Notas de la Visita')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('attached_photo')
                            ->label('Foto Adjunta')
                            ->image()
                            ->directory('patient-photos')
                            ->visibility('private')
                            ->columnSpanFull()
                            ->imagePreviewHeight(150),
                    ])->columns(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->recordTitleAttribute('notes')
            ->columns([
                Tables\Columns\TextColumn::make('observation_date')
                    ->label('Fecha')
                    ->since(),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Notas')
                    ->html()
                    ->limit(50)
                    ->tooltip(fn($record) => $record->notes),

                Tables\Columns\ImageColumn::make('attached_photo')
                    ->label('Foto')
                    ->rounded()
                    ->size(50),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
