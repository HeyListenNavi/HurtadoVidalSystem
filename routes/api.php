<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BotConversationController;
use App\Http\Controllers\Api\BotMessageController;
use App\Http\Controllers\Api\BotAppointmentController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rutas de API para el bot conversacional
Route::prefix('bot')->group(function () {

    // Rutas para mensajes
    Route::post('messages', [BotMessageController::class, 'storeMessage']);
    Route::get('messages/{conversationId}', [BotMessageController::class, 'getMessages']);

    // Rutas para conversaciones
    Route::get('conversations/{chatId}', [BotConversationController::class, 'getOrCreateConversation']);
    Route::put('conversations/{conversationId}', [BotConversationController::class, 'updateConversation']);

    

    Route::prefix('appointments')->group(function () {
        // Inicia el proceso de agendamiento.
        Route::post('start', [BotAppointmentController::class, 'startAppointment']);
        
        // Obtiene la siguiente pregunta en el flujo de agendamiento.
        Route::get('{chatId}/next-question', [BotAppointmentController::class, 'getNextQuestion']);
        
        // Procesa la respuesta del usuario y la almacena.
        Route::post('{chatId}/submit-answer', [BotAppointmentController::class, 'submitAnswer']);
        
        // Evalúa si la etapa actual puede ser aprobada y el proceso puede continuar.
        Route::post('{chatId}/appointment-approval', [BotAppointmentController::class, 'handleAppointmentApproval']);
        
        // Obtiene el estado actual de la cita para el chat especificado.
        Route::get('{chatId}/status', [BotAppointmentController::class, 'getAppointmentStatus']);

        // Permite actualizar manualmente la información de la cita.
        Route::put('{appointmentId}/update-manually', [BotAppointmentController::class, 'updateManually']);
    });

});