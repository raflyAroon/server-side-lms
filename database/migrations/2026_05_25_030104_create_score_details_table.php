<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('score_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('score_id')->constrained('scores')->onDelete('cascade');
            $table->foreignId('rubric_criteria_id')->constrained('rubric_criteria')->onDelete('cascade');
            $table->decimal('score_value', 10, 2);
            $table->unique(['score_id', 'rubric_criteria_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('score_details');
    }
};