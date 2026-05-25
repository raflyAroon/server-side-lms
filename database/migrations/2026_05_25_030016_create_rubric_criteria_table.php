<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rubric_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rubric_id')->constrained('rubrics')->onDelete('cascade');
            $table->string('criterion_name', 100);
            $table->integer('max_score')->check('max_score > 0');
            $table->decimal('weight', 5, 2)->default(1.0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rubric_criteria');
    }
};