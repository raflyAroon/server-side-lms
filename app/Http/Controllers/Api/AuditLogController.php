<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('admin');
        $logs = AuditLog::with('user')->orderBy('created_at', 'desc')->paginate(20);
        return response()->json($logs);
    }

    public function getByEntity($entityType, $entityId)
    {
        $this->authorize('admin');
        $logs = AuditLog::where('entity_type', $entityType)
                        ->where('entity_id', $entityId)
                        ->orderBy('created_at', 'desc')
                        ->get();
        return response()->json($logs);
    }
}