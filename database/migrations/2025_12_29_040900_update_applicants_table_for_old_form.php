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
        Schema::table('applicants', function (Blueprint $table) {
            // Step 1: Additional Identity fields
            $table->string('referral')->nullable()->after('university');
            
            // Step 2: Competencies (JSON arrays)
            $table->json('skills')->nullable()->after('referral');
            $table->string('other_skills')->nullable()->after('skills');
            $table->json('databases')->nullable()->after('other_skills');
            $table->string('other_databases')->nullable()->after('databases');
            $table->json('operating_systems')->nullable()->after('other_databases');
            $table->string('other_os')->nullable()->after('operating_systems');
            
            // Step 3: Interests
            $table->string('other_interest')->nullable()->after('other_os');
            $table->boolean('demo_required')->nullable()->after('other_interest');
            $table->text('self_description')->nullable()->after('demo_required');
            
            // Step 4: Documents & Dates
            $table->date('start_date')->nullable()->after('portfolio_url');
            $table->date('end_date')->nullable()->after('start_date');
            $table->string('letter_path')->nullable()->after('cv_path');
            $table->string('id_card_path')->nullable()->after('letter_path');
            
            // Remove fields not needed
            $table->dropColumn(['birth_date', 'gender', 'address', 'major', 'semester']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            // Add back removed columns
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->text('address')->nullable();
            $table->string('major')->nullable();
            $table->string('semester')->nullable();
            
            // Drop new columns
            $table->dropColumn([
                'referral', 'skills', 'other_skills', 'databases', 'other_databases',
                'operating_systems', 'other_os', 'other_interest', 'demo_required',
                'self_description', 'start_date', 'end_date', 'letter_path', 'id_card_path'
            ]);
        });
    }
};
