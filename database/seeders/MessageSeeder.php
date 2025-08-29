<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Conversation;
use App\Models\Message;

class MessageSeeder extends Seeder
{
    public function run(): void
    {
        $conversations = Conversation::all();
        foreach ($conversations as $conversation) {
            Message::factory(5)->create([
                'chat_id' => $conversation->chat_id,
            ]);
        }
    }
}
