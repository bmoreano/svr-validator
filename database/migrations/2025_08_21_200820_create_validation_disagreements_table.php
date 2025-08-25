<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('validation_disagreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->foreignId('criterion_id')->constrained()->onDelete('cascade');
            $table->foreignId('human_validator_id')->constrained('users')->onDelete('cascade');
            
            $table->string('ai_engine'); // 'chatgpt' o 'gemini'
            $table->string('ai_response'); // 'si', 'no', 'adecuar'
            $table->string('human_response'); // 'si', 'no', 'adecuar'
            
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('validation_disagreements'); }
};