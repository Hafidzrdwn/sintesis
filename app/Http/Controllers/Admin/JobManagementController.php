<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class JobManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Job::withCount('applicants');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        $jobs = $query->orderByDesc('created_at')->paginate(10)->withQueryString();

        $stats = [
            'total' => Job::count(),
            'open' => Job::where('status', 'open')->count(),
            'closed' => Job::where('status', 'closed')->count(),
            'applicants' => Job::withCount('applicants')->get()->sum('applicants_count'),
        ];

        $statusOptions = [
            ['value' => 'open', 'label' => 'Terbuka'],
            ['value' => 'closed', 'label' => 'Ditutup'],
        ];

        $typeOptions = [
            ['value' => 'Remote', 'label' => 'Remote'],
            ['value' => 'Hybrid', 'label' => 'Hybrid'],
            ['value' => 'On-site', 'label' => 'On-site'],
        ];

        return Inertia::render('Admin/JobManagement', [
            'jobs' => $jobs,
            'stats' => $stats,
            'statusOptions' => $statusOptions,
            'typeOptions' => $typeOptions,
            'filters' => $request->only(['search', 'status', 'type']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255', 'unique:job_listings,title'],
            'type' => ['required', Rule::in(['Remote', 'Hybrid', 'On-site'])],
            'status' => ['required', Rule::in(['open', 'closed'])],
            'location' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'responsibilities' => ['nullable', 'array'],
            'requirements' => ['nullable', 'array'],
            'benefits' => ['nullable', 'array'],
            'duration' => ['nullable', 'string', 'max:100'],
            'deadline' => ['nullable', 'date'],
            'image' => ['nullable', 'image', 'max:2048'],
        ], [
            'title.required' => 'Judul lowongan wajib diisi.',
            'title.max' => 'Judul maksimal 255 karakter.',
            'title.unique' => 'Judul lowongan sudah digunakan.',
            'type.required' => 'Tipe pekerjaan wajib dipilih.',
            'type.in' => 'Tipe pekerjaan tidak valid.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status tidak valid.',
            'location.required' => 'Lokasi wajib diisi.',
            'location.max' => 'Lokasi maksimal 255 karakter.',
            'description.required' => 'Deskripsi wajib diisi.',
            'duration.max' => 'Durasi maksimal 100 karakter.',
            'deadline.date' => 'Format deadline tidak valid.',
            'image.image' => 'File harus berupa gambar (jpg, png, gif, dll).',
            'image.max' => 'Ukuran gambar maksimal 2MB.',
        ]);

        $validated['slug'] = Str::slug($validated['title']);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('jobs', 'public');
        }

        Job::create($validated);

        return back()->with('success', 'Lowongan berhasil ditambahkan.');
    }

    public function update(Request $request, Job $job)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255', Rule::unique('job_listings', 'title')->ignore($job->id)],
            'type' => ['required', Rule::in(['Remote', 'Hybrid', 'On-site'])],
            'status' => ['required', Rule::in(['open', 'closed'])],
            'location' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'responsibilities' => ['nullable', 'array'],
            'requirements' => ['nullable', 'array'],
            'benefits' => ['nullable', 'array'],
            'duration' => ['nullable', 'string', 'max:100'],
            'deadline' => ['nullable', 'date'],
            'image' => ['nullable', 'image', 'max:2048'],
        ], [
            'title.required' => 'Judul lowongan wajib diisi.',
            'title.max' => 'Judul maksimal 255 karakter.',
            'title.unique' => 'Judul lowongan sudah digunakan.',
            'type.required' => 'Tipe pekerjaan wajib dipilih.',
            'type.in' => 'Tipe pekerjaan tidak valid.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status tidak valid.',
            'location.required' => 'Lokasi wajib diisi.',
            'location.max' => 'Lokasi maksimal 255 karakter.',
            'description.required' => 'Deskripsi wajib diisi.',
            'duration.max' => 'Durasi maksimal 100 karakter.',
            'deadline.date' => 'Format deadline tidak valid.',
            'image.image' => 'File harus berupa gambar (jpg, png, gif, dll).',
            'image.max' => 'Ukuran gambar maksimal 2MB.',
        ]);

        if ($validated['title'] !== $job->title) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        if ($request->hasFile('image')) {
            if ($job->image) {
                Storage::disk('public')->delete($job->image);
            }
            $validated['image'] = $request->file('image')->store('jobs', 'public');
        }

        $job->update($validated);

        return back()->with('success', 'Lowongan berhasil diperbarui.');
    }

    public function updateStatus(Request $request, Job $job)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['open', 'closed'])],
        ]);

        $job->update(['status' => $validated['status']]);

        $label = $validated['status'] === 'open' ? 'dibuka' : 'ditutup';
        return back()->with('success', "Lowongan berhasil {$label}.");
    }

    public function destroy(Job $job)
    {
        if ($job->image) {
            Storage::disk('public')->delete($job->image);
        }

        $job->delete();

        return back()->with('success', 'Lowongan berhasil dihapus.');
    }
}
