<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\Tenant;
use App\Models\UsageLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics.
     */
    public function stats(Request $request)
    {
        $user = Auth::user();
        $tenant = Tenant::find($user->tenant_id);

        if (!$tenant) {
            return response()->json([
                'totalRequests' => 0,
                'totalCost' => 0,
                'activeKeys' => 0,
                'usagePercentage' => 0,
            ]);
        }

        // Bu ayın başlangıç ve bitiş tarihleri
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        // Bu ay toplam API çağrıları
        $totalRequests = UsageLog::where('tenant_id', $tenant->id)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        // Bu ay toplam maliyet
        $totalCost = UsageLog::where('tenant_id', $tenant->id)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('cost');

        // Aktif API key sayısı
        $activeKeys = ApiKey::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->count();

        // Kullanım yüzdesi (plan limit'e göre)
        $usagePercentage = 0;
        $subscription = $tenant->activeSubscription;
        
        if ($subscription) {
            // Plan limit'ini settings'ten veya plan_id'den al
            $planId = $subscription->plan_id;
            $settings = $tenant->settings ?? [];
            
            // Plan limit'lerini tanımla (örnek değerler)
            $planLimits = [
                'free' => 1000,
                'starter' => 10000,
                'pro' => 100000,
                'enterprise' => 1000000,
            ];
            
            $planLimit = $settings['monthly_request_limit'] ?? $planLimits[$planId] ?? 0;
            
            if ($planLimit > 0) {
                $usagePercentage = min(100, round(($totalRequests / $planLimit) * 100, 2));
            }
        }

        return response()->json([
            'totalRequests' => $totalRequests,
            'totalCost' => (float) $totalCost,
            'activeKeys' => $activeKeys,
            'usagePercentage' => $usagePercentage,
        ]);
    }

    /**
     * Get usage analytics.
     */
    public function usage(Request $request)
    {
        $user = Auth::user();
        $tenant = Tenant::find($user->tenant_id);

        if (!$tenant) {
            return response()->json([]);
        }

        // Son 30 günün günlük kullanım verileri
        $startDate = now()->subDays(30);
        
        $usage = UsageLog::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as requests'),
                DB::raw('SUM(cost) as cost')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($usage);
    }

    /**
     * Get analytics data.
     */
    public function analytics(Request $request)
    {
        $user = Auth::user();
        $tenant = Tenant::find($user->tenant_id);

        if (!$tenant) {
            return response()->json([]);
        }

        // Bu ay en çok kullanılan endpoint'ler
        $topEndpoints = UsageLog::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', now()->startOfMonth())
            ->select(
                'endpoint',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(cost) as total_cost')
            )
            ->groupBy('endpoint')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Bu ay en çok kullanılan modeller
        $topModels = UsageLog::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', now()->startOfMonth())
            ->whereNotNull('metadata->model')
            ->select(
                DB::raw("JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.model')) as model"),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(cost) as total_cost')
            )
            ->groupBy('model')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return response()->json([
            'topEndpoints' => $topEndpoints,
            'topModels' => $topModels,
        ]);
    }
}

