<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Appointment;
use App\Models\AppointmentQuestion;
use App\Models\AppointmentResponse;

class BotAppointmentController extends Controller
{
    /**
     * Inicia una nueva cita y el proceso de conversación para un chat_id.
     * Si la conversación ya existe, la recupera.
     */
    public function startAppointment(Request $request)
    {
        $request->validate([
            'chat_id' => 'required|string',
            'user_name' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        $chatId = $request->input('chat_id');
        $userName = $request->input('user_name');

        // Busca o crea la conversación.
        $conversation = Conversation::firstOrCreate(
            ['chat_id' => $chatId],
            ['user_name' => $userName, 'current_process' => Appointment::class]
        );

        // Busca una cita pendiente para este chat o crea una nueva.
        $appointment = Appointment::firstOrCreate(
            ['chat_id' => $chatId, 'process_status' => 'in_progress'],
            ['patient_name' => $userName]
        );

        // Retorna la respuesta inicial al bot de WA
        return response()->json([
            'success' => true,
            'message' => 'Proceso de cita iniciado.',
            'conversation' => $conversation,
            'appointment' => $appointment
        ]);
    }

    /**
     * Obtiene la siguiente pregunta para el proceso de agendamiento.
     */
    public function getNextQuestion($chatId)
    {
        $conversation = Conversation::where('chat_id', $chatId)->first();

        if (!$conversation) {
            return response()->json(['success' => false, 'message' => 'Conversación no encontrada.'], 404);
        }

        // Aquí deberías tener la lógica para encontrar la siguiente pregunta
        // basada en la etapa actual y las preguntas ya respondidas.
        // Por ahora, es un esqueleto.
        $nextQuestion = AppointmentQuestion::orderBy('order')->first();

        if ($nextQuestion) {
            return response()->json(['success' => true, 'question' => $nextQuestion]);
        }

        return response()->json(['success' => false, 'message' => 'No hay más preguntas.'], 404);
    }

    /**
     * Procesa la respuesta del usuario y la guarda.
     */
    public function submitAnswer(Request $request, $chatId)
    {
        $request->validate([
            'question_id' => 'required|integer',
            'user_response' => 'required|string',
        ]);
        
        $conversation = Conversation::where('chat_id', $chatId)->first();
        if (!$conversation) {
             return response()->json(['success' => false, 'message' => 'Conversación no encontrada.'], 404);
        }

        $appointment = $conversation->processable;

        // Guarda la respuesta en la base de datos.
        $response = AppointmentResponse::create([
            'appointment_id' => $appointment->id,
            'question_id' => $request->input('question_id'),
            'user_response' => $request->input('user_response'),
            'question_text_snapshot' => AppointmentQuestion::find($request->input('question_id'))->question_text,
            'ai_decision' => json_encode(['status' => 'pending'])
        ]);

        // Aquí se puede agregar la lógica para la validación de la respuesta.
        // ...

        return response()->json(['success' => true, 'response' => $response]);
    }

    /**
     * Valida las respuestas de la etapa actual.
     */
    public function handleStageApproval(Request $request, $chatId)
    {
        // Lógica para evaluar si la cita puede avanzar.
        // ...
        return response()->json(['success' => true, 'message' => 'Lógica de aprobación en proceso.']);
    }

    /**
     * Obtiene el estado actual de la cita.
     */
    public function getAppointmentStatus($chatId)
    {
        $appointment = Appointment::where('chat_id', $chatId)->first();
        if (!$appointment) {
             return response()->json(['success' => false, 'message' => 'Cita no encontrada.'], 404);
        }

        return response()->json(['success' => true, 'status' => $appointment->process_status]);
    }

    /**
     * Actualiza manualmente la cita (útil para el panel de administración).
     */
    public function updateManually(Request $request, $appointmentId)
    {
        $appointment = Appointment::find($appointmentId);
        if (!$appointment) {
            return response()->json(['success' => false, 'message' => 'Cita no encontrada.'], 404);
        }

        $appointment->update($request->all());

        return response()->json(['success' => true, 'appointment' => $appointment]);
    }
}
