<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InstitutionController extends Controller
{
  /**
   * Get all institutions for select dropdown
   */
  public function index(): JsonResponse
  {
    $institutions = Institution::orderBy('name')
      ->get(['id', 'name']);

    return response()->json($institutions);
  }

  /**
   * Store a new institution (for dynamic creation)
   */
  public function store(Request $request): JsonResponse
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255|unique:institutions,name',
    ], [
      'name.required' => 'Nama institusi wajib diisi.',
      'name.unique' => 'Institusi ini sudah terdaftar.',
    ]);

    $institution = Institution::create([
      'name' => $validated['name'],
    ]);

    return response()->json([
      'id' => $institution->id,
      'name' => $institution->name,
    ], 201);
  }
}
