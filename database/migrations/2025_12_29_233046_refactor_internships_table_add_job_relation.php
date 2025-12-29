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
        // First, drop the orphaned job_id column if it exists from failed migration
        if (Schema::hasColumn('internships', 'job_id')) {
            Schema::table('internships', function (Blueprint $table) {
                $table->dropColumn('job_id');
            });
        }

        // Now add job_id properly with foreign key
        Schema::table('internships', function (Blueprint $table) {
            $table->foreignUuid('job_id')->nullable()->after('applicant_id')->constrained('job_listings')->nullOnDelete();
        });

        // Rename position to custom_position and drop department
        Schema::table('internships', function (Blueprint $table) {
            $table->renameColumn('position', 'custom_position');
            $table->dropColumn('department');
        });

        // Make custom_position nullable
        Schema::table('internships', function (Blueprint $table) {
            $table->string('custom_position')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internships', function (Blueprint $table) {
            // Remove job_id
            $table->dropForeign(['job_id']);
            $table->dropColumn('job_id');
            
            // Rename custom_position back to position
            $table->renameColumn('custom_position', 'position');
            
            // Add department back
            $table->string('department')->nullable()->after('position');
        });

        // Make position required again
        Schema::table('internships', function (Blueprint $table) {
            $table->string('position')->nullable(false)->change();
        });
    }
};
