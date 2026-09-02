<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Dispute;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\WorkerDocument;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalClients = User::where('role', 'client')->count();
        $totalWorkers = User::where('role', 'worker')->count();
        $suspendedUsers = User::suspended()->count();

        $totalBookings = Booking::count();
        $activeBookings = Booking::whereNotIn('status', ['completed', 'cancelled'])->count();
        $completedBookings = Booking::completed()->count();
        $cancelledBookings = Booking::cancelled()->count();
        $pendingVerifications = WorkerDocument::where('status', 'pending')->count();
        $openDisputes = Dispute::open()->count();
        $pendingTestimonials = Testimonial::pending()->count();

        $totalRevenue = Booking::completed()->sum('price');
        $revenueThisMonth = Booking::completed()
            ->whereBetween('completed_at', [now()->startOfMonth(), now()])
            ->sum('price');
        $avgBookingValue = Booking::completed()->avg('price');

        $completionRate = $totalBookings > 0 ? round(($completedBookings / $totalBookings) * 100) : 0;

        $recentBookings = Booking::with(['client', 'worker'])->latest()->take(5)->get();
        $bookingTrend = $this->bookingTrend();
        $bookingStatusDist = $this->bookingStatusDist();
        $topCategories = $this->topCategories();

        return view('admin.dashboard', compact(
            'totalUsers', 'totalClients', 'totalWorkers', 'suspendedUsers',
            'totalBookings', 'activeBookings', 'completedBookings', 'cancelledBookings',
            'pendingVerifications', 'openDisputes', 'pendingTestimonials',
            'totalRevenue', 'revenueThisMonth', 'avgBookingValue',
            'completionRate',
            'recentBookings', 'bookingTrend', 'bookingStatusDist', 'topCategories'
        ));
    }

    private function bookingStatusDist(): array
    {
        $map = [
            'new'         => ['label' => 'New',         'color' => '#4B5563'],
            'accepted'    => ['label' => 'Accepted',    'color' => '#1A6FC4'],
            'en_route'    => ['label' => 'En Route',    'color' => '#3B82F6'],
            'in_progress' => ['label' => 'In Progress', 'color' => '#F59E0B'],
            'completed'   => ['label' => 'Completed',   'color' => '#10B981'],
            'cancelled'   => ['label' => 'Cancelled',   'color' => '#EF4444'],
        ];

        $raw = Booking::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $labels = [];
        $values = [];
        $colors = [];
        foreach ($map as $key => $meta) {
            $labels[] = $meta['label'];
            $values[] = (int) ($raw[$key] ?? 0);
            $colors[] = $meta['color'];
        }

        return ['labels' => $labels, 'values' => $values, 'colors' => $colors];
    }

    private function topCategories(): array
    {
        $raw = Booking::selectRaw('service_category, COUNT(*) as total')
            ->whereNotNull('service_category')
            ->where('service_category', '!=', '')
            ->groupBy('service_category')
            ->orderByDesc('total')
            ->limit(5)
            ->pluck('total', 'service_category');

        return [
            'labels' => $raw->keys()->values()->all(),
            'values' => $raw->values()->map(fn ($v) => (int) $v)->all(),
        ];
    }

    private function bookingTrend(): array
    {
        $days = 14;
        $rows = Booking::where('created_at', '>=', now()->subDays($days - 1)->startOfDay())
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, COALESCE(SUM(CASE WHEN status = "completed" THEN CAST(price AS DECIMAL(12,2)) ELSE 0 END), 0) as revenue')
            ->groupBy('date')
            ->pluck('count', 'date');

        $revenueRows = Booking::where('status', 'completed')
            ->where('completed_at', '>=', now()->subDays($days - 1)->startOfDay())
            ->selectRaw('DATE(completed_at) as date, COALESCE(SUM(CAST(price AS DECIMAL(12,2))), 0) as revenue')
            ->groupBy('date')
            ->pluck('revenue', 'date');

        $labels = [];
        $counts = [];
        $revenues = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = $date;
            $counts[] = (int) ($rows[$date] ?? 0);
            $revenues[] = (float) ($revenueRows[$date] ?? 0);
        }

        return ['labels' => $labels, 'counts' => $counts, 'revenues' => $revenues];
    }

}