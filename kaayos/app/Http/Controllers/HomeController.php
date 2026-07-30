<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ServiceCategory;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->input('category');

        $categories = ServiceCategory::active()->get();

        $workersQuery = User::where('role', 'worker')
            ->withCount('reviewsReceived')
            ->withAvg('reviewsReceived', 'rating')
            ->with([
                'workerProfile.portfolios',
                'reviewsReceived' => fn($q) => $q->latest()->take(2)->with('client'),
            ])
            ->active();

        if ($category) {
            $workersQuery->where('service_category', $category);
        }

        $workers = $workersQuery->paginate(5)
            ->through(fn ($u) => [
                'id'       => $u->id,
                'name'     => $u->name,
                'category' => $u->service_category ?? 'General',
                'avatar'   => $u->avatar ? Storage::url($u->avatar) : null,
                'initials' => strtoupper(substr($u->first_name, 0, 1) . substr($u->last_name, 0, 1)),
                'rating'   => (float) ($u->reviews_received_avg_rating ?? $u->workerProfile?->average_rating ?? 0),
                'reviews'  => (int) ($u->reviews_received_count ?? 0),
                'distance' => 'Tuy, Batangas',
                'price'    => $u->workerProfile?->hourly_rate ?? 0,
                'verified' => $u->workerProfile?->government_id_verified ?? false,
                'skills'   => $u->workerProfile?->skills ?? [],
                'works'    => $u->workerProfile?->portfolios?->take(3)->map(fn($p) => [
                    'photo'   => $p->photo_path ? Storage::url($p->photo_path) : null,
                    'caption' => $p->caption,
                ])->toArray() ?? [],
                'recent_reviews' => $u->reviewsReceived->map(fn($r) => [
                    'rating'     => $r->rating,
                    'comment'    => $r->comment,
                    'client_name' => $r->client?->name ?? 'Anonymous',
                ])->toArray(),
            ])
            ->appends(request()->query());

        $testimonials = Testimonial::active()->ordered()->get();

        return view('home', compact('workers', 'categories', 'category', 'testimonials'));
    }
}
