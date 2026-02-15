<?php

namespace App\Filament\Resources\PatientResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PatientObservationsRelationManager extends RelationManager
{
    protected static string $relationship = 'observations';

    protected static ?string $title = 'Evolución y Seguimiento';
    protected static ?string $modelLabel = 'Nota de Evolución';
    protected static ?string $pluralModelLabel = 'Bitácora de Evolución';
    protected static ?string $icon = 'heroicon-o-camera';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Documentación Clínica')
                            ->description('Registro de cambios y estado actual')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Forms\Components\DatePicker::make('observation_date')
                                    ->label('Fecha de Revisión')
                                    ->required()
                                    ->default(now())
                                    ->maxDate(now())
                                    ->prefixIcon('heroicon-m-calendar')
                                    ->native(false),

                                Forms\Components\RichEditor::make('notes')
                                    ->label('Notas de Evolución')
                                    ->toolbarButtons([
                                        'bold', 'italic', 'bulletList', 'orderedList', 'h3', 'undo', 'redo'
                                    ])
                                    ->placeholder('Describir estado de cicatrización, edema, dolor...')
                                    ->required()
                                    ->columnSpanFull(),
                            ]),
                    ])->columnSpan(2),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Evidencia Visual')
                            ->description('Fotografía de control')
                            ->icon('heroicon-o-camera')
                            ->schema([
                                Forms\Components\FileUpload::make('attached_photo')
                                    ->label('Foto del Paciente')
                                    ->hiddenLabel()
                                    ->image()
                                    ->imageEditor()
                                    ->directory('patient-photos')
                                    ->visibility('private')
                                    ->columnSpanFull()
                                    ->openable()
                                    ->downloadable()
                                    ->panelLayout('integrated')
                                    ->imagePreviewHeight('250'),
                            ]),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('observation_date', 'desc')
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\ImageColumn::make('attached_photo')
                        ->height('200px')
                        ->width('100%')
                        ->extraImgAttributes(['class' => 'object-cover w-full rounded-t-lg'])
                        ->defaultImageUrl(url('/images/placeholder-medical.png')),

                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('observation_date')
                            ->date('d M, Y')
                            ->weight('bold')
                            ->size('lg')
                            ->color('primary')
                            ->icon('heroicon-m-calendar'),

                        Tables\Columns\TextColumn::make('created_at')
                            ->since()
                            ->color('gray')
                            ->size('xs'),

                        Tables\Columns\TextColumn::make('notes')
                            ->html()
                            ->limit(150)
                            ->color('gray')
                            ->extraAttributes(['class' => 'prose prose-sm mt-2']),
                    ])->space(1),
                ])->space(2),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_photo')
                    ->label('Solo con Fotos')
                    ->query(fn ($query) => $query->whereNotNull('attached_photo')),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nueva Observación')
                    ->icon('heroicon-m-plus')
                    ->slideOver(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
