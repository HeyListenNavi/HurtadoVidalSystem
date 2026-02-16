<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Set;
use Filament\Forms\Get;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Administración';

    protected static ?string $modelLabel = 'Usuario';
    protected static ?string $pluralModelLabel = 'Usuarios del Sistema';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Perfil del Usuario')
                            ->description('Información básica y nivel de acceso.')
                            ->icon('heroicon-o-user-circle')
                            ->schema([
                                Forms\Components\TextInput::make('name')->label('Nombre Completo')->required()->prefixIcon('heroicon-m-user')->maxLength(255),

                                Forms\Components\TextInput::make('email')->label('Correo Electrónico')->email()->required()->prefixIcon('heroicon-m-at-symbol')->unique(ignoreRecord: true)->maxLength(255),

                                Forms\Components\Select::make('role')
                                    ->label('Rol en el Sistema')
                                    ->options([
                                        'doctor' => 'Doctor / Especialista',
                                        'assistant' => 'Asistente Médico',
                                        'receptionist' => 'Recepcionista',
                                        'admin' => 'Administrador',
                                    ])
                                    ->required()
                                    ->native(false)
                                    ->prefixIcon('heroicon-m-shield-check')
                                    ->default('assistant'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Seguridad')
                            ->description('Credenciales de acceso.')
                            ->icon('heroicon-o-lock-closed')
                            ->schema([Forms\Components\TextInput::make('password')->label('Contraseña')->password()->revealable()->confirmed()->dehydrateStateUsing(fn(string $state): string => Hash::make($state))->dehydrated(fn(?string $state): bool => filled($state))->required(fn(string $operation): bool => $operation === 'create')->helperText(fn(string $operation) => $operation === 'edit' ? 'Dejar vacío para mantener la actual.' : null)->maxLength(255), Forms\Components\TextInput::make('password_confirmation')->label('Confirmar Contraseña')->password()->revealable()->required(fn(Get $get): bool => filled($get('password')))->dehydrated(false)]),
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
                Tables\Columns\TextColumn::make('name')->label('Usuario')->searchable()->description(fn(User $record) => $record->email)->icon('heroicon-m-user-circle'),

                Tables\Columns\TextColumn::make('role')
                    ->label('Rol')
                    ->badge()
                    ->formatStateUsing(
                        fn(string $state): string => match ($state) {
                            'doctor' => 'Doctor',
                            'assistant' => 'Asistente',
                            'receptionist' => 'Recepcionista',
                            'admin' => 'Administrador',
                            default => $state,
                        },
                    )
                    ->color(
                        fn(string $state): string => match ($state) {
                            'doctor' => 'info',
                            'assistant' => 'success',
                            'receptionist' => 'warning',
                            'admin' => 'danger',
                            default => 'gray',
                        },
                    )
                    ->icon(
                        fn(string $state): string => match ($state) {
                            'doctor' => 'heroicon-m-academic-cap',
                            'assistant' => 'heroicon-m-heart',
                            'receptionist' => 'heroicon-m-phone',
                            'admin' => 'heroicon-m-shield-check',
                            default => 'heroicon-m-user',
                        },
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')->label('Registrado')->since()->color('gray')->size('xs')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Filtrar por Rol')
                    ->options([
                        'doctor' => 'Doctores',
                        'assistant' => 'Asistentes',
                        'receptionist' => 'Recepcionistas',
                        'admin' => 'Administradores',
                    ]),
            ])
            ->actions([Tables\Actions\EditAction::make()->iconButton(), Tables\Actions\DeleteAction::make()->iconButton()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
