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
        $validated = $request->validate([
            'chat_id' => 'required|string',
            'user_name' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        $chatId = $validated['chat_id'];
        $userName = $validated['user_name'] ?? 'Paciente';

        // Busca una cita en progreso para evitar duplicados.
        $appointment = Appointment::where('chat_id', $chatId)
            ->where('process_status', 'in_progress')
            ->first();

        if ($appointment) {
            return response()->json([
                'success' => true,
                'message' => 'Ya tienes un proceso de cita en curso.',
                'appointment' => $appointment
            ]);
        }

        // Encuentra la primera pregunta en la secuencia.
        $firstQuestion = AppointmentQuestion::orderBy('order')->first();

        if (!$firstQuestion) {
            return response()->json(['error' => 'No hay preguntas de cita configuradas.'], 404);
        }

        // Crea la cita y la asocia a la primera pregunta.
        $appointment = Appointment::create([
            'chat_id' => $chatId,
            'patient_name' => $userName,
            'current_question_id' => $firstQuestion->id,
            'process_status' => 'in_progress',
        ]);

        // Busca o crea la conversación y la asocia a la cita.
        $conversation = Conversation::firstOrCreate(
            ['chat_id' => $chatId],
            [
                'user_name' => $userName,
                'current_process_id' => $appointment->id,
                'current_process_type' => Appointment::class
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Proceso de cita iniciado.',
            'appointment' => $appointment,
            'next_question_text' => $firstQuestion->question_text,
        ]);
    }

    /**
     * Obtiene la siguiente pregunta en el proceso de agendamiento.
     */
    public function getNextQuestion($chatId)
    {
        $appointment = Appointment::where('chat_id', $chatId)
            ->where('process_status', 'in_progress')
            ->first();

        if (!$appointment) {
            return response()->json(['success' => false, 'message' => 'No se encontró una cita en proceso o el proceso ha terminado.'], 404);
        }

        $currentQuestion = $appointment->currentQuestion;

        if (!$currentQuestion) {
            return response()->json(['success' => false, 'message' => 'No se pudo determinar la pregunta actual.'], 404);
        }

        // Busca la siguiente pregunta por orden.
        $nextQuestion = AppointmentQuestion::where('order', '>', $currentQuestion->order)
            ->orderBy('order')
            ->first();

        if (!$nextQuestion) {
            // No hay más preguntas, el proceso ha finalizado.
            $appointment->update([
                'process_status' => 'completed',
                'is_confirmed' => true,
            ]);

            return response()->json([
                'status' => 'process_completed',
                'message' => '¡Felicidades, tu cita ha sido agendada con éxito!'
            ]);
        } else {
            // Avanza a la siguiente pregunta.
            $appointment->update(['current_question_id' => $nextQuestion->id]);
            
            return response()->json([
                'status' => 'next_question',
                'question' => $nextQuestion,
                'next_question_text' => $nextQuestion->question_text,
            ]);
        }
    }


    /**
     * Procesa la respuesta del usuario y la guarda.
     */
    public function submitAnswer(Request $request, $chatId)
    {
        $request->validate([
            'question_id' => 'required|integer',
            'user_response' => 'required|string',
            'ai_decision' => 'nullable|string',
        ]);
        
        $appointment = Appointment::where('chat_id', $chatId)->where('process_status', 'in_progress')->first();
        
        if (!$appointment) {
            return response()->json(['success' => false, 'message' => 'Cita no encontrada o proceso terminado.'], 404);
        }

        $question = AppointmentQuestion::find($request->input('question_id'));
        if (!$question) {
            return response()->json(['success' => false, 'message' => 'Pregunta no encontrada.'], 404);
        }

        // Usa updateOrCreate para guardar la respuesta.
        $response = AppointmentResponse::updateOrCreate(
            [
                'appointment_id' => $appointment->id,
                'question_id' => $question->id,
            ],
            [
                'user_response' => $request->input('user_response'),
                'ai_decision' => $request->input('ai_decision') ?? 'pending',
                'question_text_snapshot' => $question->question_text,
            ]
        );

        // Opcionalmente, puedes actualizar el estado de la cita si se requiere supervisión.
        if ($response->ai_decision === 'requires_supervision' || $response->ai_decision === 'not_valid') {
            $appointment->update(['process_status' => $response->ai_decision === 'not_valid' ? 'rejected' : 'requires_supervision']);
        }

        return response()->json([
            'success' => true, 
            'message' => 'Respuesta guardada.',
            'response' => $response
        ]);
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