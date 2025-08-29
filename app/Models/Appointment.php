<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'patient_name',
        'appointment_date',
        'appointment_time',
        'reason_for_visit',
        'current_stage_id',
        'current_question_id',
        'process_status',
        'rejection_reason',
        'is_confirmed',
    ];

    protected $casts = [
        'is_confirmed' => 'boolean',
    ];

    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(AppointmentSetting::class, 'current_stage_id');
    }

    public function currentQuestion(): BelongsTo
    {
        return $this->belongsTo(AppointmentQuestion::class, 'current_question_id');
    }

    public function conversation(): MorphOne
    {
        return $this->morphOne(Conversation::class, 'processable');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(AppointmentResponse::class);
    }
}
