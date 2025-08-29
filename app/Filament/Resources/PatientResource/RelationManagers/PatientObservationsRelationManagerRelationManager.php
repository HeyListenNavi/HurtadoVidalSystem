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
                    ->schema([
                        Forms\Components\DatePicker::make('observation_date')
                            ->label('Fecha de la Observación')
                            ->required()
                            ->default(now()),
                        Forms\Components\RichEditor::make('notes')
                            ->label('Notas de la Visita')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('attached_photo')
                            ->label('Foto Adjunta')
                            ->image()
                            ->directory('patient-photos')
                            ->visibility('private') // Asegura que las fotos no sean accesibles públicamente
                            ->columnSpanFull(),
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
                    ->date(),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Notas')
                    ->html()
                    ->limit(50),
                Tables\Columns\ImageColumn::make('attached_photo')
                    ->label('Foto'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
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
