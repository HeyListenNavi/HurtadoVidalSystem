<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentQuestionResource\Pages;
use App\Models\AppointmentQuestion;
use App\Models\AppointmentSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;

class AppointmentQuestionResource extends Resource
{
    protected static ?string $model = AppointmentQuestion::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationGroup = 'Configuración del Bot';

    protected static ?string $modelLabel = 'Pregunta de Pre-valoración';
    protected static ?string $pluralModelLabel = 'Cuestionario de Bot';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make()
                ->columnSpanFull()
                ->schema([
                    Forms\Components\Section::make('Contenido del Mensaje')
                        ->description('Redacte la pregunta tal como la verá el paciente en el chat.')
                        ->icon('heroicon-o-chat-bubble-bottom-center-text')
                        ->schema([
                            Forms\Components\Textarea::make('question_text')
                                ->hiddenLabel()
                                ->placeholder('Ej: ¿Cuenta con cirugías previas en la zona? (Describa)')
                                ->required()
                                ->rows(5)
                                ->extraInputAttributes(['class' => 'text-lg']),

                            Forms\Components\Placeholder::make('preview')->label('Vista Previa (Simulada)')->content(
                                fn(Forms\Get $get) => new \Illuminate\Support\HtmlString(
                                    '
                                        <div style="background-color: #E5DDD5; padding: 20px; border-radius: 8px; width: 100%; max-width: 400px;">
                                            <div style="background-color: #ffffff; padding: 6px 7px 8px 9px; border-radius: 0px 7.5px 7.5px 7.5px; box-shadow: 0 1px 0.5px rgba(0,0,0,0.13); width: fit-content; max-width: 100%;">

                                                <div style="font-size: 12.8px; color: #128C7E; font-weight: 500; margin-bottom: 2px; font-family: Helvetica, Arial, sans-serif;">
                                                    Asistente Virtual
                                                </div>

                                                <div style="font-size: 14.2px; color: #111b21; line-height: 19px; font-family: Helvetica, Arial, sans-serif;">
                                                    ' .
                                        nl2br(e($get('question_text') ?: 'Escribe tu pregunta aquí...')) .
                                        '
                                                    <span style="float: right; margin-left: 10px; margin-top: 4px; font-size: 11px; color: #667781;">
                                                        ' .
                                        now()->format('H:i') .
                                        '
                                                    </span>
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
            ->reorderable('order')
            ->defaultSort('order', 'asc')
            ->columns([Tables\Columns\TextColumn::make('order')->label('#')->sortable()->weight(FontWeight::Bold)->color('gray'), Tables\Columns\TextColumn::make('question_text')->label('Pregunta')->wrap()->searchable(), Tables\Columns\TextColumn::make('updated_at')->label('Actualizado')->since()->color('gray')->size('xs')->toggleable(isToggledHiddenByDefault: true)])
            ->actions([Tables\Actions\EditAction::make()->iconButton(), Tables\Actions\DeleteAction::make()->iconButton()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->emptyStateActions([Tables\Actions\CreateAction::make()->label('Agregar Primera Pregunta')->icon('heroicon-m-plus')]);
    }

    public static function getRelations(): array
    {
        return [];
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
