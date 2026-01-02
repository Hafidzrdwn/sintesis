<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class EvaluationController extends Controller
{
  public function index(Request $request)
  {
    $mentor = $request->user();
    $search = $request->get('search', '');
    $status = $request->get('status', 'all');

    $menteesQuery = $mentor->mentees()
      ->with(['intern:id,name,email,avatar', 'job', 'evaluation' => fn($q) => $q->where('mentor_id', $mentor->id)]);

    if ($search) {
      $menteesQuery->whereHas('intern', fn($q) => $q->where('name', 'like', "%{$search}%"));
    }

    $mentees = $menteesQuery->get()->map(function ($internship) {
      $evaluation = $internship->evaluation;

      return [
        'id' => $internship->id,
        'intern_id' => $internship->intern_id,
        'intern_name' => $internship->intern?->name,
        'intern_email' => $internship->intern?->email,
        'intern_avatar' => $internship->intern?->avatar,
        'department' => $internship->department,
        'position' => $internship->custom_position ?? $internship->job?->title ?? '-',
        'start_date' => $internship->start_date->format('d M Y'),
        'end_date' => $internship->end_date->format('d M Y'),
        'progress' => $internship->getProgressPercentage(),
        'can_evaluate' => $internship->getProgressPercentage() >= 90,
        'evaluation' => $evaluation ? [
          'id' => $evaluation->id,
          'status' => $evaluation->status,
          'overall_rating' => $evaluation->overall_rating,
          'average_rating' => $evaluation->getAverageRating(),
          'submitted_at' => $evaluation->submitted_at?->format('d M Y'),
        ] : null,
      ];
    });

    if ($status === 'pending') {
      $mentees = $mentees->filter(fn($m) => !$m['evaluation'] || $m['evaluation']['status'] === 'draft');
    } elseif ($status === 'completed') {
      $mentees = $mentees->filter(fn($m) => $m['evaluation'] && $m['evaluation']['status'] === 'submitted');
    }

    $allMentees = $menteesQuery->get();
    $stats = [
      'total' => $allMentees->count(),
      'pending' => $allMentees->filter(fn($m) => !$m->evaluation || $m->evaluation->status === 'draft')->count(),
      'completed' => $allMentees->filter(fn($m) => $m->evaluation && $m->evaluation->status === 'submitted')->count(),
    ];

    return Inertia::render('Mentor/Evaluation', [
      'mentees' => $mentees->values(),
      'stats' => $stats,
      'filters' => ['search' => $search, 'status' => $status],
    ]);
  }

  public function show(Request $request, $internshipId)
  {
    $mentor = $request->user();

    $internship = $mentor->mentees()->where('id', $internshipId)->with(['intern', 'job'])->firstOrFail();

    $evaluation = Evaluation::where('internship_id', $internshipId)
      ->where('mentor_id', $mentor->id)
      ->first();

    return response()->json([
      'internship' => [
        'id' => $internship->id,
        'intern_id' => $internship->intern_id,
        'intern_name' => $internship->intern?->name,
        'intern_email' => $internship->intern?->email,
        'department' => $internship->department,
        'position' => $internship->custom_position ?? $internship->job?->title ?? '-',
        'start_date' => $internship->start_date->format('d M Y'),
        'end_date' => $internship->end_date->format('d M Y'),
        'progress' => $internship->getProgressPercentage(),
      ],
      'evaluation' => $evaluation ? [
        'id' => $evaluation->id,
        'technical_skill' => $evaluation->technical_skill,
        'communication' => $evaluation->communication,
        'teamwork' => $evaluation->teamwork,
        'initiative' => $evaluation->initiative,
        'punctuality' => $evaluation->punctuality,
        'overall_rating' => $evaluation->overall_rating,
        'custom_criteria' => $evaluation->custom_criteria ?? [],
        'strengths' => $evaluation->strengths,
        'improvements' => $evaluation->improvements,
        'recommendation' => $evaluation->recommendation,
        'final_notes' => $evaluation->final_notes,
        'status' => $evaluation->status,
        'submitted_at' => $evaluation->submitted_at?->format('d M Y H:i'),
      ] : null,
    ]);
  }

  public function store(Request $request, $internshipId)
  {
    $mentor = $request->user();

    $internship = $mentor->mentees()->where('id', $internshipId)->with(['intern', 'job'])->firstOrFail();

    if ($internship->getProgressPercentage() < 90) {
      return response()->json([
        'success' => false,
        'message' => 'Evaluasi hanya bisa dilakukan saat progress magang sudah mencapai 90%.'
      ], 400);
    }

    $validated = $request->validate([
      'technical_skill' => 'required|integer|min:0|max:100',
      'communication' => 'required|integer|min:0|max:100',
      'teamwork' => 'required|integer|min:0|max:100',
      'initiative' => 'required|integer|min:0|max:100',
      'punctuality' => 'required|integer|min:0|max:100',
      'custom_criteria' => 'nullable|array',
      'custom_criteria.*.name' => 'required_with:custom_criteria|string|max:100',
      'custom_criteria.*.rating' => 'required_with:custom_criteria|integer|min:0|max:100',
      'strengths' => 'nullable|string|max:2000',
      'improvements' => 'nullable|string|max:2000',
      'recommendation' => 'nullable|string|max:2000',
      'final_notes' => 'nullable|string|max:2000',
      'submit' => 'boolean',
    ]);

    // Calculate overall_rating from average of all criteria
    $standardRatings = [
      $validated['technical_skill'],
      $validated['communication'],
      $validated['teamwork'],
      $validated['initiative'],
      $validated['punctuality'],
    ];

    $customRatings = [];
    if (!empty($validated['custom_criteria'])) {
      foreach ($validated['custom_criteria'] as $criteria) {
        $customRatings[] = $criteria['rating'];
      }
    }

    $allRatings = array_merge($standardRatings, $customRatings);
    $overallRating = round(array_sum($allRatings) / count($allRatings));

    $evaluation = Evaluation::updateOrCreate(
      ['internship_id' => $internshipId, 'mentor_id' => $mentor->id],
      [
        'intern_id' => $internship->intern_id,
        'technical_skill' => $validated['technical_skill'],
        'communication' => $validated['communication'],
        'teamwork' => $validated['teamwork'],
        'initiative' => $validated['initiative'],
        'punctuality' => $validated['punctuality'],
        'overall_rating' => $overallRating,
        'custom_criteria' => $validated['custom_criteria'] ?? null,
        'strengths' => $validated['strengths'] ?? null,
        'improvements' => $validated['improvements'] ?? null,
        'recommendation' => $validated['recommendation'] ?? null,
        'final_notes' => $validated['final_notes'] ?? null,
        'status' => $request->boolean('submit') ? 'submitted' : 'draft',
        'submitted_at' => $request->boolean('submit') ? now() : null,
      ]
    );

    if ($request->boolean('submit')) {
      $this->generateCertificate($evaluation, $internship);
    }

    return response()->json([
      'success' => true,
      'message' => $request->boolean('submit')
        ? 'Evaluasi berhasil dikirim! Sertifikat telah dibuat.'
        : 'Evaluasi berhasil disimpan sebagai draft.',
      'evaluation' => $evaluation,
    ]);
  }

  private function generateCertificate(Evaluation $evaluation, $internship)
  {
    $evaluation->load(['intern', 'mentor']);

    $pdf = Pdf::loadView('certificates.internship', [
      'evaluation' => $evaluation,
      'internship' => $internship,
    ])->setPaper('a4', 'landscape');

    $filename = 'certificates/' . $evaluation->id . '.pdf';
    Storage::disk('public')->put($filename, $pdf->output());

    $internship->update([
      'certificate_url' => '/storage/' . $filename,
      'status' => 'completed',
    ]);
  }

  public function previewCertificate(Request $request, $internshipId)
  {
    $mentor = $request->user();
    $internship = $mentor->mentees()->where('id', $internshipId)->with(['intern', 'job', 'evaluation'])->firstOrFail();

    if (!$internship->evaluation) {
      return response()->json(['error' => 'Evaluasi belum ada'], 404);
    }

    $evaluation = $internship->evaluation;
    $evaluation->load(['intern', 'mentor']);

    $pdf = Pdf::loadView('certificates.internship', [
      'evaluation' => $evaluation,
      'internship' => $internship,
    ])->setPaper('a4', 'landscape');

    return $pdf->stream('sertifikat-' . $evaluation->intern->name . '.pdf');
  }
}
