<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q');
        $category = $request->input('category');

        $workersQuery = User::where('role', 'worker')
            ->with(['workerProfile.portfolios', 'reviewsReceived'])
            ->active();

        if ($category) {
            $workersQuery->where('service_category', 'LIKE', "%{$category}%");
        }

        if ($query) {
            $workersQuery->where(function ($q) use ($query) {
                $q->where('first_name', 'LIKE', "%{$query}%")
                  ->orWhere('last_name', 'LIKE', "%{$query}%")
                  ->orWhere('service_category', 'LIKE', "%{$query}%");
            });
        }

        $workers = $workersQuery->paginate(12)
            ->through(fn ($u) => [
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
                'works'    => $u->workerProfile?->portfolios?->take(3)->map(fn($p) => [
                    'photo'   => $p->photo_path ? Storage::url($p->photo_path) : null,
                    'caption' => $p->caption,
                ])->toArray() ?? [],
            ]);

        $categories = ServiceCategory::active()->get();

        return view('search.index', compact('workers', 'categories', 'query', 'category'));
    }
}
