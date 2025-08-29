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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('chat_id')->unique();
            $table->string('user_name')->nullable();

            // Campos polimórficos con convención estándar
            $table->unsignedBigInteger('processable_id')->nullable();
            $table->string('processable_type')->nullable();

            $table->string('process_status')->default('in_progress');
            $table->timestamps();

            // Índice combinado para consultas rápidas
            $table->index(['processable_id', 'processable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
