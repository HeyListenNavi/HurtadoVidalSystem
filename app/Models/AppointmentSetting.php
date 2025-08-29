<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AppointmentSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'rejection_reason',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(AppointmentQuestion::class);
    }
}
