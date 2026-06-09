<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->enum('selection_status', ['pending', 'approved', 'rejected'])->default('pending')->after('city');
            $table->text('selection_note')->nullable()->after('selection_status');
            $table->timestamp('selection_processed_at')->nullable()->after('selection_note');
        });
    }

    public function down()
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn(['selection_status', 'selection_note', 'selection_processed_at']);
        });
    }
};