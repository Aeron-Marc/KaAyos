<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    public function index()
    {
        $workers = User::where('role', 'worker')
            ->with('workerProfile')
            ->active()
            ->take(8)
            ->get()
            ->map(fn ($u) => [
                'id'       => $u->id,
                'name'     => $u->name,
                'category' => $u->service_category ?? 'General',
                'avatar'   => $u->avatar ? Storage::url($u->avatar) : null,
                'initials' => strtoupper(substr($u->first_name, 0, 1) . substr($u->last_name, 0, 1)),
                'rating'   => $u->workerProfile?->average_rating ?? 0,
                'reviews'  => $u->reviewsReceived()->count(),
                'distance' => 'Tuy, Batangas',
                'price'    => $u->workerProfile?->hourly_rate ?? 0,
                'verified' => $u->workerProfile?->government_id_verified ?? false,
                'skills'   => $u->workerProfile?->skills ?? [],
            ])
            ->toArray();

        return view('home', compact('workers'));
    }
}