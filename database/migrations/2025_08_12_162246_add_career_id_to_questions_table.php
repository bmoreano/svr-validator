<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void {
    Schema::table('questions', function (Blueprint $table) {
        $table->dropColumn('career_id');
        $table->dropColumn('code');
        $table->dropColumn('assigned_validator_id');
        $table->foreignId('career_id')->nullable()->after('author_id')
              ->constrained('careers')->onDelete('set null');
        $table->string('code')->unique()->after('author_id');
        $table->foreignId('assigned_validator_id')->nullable()->after('career_id')
              ->constrained('assigned_validator')->onDelete('set null');
    });
}

    /**
     * Reverse the migrations.
     */
public function down(): void
{
    Schema::table('questions', function (Blueprint $table) {
        $table->dropColumn('career_id');
        $table->dropColumn('code');
        $table->dropColumn('assigned_validator_id');
    });
}
};
