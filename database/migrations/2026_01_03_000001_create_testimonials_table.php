<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('testimonials', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
      $table->foreignUuid('internship_id')->constrained()->onDelete('cascade');
      $table->text('content');
      $table->tinyInteger('rating')->default(5); 
      $table->string('position')->nullable(); 
      $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved');
      $table->timestamps();

      $table->unique(['user_id', 'internship_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('testimonials');
  }
};
