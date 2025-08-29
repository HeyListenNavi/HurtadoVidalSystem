<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'user_name',
        'processable_id',    // agregado
        'processable_type',  // agregado
        'process_status',    // si lo usas
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'chat_id', 'chat_id');
    }

    public function processable(): MorphTo
    {
        return $this->morphTo();
    }
}
