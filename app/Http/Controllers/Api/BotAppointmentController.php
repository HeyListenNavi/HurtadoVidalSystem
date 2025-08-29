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
        $phone = $validated['phone'] ?? null;

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

        // Crea la cita con el primer paso de la máquina de estados.
        $appointment = Appointment::create([
            'chat_id' => $chatId,
            'patient_name' => $userName,
            'phone' => $phone,
            'process_status' => 'in_progress',
            'current_step' => 'ask_patient_name', // <-- Estado inicial de la máquina
        ]);

        // Opcional: Busca o crea la conversación.
        Conversation::firstOrCreate(
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
            'next_question_text' => '¡Hola! Para comenzar, por favor, dime tu nombre completo.',
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
        
        $currentStep = $appointment->current_step;

        switch ($currentStep) {
            case 'ask_patient_name':
                $nextQuestionText = '¡Hola! Para comenzar, por favor, dime tu nombre completo.';
                break;

            case 'ask_appointment_date':
                $nextQuestionText = 'Gracias, ' . $appointment->patient_name . '. ¿Qué día te gustaría agendar tu cita? (Ej. 29/08/2025)';
                break;

            case 'ask_appointment_time':
                $nextQuestionText = 'Entendido. ¿A qué hora te gustaría? (Ej. 10:30 AM)';
                break;

            case 'ready_for_custom_questions':
                // Ahora pasamos a las preguntas dinámicas de tu tabla AppointmentQuestion.
                $nextQuestion = AppointmentQuestion::where('id', '>', $appointment->current_question_id ?? 0)
                    ->orderBy('id')
                    ->first();

                if ($nextQuestion) {
                    // Actualiza el estado para la siguiente pregunta dinámica.
                    $appointment->update([
                        'current_question_id' => $nextQuestion->id,
                        'current_step' => 'ask_custom_question', // <-- Nuevo estado para preguntas dinámicas
                    ]);
                    $nextQuestionText = $nextQuestion->question_text;
                } else {
                    // No hay más preguntas dinámicas, el proceso ha finalizado.
                    $appointment->update([
                        'process_status' => 'completed',
                        'current_step' => 'completed_process',
                    ]);
                    return response()->json([
                        'status' => 'process_completed',
                        'message' => '¡Felicidades, tu cita ha sido agendada con éxito!'
                    ]);
                }
                break;

            case 'ask_custom_question':
                // Continuamos con las preguntas dinámicas.
                $nextQuestion = AppointmentQuestion::where('id', '>', $appointment->current_question_id)
                    ->orderBy('id')
                    ->first();

                if ($nextQuestion) {
                    $appointment->update(['current_question_id' => $nextQuestion->id]);
                    $nextQuestionText = $nextQuestion->question_text;
                } else {
                    // Proceso de preguntas dinámicas completado.
                    $appointment->update([
                        'process_status' => 'completed',
                        'current_step' => 'completed_process',
                    ]);
                    return response()->json([
                        'status' => 'process_completed',
                        'message' => '¡Felicidades, tu cita ha sido agendada con éxito!'
                    ]);
                }
                break;

            case 'completed_process':
                return response()->json([
                    'status' => 'process_completed',
                    'message' => 'El proceso de agendamiento ya ha finalizado.'
                ]);
            
            default:
                return response()->json(['success' => false, 'message' => 'Estado de conversación no válido.'], 400);
        }
        
        return response()->json([
            'status' => 'next_question',
            'next_question_text' => $nextQuestionText,
            'current_step' => $appointment->current_step,
        ]);
    }

    /**
     * Procesa la respuesta del usuario y la guarda.
     */
    public function submitAnswer(Request $request, $chatId)
    {
        $request->validate([
            'user_response' => 'required|string',
        ]);
        
        $userResponse = $request->input('user_response');

        $appointment = Appointment::where('chat_id', $chatId)
            ->where('process_status', 'in_progress')
            ->first();
        
        if (!$appointment) {
            return response()->json(['success' => false, 'message' => 'Cita no encontrada o proceso terminado.'], 404);
        }
        
        $currentStep = $appointment->current_step;

        // Lógica para guardar la respuesta según el paso de la máquina.
        switch ($currentStep) {
            case 'ask_patient_name':
                $appointment->update([
                    'patient_name' => $userResponse,
                    'current_step' => 'ask_appointment_date',
                ]);
                break;

            case 'ask_appointment_date':
                $appointment->update([
                    'appointment_date' => $userResponse,
                    'current_step' => 'ask_appointment_time',
                ]);
                break;

            case 'ask_appointment_time':
                $appointment->update([
                    'appointment_time' => $userResponse,
                    'current_step' => 'ready_for_custom_questions',
                ]);
                break;

            case 'ask_custom_question':
                // Lógica para guardar respuestas de preguntas dinámicas.
                $question = AppointmentQuestion::find($appointment->current_question_id);
                if (!$question) {
                     return response()->json(['success' => false, 'message' => 'Pregunta dinámica no encontrada.'], 404);
                }

                AppointmentResponse::create([
                    'appointment_id' => $appointment->id,
                    'appointment_question_id' => $question->id,
                    'user_response' => $userResponse,
                    'question_text_snapshot' => $question->question_text,
                ]);
                break;
            
            case 'completed_process':
                return response()->json(['success' => false, 'message' => 'El proceso de agendamiento ya ha finalizado.'], 200);

            default:
                return response()->json(['success' => false, 'message' => 'Estado de conversación no válido.'], 400);
        }

        return response()->json([
            'success' => true, 
            'message' => 'Respuesta guardada y proceso avanzado.',
            'appointment' => $appointment
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
