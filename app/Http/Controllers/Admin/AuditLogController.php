<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditLogController extends Controller
{
  public function index(Request $request)
  {
    $query = AuditLog::with('user:id,name,email')
      ->orderBy('created_at', 'desc');

    // Filter by action
    if ($request->filled('action')) {
      $query->action($request->action);
    }

    // Filter by user
    if ($request->filled('user_id')) {
      $query->byUser($request->user_id);
    }

    // Filter by model type
    if ($request->filled('model_type')) {
      $query->where('auditable_type', 'like', '%' . $request->model_type . '%');
    }

    // Filter by date range
    if ($request->filled('start_date') && $request->filled('end_date')) {
      $query->dateRange($request->start_date, $request->end_date);
    }

    // Search by IP address or URL
    if ($request->filled('search')) {
      $search = $request->search;
      $query->where(function ($q) use ($search) {
        $q->where('ip_address', 'like', "%{$search}%")
          ->orWhere('url', 'like', "%{$search}%")
          ->orWhereHas('user', function ($userQuery) use ($search) {
            $userQuery->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
          });
      });
    }

    $logs = $query->paginate(20)->withQueryString();

    // Transform logs for frontend
    $logs->getCollection()->transform(function ($log) {
      return [
        'id' => $log->id,
        'user' => $log->user ? [
          'id' => $log->user->id,
          'name' => $log->user->name,
          'email' => $log->user->email,
        ] : null,
        'action' => $log->action,
        'action_label' => $log->getActionLabel(),
        'model_type' => $log->getModelTypeName(),
        'model_id' => $log->auditable_id,
        'old_values' => $log->old_values,
        'new_values' => $log->new_values,
        'changes' => $log->getChanges(),
        'ip_address' => $log->ip_address,
        'user_agent' => $log->user_agent,
        'url' => $log->url,
        'method' => $log->method,
        'created_at' => $log->created_at->format('Y-m-d H:i:s'),
        'created_at_human' => $log->created_at->diffForHumans(),
      ];
    });

    // Get filter options
    $users = User::select('id', 'name', 'email')
      ->whereIn('id', AuditLog::distinct()->pluck('user_id'))
      ->orderBy('name')
      ->get();

    $modelTypes = AuditLog::distinct()
      ->pluck('auditable_type')
      ->filter()
      ->map(fn($type) => [
        'value' => $type,
        'label' => class_basename($type),
      ])
      ->values();

    return Inertia::render('Admin/AuditLog', [
      'logs' => $logs,
      'filters' => $request->only(['action', 'user_id', 'model_type', 'start_date', 'end_date', 'search']),
      'availableActions' => AuditLog::availableActions(),
      'users' => $users,
      'modelTypes' => $modelTypes,
    ]);
  }
}
