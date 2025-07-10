<?php

// database/migrations/..._create_criteria_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('criteria', function (Blueprint $table) {
            $table->id();
            $table->text('text');
            $table->enum('category', ['formulacion', 'opciones', 'argumentacion', 'bibliografia']);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
        });
    }
    public function down(): void { 
        Schema::dropIfExists('criteria'); 
    }
};