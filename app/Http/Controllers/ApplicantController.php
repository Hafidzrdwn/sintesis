<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApplicantRequest;
use App\Models\Applicant;
use App\Models\Job;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class ApplicantController extends Controller
{
    /**
     * Store a new internship application
     */
    public function store(StoreApplicantRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        // Check if user can apply (prevent double application)
        if (!$user->canApply()) {
            return redirect()->route('dashboard')
                ->with('error', 'Anda sudah memiliki lamaran yang sedang diproses.');
        }

        $validated = $request->validated();
        
        // Get the job to store position name
        $job = Job::findOrFail($validated['job_id']);
        
        // Handle file uploads
        $letterPath = null;
        $idCardPath = null;
        $cvPath = null;
        
        if ($request->hasFile('letter_file')) {
            $letterPath = $request->file('letter_file')->store('applicants/letters', 'public');
        }
        
        if ($request->hasFile('id_card_file')) {
            $idCardPath = $request->file('id_card_file')->store('applicants/id_cards', 'public');
        }
        
        if ($request->hasFile('cv_file')) {
            $cvPath = $request->file('cv_file')->store('applicants/cv', 'public');
        }
        
        // Create the applicant
        Applicant::create([
            'job_id' => $validated['job_id'],
            'position_applied' => $job->title,
            
            // Step 1: Identity
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'university' => $validated['university'],
            'referral' => $validated['referral'] ?? null,
            
            // Step 2: Competencies
            'skills' => $validated['skills'] ?? [],
            'other_skills' => $validated['other_skills'] ?? null,
            'databases' => $validated['databases'] ?? [],
            'other_databases' => $validated['other_databases'] ?? null,
            'operating_systems' => $validated['operating_systems'] ?? [],
            'other_os' => $validated['other_os'] ?? null,
            
            // Step 3: Interests
            'other_interest' => $validated['other_interest'] ?? null,
            'demo_required' => $validated['demo_required'] ?? null,
            'self_description' => $validated['self_description'],
            'portfolio_url' => $validated['portfolio_url'] ?? null,
            
            // Step 4: Documents
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'letter_path' => $letterPath ? '/storage/' . $letterPath : null,
            'id_card_path' => $idCardPath ? '/storage/' . $idCardPath : null,
            'cv_path' => $cvPath ? '/storage/' . $cvPath : null,
            
            // Meta
            'status' => 'pending',
        ]);
        
        return redirect()->route('dashboard')
            ->with('success', 'Lamaran Anda berhasil dikirim! Kami akan menghubungi Anda melalui email.');
    }
}
