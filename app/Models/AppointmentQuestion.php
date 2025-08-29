<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AppointmentQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_setting_id',
        'question_text',
        'approval_criteria',
        'order',
    ];

    protected $casts = [
        'approval_criteria' => 'json',
    ];

    public function setting(): BelongsTo
    {
        return $this->belongsTo(AppointmentSetting::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(AppointmentResponse::class);
    }
}
