<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Evaluation extends Model
{
  use HasFactory, HasUuids, SoftDeletes, Auditable;

  protected $fillable = [
    'internship_id',
    'intern_id',
    'mentor_id',
    'technical_skill',
    'communication',
    'teamwork',
    'initiative',
    'punctuality',
    'overall_rating',
    'custom_criteria',
    'strengths',
    'improvements',
    'recommendation',
    'final_notes',
    'status',
    'submitted_at',
  ];

  protected function casts(): array
  {
    return [
      'submitted_at' => 'datetime',
      'technical_skill' => 'integer',
      'communication' => 'integer',
      'teamwork' => 'integer',
      'initiative' => 'integer',
      'punctuality' => 'integer',
      'overall_rating' => 'integer',
      'custom_criteria' => 'array',
    ];
  }

  public function isDraft(): bool
  {
    return $this->status === 'draft';
  }

  public function isSubmitted(): bool
  {
    return $this->status === 'submitted';
  }

  public function submit(): void
  {
    $this->update([
      'status' => 'submitted',
      'submitted_at' => now(),
    ]);
  }

  public function getAverageRating(): float
  {
    $ratings = [
      $this->technical_skill,
      $this->communication,
      $this->teamwork,
      $this->initiative,
      $this->punctuality,
    ];

    $validRatings = array_filter($ratings, fn($r) => $r > 0);
    return count($validRatings) > 0 ? round(array_sum($validRatings) / count($validRatings), 1) : 0;
  }

  public function getGradeLabel(): string
  {
    $avg = $this->overall_rating ?: $this->getAverageRating();
    if ($avg >= 90) return 'Sangat Baik';
    if ($avg >= 80) return 'Baik';
    if ($avg >= 70) return 'Cukup';
    if ($avg >= 60) return 'Kurang';
    return 'Sangat Kurang';
  }

  // Relationships
  public function internship(): BelongsTo
  {
    return $this->belongsTo(Internship::class);
  }

  public function intern(): BelongsTo
  {
    return $this->belongsTo(User::class, 'intern_id');
  }

  public function mentor(): BelongsTo
  {
    return $this->belongsTo(User::class, 'mentor_id');
  }
}
