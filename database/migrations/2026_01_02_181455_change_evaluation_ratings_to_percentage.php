<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->smallInteger('technical_skill')->default(0)->change();
            $table->smallInteger('communication')->default(0)->change();
            $table->smallInteger('teamwork')->default(0)->change();
            $table->smallInteger('initiative')->default(0)->change();
            $table->smallInteger('punctuality')->default(0)->change();
            $table->smallInteger('overall_rating')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->tinyInteger('technical_skill')->default(0)->change();
            $table->tinyInteger('communication')->default(0)->change();
            $table->tinyInteger('teamwork')->default(0)->change();
            $table->tinyInteger('initiative')->default(0)->change();
            $table->tinyInteger('punctuality')->default(0)->change();
            $table->tinyInteger('overall_rating')->default(0)->change();
        });
    }
};
