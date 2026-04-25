<?php

namespace App\Filament\Pages;

use App\Models\AppointmentSetting;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RagSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';
    protected static ?string $navigationGroup = 'Configuración del Bot';
    protected static ?string $title = 'Gestión del Conocimiento';

    protected static string $view = 'filament.pages.rag-settings';

    public ?array $data = [];
    public ?AppointmentSetting $setting = null;

    public function mount(): void
    {
        $this->setting = AppointmentSetting::firstOrCreate(
            [],
            [
                'name' => 'default', 
                'rag_content' => ''
            ]
        );

        $this->form->fill([
            'rag_content' => $this->setting->rag_content,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Base de Conocimiento')
                    ->description('Escribe el contenido que el bot utilizará para responder consultas.')
                    ->schema([
                        MarkdownEditor::make('rag_content')
                            ->label('Contenido del RAG')
                            ->placeholder("# Información de la Clínica...")
                            ->toolbarButtons([
                                'blockquote', 'bold', 'bulletList', 'codeBlock', 'heading',
                                'italic', 'link', 'orderedList', 'redo', 'strike', 'undo',
                            ])
                            ->columnSpanFull()
                            ->required(),
                    ])
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sync_rag')
                ->label('Sincronizar')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('¿Sincronizar Base de Conocimiento?')
                ->modalDescription('El contenido se enviará al bot para actualizar su base de conocimiento.')
                ->action(fn () => $this->syncToN8n()),
        ];
    }

    public function syncToN8n()
    {
        $this->validate();
        
        $markdownContent = $this->data['rag_content'] ?? '';
        $this->setting->update(['rag_content' => $markdownContent]);

        $webhookUrl = config('services.n8n.rag_webhook_url');

        if (!$webhookUrl) {
            Notification::make()->title('Error de Configuración: Contacta a un administrador')->danger()->send();
            return;
        }

        try {
            $response = Http::timeout(60)
                ->attach('data', $markdownContent, 'knowledge_base.md')
                ->post($webhookUrl, [
                    'markdown' => $markdownContent
                ]);

            $status = $response->status();
            $body = trim($response->body());

            if ($status === 200) {
                Notification::make()
                    ->title('Sincronización Exitosa')
                    ->body('La base de conocimiento del bot ha sido actualizado correctamente')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Error al Sincronizar')
                    ->body('Respuesta del servidor: ' . ($body ?: 'Sin respuesta (Status: ' . $status . ')'))
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error de Conexión')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
