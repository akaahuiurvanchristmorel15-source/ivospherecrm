<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Domain;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the activity logs.
     */
    public function index(Request $request): View
    {
        $query = ActivityLog::with(['user', 'domain'])->latest('created_at');

        if ($domainId = $request->input('domain_id')) {
            $query->where('domain_id', $domainId);
        }

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($action = $request->input('action')) {
            $query->where('action', 'like', "%{$action}%");
        }

        if ($search = $request->input('search')) {
            $query->where('description', 'like', "%{$search}%");
        }

        if ($date = $request->input('date')) {
            $query->whereDate('created_at', $date);
        }

        $logs = $query->paginate(20)->withQueryString();
        $domains = Domain::all();
        $users = User::all();

        return view('admin.activity_logs.index', compact('logs', 'domains', 'users'));
    }
}
