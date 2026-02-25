<?php

namespace App\Filament\Pages;

use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Relaticle\Flowforge\Filament\Pages\KanbanBoardPage;

class TaskBoardBoardPage extends KanbanBoardPage
{
    protected static ?string $navigationIcon = 'heroicon-o-view-columns';
    protected static ?string $navigationLabel = 'Tablero de Tareas';
    protected static ?string $title = 'Tareas';

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make('crear_tarea')
                ->model(Task::class)
                ->label('Nueva Tarea')
                ->form([
                    \Filament\Forms\Components\TextInput::make('title')
                        ->label('Título')
                        ->required(),

                    // CORRECCIÓN 1: Mostrar nombres, no IDs.
                    // Usamos 'first_name' porque es una columna real en tu tabla 'patients'.
                    \Filament\Forms\Components\Select::make('patient_id')
                        ->label('Paciente')
                        ->relationship('patient', 'first_name')
                        ->getOptionLabelFromRecordUsing(fn(\App\Models\Patient $record) => "{$record->first_name} {$record->last_name}")
                        ->searchable()
                        ->preload(),

                    // CORRECCIÓN 2: ToggleButtons en lugar de Select.
                    \Filament\Forms\Components\ToggleButtons::make('status')
                        ->label('Estado Inicial')
                        ->options([
                            'todo' => 'To Do',
                            'in_progress' => 'In Progress',
                            'completed' => 'Completed',
                        ])
                        ->colors([
                            'todo' => 'info',
                            'in_progress' => 'warning',
                            'completed' => 'success',
                        ])
                        ->default('todo')
                        ->inline() // Para que se vean como botones en fila
                        ->required(),
                ])
                ->mutateFormDataUsing(function (array $data): array {
                    $data['user_id'] = auth()->id();
                    return $data;
                })
                // CORRECCIÓN 3: Forzar la recarga del tablero.
                // Al ser una página personalizada, necesitas redirigir a sí misma para refrescar el estado del Kanban.
                ->after(fn($livewire) => $livewire->redirect(static::getUrl())),
        ];
    }

    public function getSubject(): Builder
    {
        return Task::query()->where('user_id', auth()->id());
    }

    public function mount(): void
    {
        $this
            ->titleField('title')
            ->orderField('position')
            ->columnField('status')
            ->columns([
                'todo' => 'To Do',
                'in_progress' => 'In Progress',
                'completed' => 'Completed',
            ])
            ->columnColors([
                'todo' => 'blue',
                'in_progress' => 'yellow',
                'completed' => 'green',
            ]);
    }
}
