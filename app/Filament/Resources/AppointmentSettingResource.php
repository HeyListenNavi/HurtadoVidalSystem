<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentSettingResource\Pages;
use App\Models\AppointmentSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;

class AppointmentSettingResource extends Resource
{
    protected static ?string $model = AppointmentSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
    protected static ?string $navigationGroup = 'Configuración del Bot';

    protected static ?string $modelLabel = 'Flujo de Chat';
    protected static ?string $pluralModelLabel = 'Flujos de Chat';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Identidad del Flujo')
                            ->description('Nombre interno para identificar este conjunto de reglas.')
                            ->icon('heroicon-o-finger-print')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre del Flujo')
                                    ->placeholder('Ej: Flujo Estándar 2025, Campaña Botox...')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-m-tag'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),

                Forms\Components\Group::make()
                    ->schema([

                        Forms\Components\Section::make('Configuración del Agente')
                            ->description('Instrucciones, personalidad, reglas de comportamiento y tono del agente.')
                            ->icon('heroicon-o-cpu-chip')
                            ->schema([
                                Forms\Components\RichEditor::make('agent_configuration')
                                    ->label('Configuración del agente')
                                    ->placeholder('Ej: Actúa como un asesor médico profesional, usa un tono empático...')
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'underline',
                                        'bulletList',
                                        'orderedList',
                                        'h2',
                                        'h3',
                                        'link',
                                    ])
                                    ->columnSpanFull()
                                    ->required(),
                            ]),

                        Forms\Components\Section::make('RAG - Memoria e Información de Productos')
                            ->description('Base de conocimiento del agente: productos, precios, protocolos, FAQs.')
                            ->icon('heroicon-o-circle-stack')
                            ->schema([
                                Forms\Components\RichEditor::make('rag_content')
                                    ->label('Contenido RAG')
                                    ->placeholder('Ej: Producto: Botox Premium\nPrecio: $4,500\nDuración: 6 meses...')
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'underline',
                                        'bulletList',
                                        'orderedList',
                                        'h2',
                                        'h3',
                                        'link',
                                    ])
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Section::make('Respuestas Automáticas (Fallback)')
                            ->description('Mensajes que el bot enviará en situaciones de rechazo.')
                            ->icon('heroicon-o-exclamation-triangle')
                            ->schema([
                                Forms\Components\Textarea::make('rejection_reason')
                                    ->label('Mensaje de Rechazo por Defecto')
                                    ->helperText('Este mensaje se enviará si el paciente no cumple los criterios y no hay una razón específica.')
                                    ->placeholder('Lo sentimos, en este momento no podemos agendar su cita debido a...')
                                    ->rows(4)
                                    ->required()
                                    ->columnSpanFull()
                                    ->autoSize(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),
            ])
            ->columns(1);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([Tables\Columns\TextColumn::make('name')->label('Nombre del Flujo')->searchable()->sortable()->icon('heroicon-m-cpu-chip')->description(fn($record) => 'Creado: ' . $record->created_at->format('d/m/Y')), Tables\Columns\TextColumn::make('rejection_reason')->label('Mensaje de Rechazo')->limit(60)->tooltip(fn($record) => $record->rejection_reason)->icon('heroicon-m-chat-bubble-left-ellipsis')->color('gray'), Tables\Columns\TextColumn::make('updated_at')->label('Última Edición')->since()->color('gray')->size('xs')->toggleable(isToggledHiddenByDefault: true)])
            ->filters([
                //
            ])
            ->actions([Tables\Actions\EditAction::make()->iconButton(), Tables\Actions\DeleteAction::make()->iconButton()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->emptyStateActions([Tables\Actions\CreateAction::make()->label('Crear Nuevo Flujo')->icon('heroicon-m-plus')]);
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
            'index' => Pages\ListAppointmentSettings::route('/'),
            'create' => Pages\CreateAppointmentSetting::route('/create'),
            'edit' => Pages\EditAppointmentSetting::route('/{record}/edit'),
        ];
    }
}
