<?php

namespace App\Repositories;

use App\Models\Booking;
use App\Models\Complaint;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;

class AdminDashboardRepository
{
    public function getDashboardStats(): array
    {
        $today = Carbon::today();

        return [
            'total_active_customers' => User::query()
                ->where('role', 'customer')
                ->where('status', 'active')
                ->count(),

            'total_cleaners' => User::query()
                ->where('role', 'cleaner')
                ->where('status', 'active')
                ->count(),

            'total_active_bookings' => Booking::query()
                ->where('status', 'active')
                ->count(),

            'total_revenue_today' => Payment::query()
                ->where('status', 'success')
                ->whereDate('created_at', $today)
                ->sum('amount'),

            'total_active_complaints' => Complaint::query()
                ->where('status', 'pending')
                ->count(),
        ];
    }
}

