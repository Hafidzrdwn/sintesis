<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role') && $request->role !== 'all') {
            if ($request->role === 'candidate') {
                $query->where('role', 'intern')
                    ->whereDoesntHave('internshipsAsIntern', function ($q) {
                        $q->where('status', 'active');
                    });
            } elseif ($request->role === 'active_intern') {
                $query->where('role', 'intern')
                    ->whereHas('internshipsAsIntern', function ($q) {
                        $q->where('status', 'active');
                    });
            } else {
                $query->where('role', $request->role);
            }
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $query->with(['internshipsAsIntern' => function ($q) {
            $q->where('status', 'active')
              ->with(['job:id,title', 'mentor:id,name']);
        }]);

        $users = $query->paginate(10)->withQueryString();

        $users->getCollection()->transform(function ($user) {
            $user->has_active_internship = $user->role === 'intern' && $user->internshipsAsIntern->isNotEmpty();
            $user->intern_position = ($user->role === 'intern' && $user->internshipsAsIntern->isNotEmpty()) ? $user->internshipsAsIntern->first()->position : null;
            return $user;
        });

        $activeInternCount = User::where('role', 'intern')
            ->whereHas('internshipsAsIntern', function ($q) {
                $q->where('status', 'active');
            })
            ->count();
        
        $candidateCount = User::where('role', 'intern')->count() - $activeInternCount;

        $stats = [
            'total' => User::count(),
            'admin' => User::where('role', 'admin')->count(),
            'mentor' => User::where('role', 'mentor')->count(),
            'candidate' => $candidateCount,
            'active_intern' => $activeInternCount,
            'active' => User::where('status', 'active')->count()
        ];

        $roleOptions = [
            ['value' => 'admin', 'label' => 'Admin'],
            ['value' => 'mentor', 'label' => 'Mentor'],
            ['value' => 'candidate', 'label' => 'Kandidat'],
            ['value' => 'active_intern', 'label' => 'Intern Aktif'],
        ];

        $statusOptions = [
            ['value' => 'active', 'label' => 'Aktif'],
            ['value' => 'inactive', 'label' => 'Tidak Aktif'],
        ];

        $jobs = \App\Models\Job::select('id', 'title')
            ->orderBy('title')
            ->get();

        $mentors = User::where('role', 'mentor')
            ->where('status', 'active')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/UserManagement', [
            'users' => $users,
            'stats' => $stats,
            'roleOptions' => $roleOptions,
            'statusOptions' => $statusOptions,
            'jobs' => $jobs,
            'mentors' => $mentors,
            'filters' => $request->only(['search', 'role', 'status']),
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', Password::min(8)],
            'role' => ['required', 'in:admin,mentor,candidate,active_intern'],
            'status' => ['required', 'in:active,inactive'],
            'use_custom_position' => ['boolean'],
            'job_id' => ['nullable', 'required_if:use_custom_position,false,role,active_intern', 'exists:job_listings,id'],
            'custom_position' => ['nullable', 'required_if:use_custom_position,true', 'string', 'max:255'],
            'mentor_id' => ['nullable', 'required_if:role,active_intern', 'exists:users,id'],
            'start_date' => ['nullable', 'required_if:role,active_intern', 'date'],
            'end_date' => ['nullable', 'required_if:role,active_intern', 'date', 'after:start_date'],
        ];

        $messages = [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'username.max' => 'Username maksimal 50 karakter.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, dash, dan underscore.',
            'username.unique' => 'Username sudah digunakan.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role tidak valid.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status tidak valid.',
            'job_id.required_if' => 'Posisi wajib dipilih untuk Intern Aktif.',
            'job_id.exists' => 'Posisi tidak valid.',
            'custom_position.required_if' => 'Posisi custom wajib diisi.',
            'custom_position.max' => 'Posisi custom maksimal 255 karakter.',
            'mentor_id.required_if' => 'Mentor wajib dipilih untuk Intern Aktif.',
            'mentor_id.exists' => 'Mentor tidak valid.',
            'start_date.required_if' => 'Tanggal mulai wajib diisi untuk Intern Aktif.',
            'start_date.date' => 'Format tanggal mulai tidak valid.',
            'end_date.required_if' => 'Tanggal selesai wajib diisi untuk Intern Aktif.',
            'end_date.date' => 'Format tanggal selesai tidak valid.',
            'end_date.after' => 'Tanggal selesai harus setelah tanggal mulai.',
        ];

        $validated = $request->validate($rules, $messages);

        $userRole = in_array($validated['role'], ['candidate', 'active_intern']) ? 'intern' : $validated['role'];

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => $userRole,
            'status' => $validated['status'],
            'email_verified_at' => now(),
        ]);

        if ($validated['role'] === 'active_intern') {
            $useCustom = $validated['use_custom_position'] ?? false;
            \App\Models\Internship::create([
                'intern_id' => $user->id,
                'mentor_id' => $validated['mentor_id'],
                'job_id' => $useCustom ? null : ($validated['job_id'] ?? null),
                'custom_position' => $useCustom ? ($validated['custom_position'] ?? null) : null,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'status' => 'active',
            ]);
        }

        return back()->with('success', 'User berhasil ditambahkan.');
    }

    public function show(User $user)
    {
        return response()->json([
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', Password::min(8)],
            'role' => ['required', 'in:admin,mentor,candidate,active_intern'],
            'status' => ['required', 'in:active,inactive'],
            'use_custom_position' => ['boolean'],
            'job_id' => ['nullable', 'required_if:use_custom_position,false,role,active_intern', 'exists:job_listings,id'],
            'custom_position' => ['nullable', 'required_if:use_custom_position,true', 'string', 'max:255'],
            'mentor_id' => ['nullable', 'required_if:role,active_intern', 'exists:users,id'],
            'start_date' => ['nullable', 'required_if:role,active_intern', 'date'],
            'end_date' => ['nullable', 'required_if:role,active_intern', 'date', 'after:start_date'],
        ];

        $messages = [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'username.max' => 'Username maksimal 50 karakter.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, dash, dan underscore.',
            'username.unique' => 'Username sudah digunakan.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.min' => 'Password minimal 8 karakter.',
            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role tidak valid.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status tidak valid.',
            'job_id.required_if' => 'Posisi wajib dipilih untuk Intern Aktif.',
            'job_id.exists' => 'Posisi tidak valid.',
            'custom_position.required_if' => 'Posisi custom wajib diisi.',
            'custom_position.max' => 'Posisi custom maksimal 255 karakter.',
            'mentor_id.required_if' => 'Mentor wajib dipilih untuk Intern Aktif.',
            'mentor_id.exists' => 'Mentor tidak valid.',
            'start_date.required_if' => 'Tanggal mulai wajib diisi untuk Intern Aktif.',
            'start_date.date' => 'Format tanggal mulai tidak valid.',
            'end_date.required_if' => 'Tanggal selesai wajib diisi untuk Intern Aktif.',
            'end_date.date' => 'Format tanggal selesai tidak valid.',
            'end_date.after' => 'Tanggal selesai harus setelah tanggal mulai.',
        ];

        $validated = $request->validate($rules, $messages);

        $userRole = in_array($validated['role'], ['candidate', 'active_intern']) ? 'intern' : $validated['role'];

        $updateData = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => $userRole,
            'status' => $validated['status'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        // Handle internship based on role
        if ($validated['role'] === 'active_intern') {
            $useCustom = $validated['use_custom_position'] ?? false;
            $activeInternship = $user->internshipsAsIntern()->where('status', 'active')->first();
            
            $internshipData = [
                'mentor_id' => $validated['mentor_id'],
                'job_id' => $useCustom ? null : ($validated['job_id'] ?? null),
                'custom_position' => $useCustom ? ($validated['custom_position'] ?? null) : null,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
            ];
            
            if ($activeInternship) {
                $activeInternship->update($internshipData);
            } else {
                \App\Models\Internship::create(array_merge($internshipData, [
                    'intern_id' => $user->id,
                    'status' => 'active',
                ]));
            }
        } elseif ($validated['role'] === 'candidate') {
            // If switching to candidate, deactivate active internships
            $user->internshipsAsIntern()->where('status', 'active')->update(['status' => 'completed']);
        }

        return back()->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        // Delete avatar file if exists (and not external URL like Google avatar)
        if ($user->avatar && !str_starts_with($user->avatar, 'http')) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

        return back()->with('success', 'User berhasil dihapus.');
    }
}

