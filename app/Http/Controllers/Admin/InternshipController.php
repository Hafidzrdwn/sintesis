<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Internship;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class InternshipController extends Controller
{
    public function index(Request $request)
    {
        Internship::whereIn('status', ['active', 'extended'])
            ->where('end_date', '<', now()->toDateString())
            ->update(['status' => 'completed']);

        $query = Internship::with(['intern:id,name,email,avatar', 'mentor:id,name', 'job:id,title']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('intern', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('mentor_id')) {
            $query->where('mentor_id', $request->mentor_id);
        }

        if ($request->filled('period_start')) {
            $query->where('start_date', '>=', $request->period_start);
        }
        if ($request->filled('period_end')) {
            $query->where('end_date', '<=', $request->period_end);
        }

        $internships = $query->orderByDesc('created_at')->paginate(10)->withQueryString();

        $stats = [
            'total' => Internship::count(),
            'active' => Internship::where('status', 'active')->count(),
            'extended' => Internship::where('status', 'extended')->count(),
            'completed' => Internship::where('status', 'completed')->count(),
            'terminated' => Internship::where('status', 'terminated')->count(),
        ];

        $statusOptions = [
            ['value' => 'active', 'label' => 'Aktif'],
            ['value' => 'extended', 'label' => 'Diperpanjang'],
            ['value' => 'completed', 'label' => 'Selesai'],
            ['value' => 'terminated', 'label' => 'Diberhentikan'],
        ];

        $mentors = User::where('role', 'mentor')
            ->where('status', 'active')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();


        return Inertia::render('Admin/InternshipManagement', [
            'internships' => $internships,
            'stats' => $stats,
            'statusOptions' => $statusOptions,
            'mentors' => $mentors,
            'filters' => $request->only(['search', 'status', 'mentor_id', 'period_start', 'period_end']),
        ]);
    }

    public function updateStatus(Request $request, Internship $internship)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['completed', 'extended', 'terminated'])],
            'end_date' => ['nullable', 'required_if:status,extended', 'date', 'after:' . $internship->end_date->toDateString()],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status tidak valid.',
            'end_date.required_if' => 'Tanggal selesai baru wajib diisi untuk perpanjangan.',
            'end_date.date' => 'Format tanggal tidak valid.',
            'end_date.after' => 'Tanggal perpanjangan harus setelah tanggal selesai saat ini (' . $internship->end_date->format('d M Y') . ').',
            'notes.max' => 'Catatan maksimal 1000 karakter.',
        ]);

        $updateData = [
            'status' => $validated['status'],
        ];

        if ($validated['status'] === 'extended' && !empty($validated['end_date'])) {
            $updateData['end_date'] = $validated['end_date'];
        }

        if (in_array($validated['status'], ['completed', 'terminated'])) {
            $updateData['end_date'] = now()->toDateString();
        }

        if (!empty($validated['notes'])) {
            $existingNotes = $internship->notes ?? '';
            $newNote = '[' . now()->format('Y-m-d H:i') . '] ' . $validated['notes'];
            $updateData['notes'] = $existingNotes ? $existingNotes . "\n" . $newNote : $newNote;
        }

        $internship->update($updateData);

        $statusLabels = [
            'completed' => 'diselesaikan',
            'extended' => 'diperpanjang',
            'terminated' => 'diberhentikan',
        ];

        return back()->with('success', 'Internship berhasil ' . $statusLabels[$validated['status']] . '.');
    }

    public function updateMentor(Request $request, Internship $internship)
    {
        $validated = $request->validate([
            'mentor_id' => ['required', 'exists:users,id'],
        ], [
            'mentor_id.required' => 'Mentor wajib dipilih.',
            'mentor_id.exists' => 'Mentor tidak valid.',
        ]);

        $internship->update([
            'mentor_id' => $validated['mentor_id'],
        ]);

        return back()->with('success', 'Mentor berhasil diubah.');
    }
}
