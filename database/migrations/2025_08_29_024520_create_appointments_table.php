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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('chat_id')->unique();
            $table->string('patient_name')->nullable();
            $table->date('appointment_date')->nullable();
            $table->time('appointment_time')->nullable();
            $table->text('reason_for_visit')->nullable();
            $table->foreignId('current_question_id')->nullable()->constrained('appointment_questions')->onDelete('set null');
            $table->string('process_status')->default('in_progress'); // in_progress, completed, rejected, cancelled
            $table->text('rejection_reason')->nullable();
            $table->boolean('is_confirmed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
