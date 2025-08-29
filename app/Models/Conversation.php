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
        'current_process',
        'process_id',
        'process_status',
    ];

    /**
     * Relación con los mensajes de la conversación.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'chat_id', 'chat_id');
    }

    /**
     * Relación polimórfica al proceso activo.
     */
    public function processable(): MorphTo
    {
        return $this->morphTo('processable', 'current_process', 'process_id');
    }
}
