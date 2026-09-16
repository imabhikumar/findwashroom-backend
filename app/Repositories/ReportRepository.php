<?php

namespace App\Repositories;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportRepository
{
    public function bookings(array $range): array
    {
        $query = $this->between('bookings', 'created_at', $range);
        $amount = 'COALESCE(total_amount, amount, 0)';

        return [
            'total' => (clone $query)->count(),
            'by_status' => $this->counts((clone $query), 'status'),
            'by_type' => $this->counts((clone $query), 'booking_type'),
            'trend' => $this->trendBookings($range, $amount),
            'top_properties' => DB::table('bookings')
                ->join('properties', 'properties.id', '=', 'bookings.property_id')
                ->whereBetween('bookings.created_at', [$range['from'], $range['to']])
                ->select('properties.id', 'properties.name')
                ->selectRaw('COUNT(bookings.id) as bookings')
                ->selectRaw("SUM({$amount}) as revenue")
                ->groupBy('properties.id', 'properties.name')
                ->orderByDesc('bookings')
                ->limit(10)
                ->get()
                ->map(fn ($row) => (array) $row)
                ->all(),
        ];
    }

    public function revenue(array $range): array
    {
        $payments = $this->between('payments', 'created_at', $range);
        $refunds = DB::table('wallet_transactions')
            ->where('transaction_type', 'refund')
            ->whereBetween('created_at', [$range['from'], $range['to']]);

        return [
            'gross' => (float) (clone $payments)->where('status', 'success')->sum('amount'),
            'commission' => (float) (clone $payments)->where('status', 'success')->sum('platform_commission'),
            'refunds' => (float) $refunds->sum('amount'),
            'net' => (float) (clone $payments)->where('status', 'success')->sum('amount') - (float) $refunds->sum('amount'),
            'by_gateway' => (clone $payments)->where('status', 'success')->selectRaw('COALESCE(gateway, payment_gateway) as gateway, SUM(amount) as gross')->groupByRaw('COALESCE(gateway, payment_gateway)')->pluck('gross', 'gateway'),
            'trend' => $this->trendRevenue($range),
        ];
    }

    public function complaints(array $range): array
    {
        $query = $this->between('complaints', 'created_at', $range);

        return [
            'total' => (clone $query)->count(),
            'by_status' => $this->counts((clone $query), 'status'),
            'by_category' => $this->counts((clone $query), 'category'),
            'by_priority' => $this->counts((clone $query), 'priority'),
            'avg_resolution_hours' => (float) ((clone $query)->whereNotNull('resolved_at')->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as average')->value('average') ?? 0),
            'trend' => $this->trendComplaints($range),
        ];
    }

    public function trust(array $range): array
    {
        if (! Schema::hasTable('user_trust_scores')) {
            return ['users_by_score_bucket' => [], 'top_trusted' => [], 'events_summary' => [], 'note' => 'Trust score table is unavailable.'];
        }

        $bucket = DB::table('user_trust_scores')->selectRaw("CASE WHEN score <= 20 THEN '0-20' WHEN score <= 40 THEN '21-40' WHEN score <= 60 THEN '41-60' WHEN score <= 80 THEN '61-80' ELSE '81-100' END as bucket, COUNT(*) as count")->groupBy('bucket')->pluck('count', 'bucket');
        $top = DB::table('user_trust_scores')->join('users', 'users.id', '=', 'user_trust_scores.user_id')->orderByDesc('user_trust_scores.score')->limit(10)->get(['users.id', 'users.name', 'user_trust_scores.score'])->map(fn ($row) => (array) $row)->all();
        $events = Schema::hasTable('trust_events') ? DB::table('trust_events')->whereBetween('occurred_at', [$range['from'], $range['to']])->select('event_type')->selectRaw('COUNT(*) as count')->groupBy('event_type')->pluck('count', 'event_type') : collect();

        return ['users_by_score_bucket' => $bucket, 'top_trusted' => $top, 'events_summary' => $events];
    }

    public function safety(array $range): array
    {
        if (! Schema::hasTable('sos_alerts') && ! Schema::hasTable('incidents')) {
            return ['sos_total' => 0, 'sos_by_status' => [], 'incidents_by_severity' => [], 'trend' => [], 'note' => 'Safety alert and incident tables are unavailable.'];
        }

        return ['sos_total' => 0, 'sos_by_status' => [], 'incidents_by_severity' => [], 'trend' => [], 'note' => 'Safety reporting is partially unavailable.'];
    }

    public function exportRows(string $type, array $range): array
    {
        $query = match ($type) {
            'bookings' => DB::table('bookings')->whereBetween('created_at', [$range['from'], $range['to']])->select('id', 'booking_number', 'status', 'booking_type', 'total_amount', 'created_at'),
            'revenue' => DB::table('payments')->whereBetween('created_at', [$range['from'], $range['to']])->select('id', DB::raw('COALESCE(gateway, payment_gateway) as gateway'), 'amount', 'platform_commission', 'status', 'created_at'),
            default => DB::table('complaints')->whereBetween('created_at', [$range['from'], $range['to']])->select('id', 'complaint_number', 'category', 'priority', 'status', 'created_at'),
        };

        return $query->orderBy('created_at')->get()->all();
    }

    private function between(string $table, string $column, array $range): Builder
    {
        return DB::table($table)->whereBetween($column, [$range['from'], $range['to']]);
    }

    private function counts(Builder $query, string $column): array
    {
        return $query->select($column)->selectRaw('COUNT(*) as count')->groupBy($column)->pluck('count', $column)->all();
    }

    private function expression(string $column, string $group): string
    {
        return match ($group) {
            'week' => "DATE_FORMAT({$column}, '%x-W%v')",
            'month' => "DATE_FORMAT({$column}, '%Y-%m')",
            default => "DATE_FORMAT({$column}, '%Y-%m-%d')",
        };
    }

    private function trendBookings(array $range, string $amount): array
    {
        $date = $this->expression('created_at', $range['group_by']);
        return DB::table('bookings')->whereBetween('created_at', [$range['from'], $range['to']])->selectRaw("{$date} as date, COUNT(*) as count, SUM({$amount}) as amount")->groupBy('date')->orderBy('date')->get()->map(fn ($row) => (array) $row)->all();
    }

    private function trendRevenue(array $range): array
    {
        $date = $this->expression('created_at', $range['group_by']);
        $refundDate = $this->expression('wallet_transactions.created_at', $range['group_by']);
        $payments = DB::table('payments')->whereBetween('payments.created_at', [$range['from'], $range['to']])->where('payments.status', 'success')->selectRaw("{$date} as date, SUM(amount) as gross, SUM(platform_commission) as commission")->groupBy('date')->get()->keyBy('date');
        $refunds = DB::table('wallet_transactions')->where('transaction_type', 'refund')->whereBetween('created_at', [$range['from'], $range['to']])->selectRaw("{$refundDate} as date, SUM(amount) as refunds")->groupBy('date')->pluck('refunds', 'date');
        return $payments->map(function ($row, $date) use ($refunds) { $result = (array) $row; $result['refunds'] = (float) ($refunds[$date] ?? 0); return $result; })->values()->all();
    }

    private function trendComplaints(array $range): array
    {
        $date = $this->expression('created_at', $range['group_by']);
        return DB::table('complaints')->whereBetween('created_at', [$range['from'], $range['to']])->selectRaw("{$date} as date, COUNT(*) as raised, SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved")->groupBy('date')->orderBy('date')->get()->map(fn ($row) => (array) $row)->all();
    }
}