<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('internship_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('intern_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('mentor_id')->constrained('users')->onDelete('cascade');

            // Rating scores (1-5)
            $table->tinyInteger('technical_skill')->default(0);
            $table->tinyInteger('communication')->default(0);
            $table->tinyInteger('teamwork')->default(0);
            $table->tinyInteger('initiative')->default(0);
            $table->tinyInteger('punctuality')->default(0);
            $table->tinyInteger('overall_rating')->default(0);

            // Text feedback
            $table->text('strengths')->nullable();
            $table->text('improvements')->nullable();
            $table->text('recommendation')->nullable();
            $table->text('final_notes')->nullable();

            // Status
            $table->enum('status', ['draft', 'submitted'])->default('draft');
            $table->timestamp('submitted_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
