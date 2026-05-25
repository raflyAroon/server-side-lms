<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->text('content');
            $table->foreignId('target_team_id')->nullable()->constrained('teams')->onDelete('cascade');
            $table->foreignId('target_stage_id')->nullable()->constrained('stages')->onDelete('cascade');
            $table->enum('type', ['global', 'stage', 'team']);
            $table->timestamp('published_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};