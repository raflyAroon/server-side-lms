<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('team_members', function (Blueprint $table) {
            $table->string('nim', 50)->nullable()->after('phone');
            $table->string('faculty', 100)->nullable()->after('nim');
            $table->string('study_program', 100)->nullable()->after('faculty');
        });
    }

    public function down()
    {
        Schema::table('team_members', function (Blueprint $table) {
            $table->dropColumn(['nim', 'faculty', 'study_program']);
        });
    }
};