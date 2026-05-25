<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('selection_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');
            $table->foreignId('stage_id')->constrained('stages')->onDelete('cascade');
            $table->boolean('is_passed');
            $table->text('note')->nullable();
            $table->timestamp('announced_at')->useCurrent();
            $table->unique(['team_id', 'stage_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('selection_results');
    }
};