<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\UsageLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LogsController extends Controller
{
    /**
     * Get usage logs.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tenant = Tenant::find($user->tenant_id);

        if (!$tenant) {
            return response()->json([]);
        }

        $perPage = $request->get('per_page', 50);
        $page = $request->get('page', 1);
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $apiKeyId = $request->get('api_key_id');

        $query = UsageLog::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->with('apiKey:id,name,litellm_key_id');

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        if ($apiKeyId) {
            $query->where('api_key_id', $apiKeyId);
        }

        $logs = $query->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json($logs);
    }

    /**
     * Get log statistics.
     */
    public function stats(Request $request)
    {
        $user = Auth::user();
        $tenant = Tenant::find($user->tenant_id);

        if (!$tenant) {
            return response()->json([]);
        }

        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $stats = UsageLog::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('COUNT(*) as total_requests'),
                DB::raw('SUM(cost) as total_cost'),
                DB::raw('SUM(tokens_used) as total_tokens'),
                DB::raw('AVG(response_time) as avg_response_time')
            )
            ->first();

        return response()->json([
            'total_requests' => (int) ($stats->total_requests ?? 0),
            'total_cost' => (float) ($stats->total_cost ?? 0),
            'total_tokens' => (int) ($stats->total_tokens ?? 0),
            'avg_response_time' => (float) ($stats->avg_response_time ?? 0),
        ]);
    }
}

