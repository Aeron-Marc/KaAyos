<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Booking;

class PageController extends Controller
{
    public function about()
    {
        $stats = [
            'workers'    => User::where('role', 'worker')->active()->count(),
            'jobs'       => Booking::count(),
            'barangays'  => User::whereNotNull('city')->distinct('city')->count('city') ?: 42,
            'rating'     => '4.8',
        ];

        $team = User::where('role', 'admin')->get()->map(fn ($u) => [
            'name'     => $u->name,
            'email'    => $u->email,
            'avatar'   => $u->avatar ? \Illuminate\Support\Facades\Storage::url($u->avatar) : null,
            'initials' => strtoupper(substr($u->first_name, 0, 1) . substr($u->last_name, 0, 1)),
        ]);

        return view('pages.about', compact('stats', 'team'));
    }

    public function contact()
    {
        $stats = [
            'workers'   => User::where('role', 'worker')->active()->count(),
            'jobs'      => Booking::count(),
            'barangays' => User::whereNotNull('city')->distinct('city')->count('city') ?: 42,
        ];

        return view('pages.contact', compact('stats'));
    }

    public function privacy()
    {
        return view('pages.privacy');
    }

    public function safety()
    {
        return view('pages.safety');
    }
}
