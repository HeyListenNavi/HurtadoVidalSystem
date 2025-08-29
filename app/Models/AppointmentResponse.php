<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'question_id',
        'question_text_snapshot',
        'user_response',
        "ai_decision",
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(AppointmentQuestion::class);
    }
}
