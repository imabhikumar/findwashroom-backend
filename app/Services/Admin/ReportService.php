<?php

namespace App\Services\Admin;

use App\Repositories\ReportRepository;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportService
{
    public function __construct(private readonly ReportRepository $repository)
    {
    }

    public function bookings(array $filters): array
    {
        return $this->repository->bookings($this->range($filters));
    }

    public function revenue(array $filters): array
    {
        return $this->repository->revenue($this->range($filters));
    }

    public function complaints(array $filters): array
    {
        return $this->repository->complaints($this->range($filters));
    }

    public function trust(array $filters): array
    {
        return $this->repository->trust($this->range($filters));
    }

    public function safety(array $filters): array
    {
        return $this->repository->safety($this->range($filters));
    }

    public function export(string $type, array $filters): StreamedResponse
    {
        $range = $this->range($filters);
        $rows = $this->repository->exportRows($type, $range);
        $filename = "{$type}-report-" . $range['from']->format('Ymd') . '-' . $range['to']->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            if ($rows) {
                fputcsv($handle, array_keys((array) $rows[0]));
                foreach ($rows as $row) {
                    fputcsv($handle, (array) $row);
                }
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function range(array $filters): array
    {
        $to = isset($filters['to_date'])
            ? Carbon::parse($filters['to_date'])->endOfDay()
            : now()->endOfDay();
        $from = isset($filters['from_date'])
            ? Carbon::parse($filters['from_date'])->startOfDay()
            : $to->copy()->subDays(29)->startOfDay();

        return [
            'from' => $from,
            'to' => $to,
            'group_by' => $filters['group_by'] ?? 'day',
        ];
    }
}