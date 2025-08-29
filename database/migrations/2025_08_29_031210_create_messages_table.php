<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('chat_id'); // Usamos chat_id en lugar de conversation_id para la consistencia
            $table->string('phone')->nullable();
            $table->string('name')->nullable();
            $table->text('message');
            $table->string('role'); // user, bot
            $table->timestamps();

            $table->foreign('chat_id')->references('chat_id')->on('conversations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
