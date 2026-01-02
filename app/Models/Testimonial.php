<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Testimonial extends Model
{
  use HasFactory, HasUuids;

  protected $fillable = [
    'user_id',
    'internship_id',
    'content',
    'rating',
    'position',
    'status',
  ];

  protected $casts = [
    'rating' => 'integer',
  ];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function internship(): BelongsTo
  {
    return $this->belongsTo(Internship::class);
  }

  public function scopeApproved($query)
  {
    return $query->where('status', 'approved');
  }
}
