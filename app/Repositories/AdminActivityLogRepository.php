<?php

namespace App\Repositories;

use App\Models\AdminActivityLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class AdminActivityLogRepository
{
    public function log(?int $adminId, string $actionType, ?string $description, ?string $ipAddress, ?string $userAgent): AdminActivityLog
    {
        return AdminActivityLog::create([
            'admin_id' => $adminId,
            'action_type' => $actionType,
            'description' => $description,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    public function getLastDaysLogs(?int $adminId, int $days = 30, int $limit = 100): Collection
    {
        $since = Carbon::now()->subDays($days);

        $query = AdminActivityLog::query()
            ->where('created_at', '>=', $since)
            ->orderByDesc('created_at')
            ->limit($limit);

        if ($adminId !== null) {
            $query->where('admin_id', $adminId);
        }

        return $query->get();
    }

    /**
     * Suspicious activity = the admin used multiple distinct IPs in the last N days.
     */
    public function getSuspiciousAdmins(int $days = 30, int $minDistinctIps = 3, int $limit = 50): Collection
    {
        $since = Carbon::now()->subDays($days);

        return AdminActivityLog::query()
            ->selectRaw('admin_id, COUNT(DISTINCT ip_address) as distinct_ips')
            ->whereNotNull('admin_id')
            ->where('created_at', '>=', $since)
            ->groupBy('admin_id')
            ->havingRaw('COUNT(DISTINCT ip_address) >= ?', [$minDistinctIps])
            ->orderByDesc('distinct_ips')
            ->limit($limit)
            ->get();
    }
}

