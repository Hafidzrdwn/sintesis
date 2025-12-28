<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('presences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            
            // GPS Coordinates for Haversine calculation
            $table->decimal('check_in_latitude', 10, 8)->nullable();
            $table->decimal('check_in_longitude', 11, 8)->nullable();
            $table->decimal('check_out_latitude', 10, 8)->nullable();
            $table->decimal('check_out_longitude', 11, 8)->nullable();
            
            // Haversine distance result in meters
            $table->decimal('check_in_distance_meters', 10, 2)->nullable();
            $table->decimal('check_out_distance_meters', 10, 2)->nullable();
            
            // Attendance mode and status
            $table->enum('attendance_mode', ['WFO', 'WFH'])->default('WFO');
            $table->enum('status', ['present', 'late', 'absent', 'leave', 'sick', 'permit'])->default('present');
            
            // Photo proof paths
            $table->string('check_in_photo')->nullable();
            $table->string('check_out_photo')->nullable();
            
            // Additional fields
            $table->text('notes')->nullable();
            $table->boolean('is_validated')->default(false);
            $table->foreignUuid('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            // Indexes for faster queries
            $table->index('user_id');
            $table->index('date');
            $table->index(['user_id', 'date']);
            $table->index('status');
            $table->index('attendance_mode');
            
            // Unique constraint: one presence per user per day
            $table->unique(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presences');
    }
};
