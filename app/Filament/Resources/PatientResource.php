<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatientResource\Pages;
use App\Filament\Resources\PatientResource\RelationManagers;
use App\Models\Patient;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PatientResource extends Resource
{
    protected static ?string $model = Patient::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $activeNavigationIcon = 'heroicon-s-user-group';

    protected static ?string $modelLabel = 'Paciente';

    protected static ?string $pluralModelLabel = 'Pacientes';

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Ficha de Identificación')
                            ->description('Datos demográficos principales')
                            ->icon('heroicon-o-identification')
                            ->schema([
                                Forms\Components\Split::make([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\TextInput::make('first_name')
                                                ->label('Nombres')
                                                ->required()
                                                ->prefixIcon('heroicon-m-user')
                                                ->maxLength(255),
                                            Forms\Components\TextInput::make('last_name')
                                                ->label('Apellidos')
                                                ->required()
                                                ->prefixIcon('heroicon-m-user')
                                                ->maxLength(255),
                                        ]),
                                ]),

                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\DatePicker::make('birth_date')
                                            ->label('Fecha de Nacimiento')
                                            ->required()
                                            ->native(false)
                                            ->displayFormat('d/m/Y')
                                            ->maxDate(now()),

                                        Forms\Components\Select::make('gender')
                                            ->label('Género')
                                            ->options([
                                                'male' => 'Masculino',
                                                'female' => 'Femenino',
                                                'other' => 'Otro',
                                            ])
                                            ->native(false)
                                            ->required(),

                                        Forms\Components\TextInput::make('phone')
                                            ->label('Teléfono Móvil')
                                            ->tel()
                                            ->prefixIcon('heroicon-m-device-phone-mobile')
                                            ->maxLength(255),
                                    ]),

                                Forms\Components\TextInput::make('email')
                                    ->label('Correo Electrónico')
                                    ->email()
                                    ->prefixIcon('heroicon-m-at-symbol')
                                    ->columnSpanFull()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('address')
                                    ->label('Domicilio')
                                    ->prefixIcon('heroicon-m-map-pin')
                                    ->columnSpanFull()
                                    ->maxLength(255),
                            ]),

                        Forms\Components\Section::make('Contacto de Emergencia')
                            ->icon('heroicon-o-shield-exclamation')
                            ->collapsed()
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('emergency_contact_name')
                                        ->label('Nombre del Contacto')
                                        ->prefixIcon('heroicon-m-user-circle'),
                                    Forms\Components\TextInput::make('emergency_contact_phone')
                                        ->label('Teléfono')
                                        ->tel()
                                        ->prefixIcon('heroicon-m-phone'),
                                ]),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Perfil Clínico')
                            ->description('Antecedentes y Alertas')
                            ->icon('heroicon-o-heart')
                            ->schema([
                                Forms\Components\Select::make('blood_type')
                                    ->label('Tipo de Sangre')
                                    ->options([
                                        'A+' => 'A+', 'A-' => 'A-',
                                        'B+' => 'B+', 'B-' => 'B-',
                                        'AB+' => 'AB+', 'AB-' => 'AB-',
                                        'O+' => 'O+', 'O-' => 'O-',
                                    ])
                                    ->native(false),

                                Forms\Components\Textarea::make('allergies')
                                    ->label('Alergias')
                                    ->placeholder('NINGUNA CONOCIDA')
                                    ->helperText('⚠️ Indicar claramente si existen reacciones a medicamentos.')
                                    ->rows(3)
                                    ->autoSize()
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('medical_history')
                                    ->label('Historial Médico')
                                    ->placeholder('Cirugías previas, condiciones crónicas...')
                                    ->rows(6)
                                    ->autoSize()
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Paciente')
                    ->description(fn (Patient $record) => $record->email)
                    ->searchable(['first_name', 'last_name', 'email'])
                    ->sortable(['first_name'])
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('birth_date')
                    ->label('Edad')
                    ->formatStateUsing(fn ($state) => Carbon::parse($state)->age.' años')
                    ->description(fn (Patient $record) => Carbon::parse($record->birth_date)->format('d/m/Y'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Contacto')
                    ->icon('heroicon-m-phone')
                    ->copyable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('blood_type')
                    ->label('Sangre')
                    ->badge()
                    ->color('danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrado')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->iconButton(),
                Tables\Actions\EditAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PatientObservationsRelationManager::class,
            RelationManagers\QuotesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPatients::route('/'),
            'create' => Pages\CreatePatient::route('/create'),
            'edit' => Pages\EditPatient::route('/{record}/edit'),
        ];
    }
}
