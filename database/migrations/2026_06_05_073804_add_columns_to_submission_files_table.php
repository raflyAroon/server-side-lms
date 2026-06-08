<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submission_files', function (Blueprint $table) {
            $table->enum('file_type', ['file', 'link'])->default('file');
            $table->text('external_url')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->string('file_path', 500)->nullable(); // path internal
            $table->boolean('is_verified')->default(false); // untuk admin verifikasi link
        });
    }

    public function down(): void
    {
        Schema::table('submission_files', function (Blueprint $table) {
            $table->dropColumn(['file_type', 'external_url', 'mime_type', 'file_path', 'is_verified']);
        });
    }
};