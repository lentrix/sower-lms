<?php

namespace App\Http\Controllers;

use App\Models\SystemLog;

class SystemLogController extends Controller
{
    public function index() {

        $logs = SystemLog::with('user')
            ->orderBy('created_at','DESC')
            ->orderBy('id','DESC')
            ->paginate(50)
            ->withQueryString()
            ->through(function($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'description' => $log->description,
                    'properties' => $log->properties,
                    'user' => $log->user?->full_name ?? 'System',
                    'created_at' => $log->created_at->format('M d, Y h:i A'),
                ];
            });

        return inertia('SystemLogs/Index',[
            'logs' => $logs
        ]);
    }
}
