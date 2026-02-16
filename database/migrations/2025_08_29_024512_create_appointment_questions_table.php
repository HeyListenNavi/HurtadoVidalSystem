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
        Schema::create('appointment_questions', function (Blueprint $table) {
            $table->id();
            $table->text('question_text');
	    $table->foreignId('appointment_setting_id')->constrained()->cascadeOnDelete();
	    $table->json('approval_criteria')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_questions');
    }
};
