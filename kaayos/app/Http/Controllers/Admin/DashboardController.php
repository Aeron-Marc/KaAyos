<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Dispute;
use App\Models\User;
use App\Models\WorkerDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalProviders = User::where('role', 'worker')->count();
        $totalClients = User::where('role', 'client')->count();
        $totalAdmins = User::where('role', 'admin')->count();
        $activeUsers = User::active()->count();
        $suspendedUsers = User::suspended()->count();

        $totalBookings = Booking::count();
        $activeBookings = Booking::whereIn('status', ['pending', 'confirmed', 'in_progress'])->count();
        $completedBookings = Booking::completed()->count();
        $cancelledBookings = Booking::cancelled()->count();

        $totalRevenue = Booking::completed()->sum('price');
        $pendingVerifications = WorkerDocument::where('status', 'pending')->count();

        $totalDisputes = Dispute::count();
        $openDisputes = Dispute::open()->count();

        $recentUsers = User::latest()->take(5)->get();
        $recentBookings = Booking::with(['client', 'worker'])->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalUsers', 'totalProviders', 'totalClients', 'totalAdmins',
            'activeUsers', 'suspendedUsers',
            'totalBookings', 'activeBookings', 'completedBookings', 'cancelledBookings',
            'totalRevenue', 'pendingVerifications',
            'totalDisputes', 'openDisputes',
            'recentUsers', 'recentBookings'
        ));
    }
}
