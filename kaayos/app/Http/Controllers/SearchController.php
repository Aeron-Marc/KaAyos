<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ServiceCategory;
use App\Support\TuyBarangays;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SearchController extends Controller
{
    private const PER_PAGE = 12;

    private const TRADE_SYNONYMS = [
        'Plumbing'   => ['plumber', 'plumbers', 'pipe', 'leak'],
        'Electrical' => ['electrician', 'electricians', 'wiring', 'circuit'],
        'Cleaning'   => ['cleaner', 'cleaners', 'house cleaning'],
        'Carpentry'  => ['carpenter', 'carpenters', 'wood'],
        'Painting'   => ['painter', 'painters'],
        'Aircon'     => ['aircon', 'air conditioning'],
    ];

    /**
     * Natural-language intent cues (English + Filipino/Taglish) -> service categories.
     * Matched with word-boundary regex against the free-text query.
     */
    private const INTENT_KEYWORDS = [
        'Plumbing'   => ['plumbing', 'plumber', 'plumbers', 'gripo', 'tubero', 'leak', 'tumutulo', 'tulo', 'pipe', 'tubero', 'faucet', 'sink', 'lababo', 'toilet', 'inodoro', 'kubeta', 'shower', 'banyo'],
        'Electrical' => ['electrical', 'electrician', 'electricians', 'kuryente', 'ilaw', 'bombilya', 'wiring', 'circuit', 'saksakan', 'outlet', 'switch', 'bisagra', 'latelectric'],
        'Cleaning'   => ['cleaning', 'cleaner', 'cleaners', 'linis', 'maglilinis', 'walis', 'housekeeping', 'general clean', 'deep clean', 'burara'],
        'Carpentry'  => ['carpentry', 'carpenter', 'carpenters', 'karpintero', 'wood', 'kahoy', 'furniture', 'muwebles', 'cabinet', 'gabineto', 'pinto', 'door'],
        'Painting'   => ['painting', 'painter', 'painters', 'pintura', 'paint', 'kulyit', 'repaint'],
        'Aircon'     => ['aircon', 'air con', 'air conditioning', 'ere', 'split type', 'window type', 'compressor'],
    ];

    public function index(Request $request)
    {
        $validated = $request->validate([
            'q'        => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
        ]);
        $query    = $validated['q'] ?? null;
        $category = $validated['category'] ?? null;
        $location = $validated['location'] ?? null;

        // Natural-language location extraction: "plumber in Bolbok", "malapit sa Luna"
        if (! $location && $query) {
            $location = $this->extractLocationFromQuery($query);
        }

        $workersQuery = User::where('role', 'worker')
            ->with(['workerProfile.portfolios'])
            ->withCount('reviewsReceived')
            ->active()
            ->whereHas('workerProfile', fn ($q) => self::whereAvailable($q));

        if ($category) {
            $workersQuery->where('service_category', 'LIKE', $category);
        }

        if ($query) {
            $categories = $this->categoriesForKeyword($query);

            $workersQuery->where(function ($q) use ($query, $categories) {
                $q->where('first_name', 'LIKE', "%{$query}%")
                  ->orWhere('last_name', 'LIKE', "%{$query}%")
                  ->orWhere('service_category', 'LIKE', "%{$query}%");

                foreach ($categories as $categoryName) {
                    $q->orWhere('service_category', 'LIKE', "%{$categoryName}%");
                }
            });
        }

        $locationBarangay = null;
        $locationNotice   = null;

        if ($location) {
            $locationBarangay = TuyBarangays::parseLocation($location);

            if ($locationBarangay === null) {
                $locationNotice = "We couldn't find a barangay matching “{$location}”. Showing all matching workers instead.";
            }
        }

        $lat = null;
        $lng = null;
        if ($locationBarangay) {
            [$lat, $lng] = TuyBarangays::pointForStatic($locationBarangay);
        }

        $rows = $workersQuery->get()->map(function (User $u) use ($lat, $lng) {
            $distance = null;

            if ($lat !== null && $lng !== null) {
                $wLat = $u->workerProfile?->current_latitude;
                $wLng = $u->workerProfile?->current_longitude;

                if ($wLat !== null && $wLng !== null) {
                    $distance = TuyBarangays::distanceKm($lat, $lng, (float) $wLat, (float) $wLng);
                }
            }

            return ['worker' => $u, 'distance' => $distance];
        });

        if ($lat !== null && $lng !== null) {
            $rows = $rows->filter(function (array $row) {
                $radius = $row['worker']->workerProfile?->service_radius_km;

                if ($radius !== null && $row['distance'] !== null && $row['distance'] > $radius) {
                    return false;
                }

                return true;
            });

            $rows = $rows->sortBy(fn (array $row) => $row['distance'] ?? PHP_FLOAT_MAX);
        }

        $rows = $rows->values();

        $page    = Paginator::resolveCurrentPage();
        $perPage = self::PER_PAGE;

        $workers = new LengthAwarePaginator(
            $rows->forPage($page, $perPage),
            $rows->count(),
            $perPage,
            $page,
            [
                'path'  => Paginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );

        $workers->through(fn (array $row) => $this->formatWorker($row['worker'], $row['distance']));

        $categories = ServiceCategory::active()->get();

        return view('search.index', compact(
            'workers',
            'categories',
            'query',
            'category',
            'location',
            'locationBarangay',
            'locationNotice',
        ));
    }

    /**
     * Restrict to workers with at least one active day in their availability
     * JSON. Portable across MariaDB (no $[*] path wildcard support), MySQL,
     * and SQLite. Availability is a fixed 7-entry array (Mon..Sun).
     */
    protected static function whereAvailable($q): void
    {
        $q->where(function ($w) {
            for ($i = 0; $i < 7; $i++) {
                $w->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(availability, ?)) = 'true'", ["\$[{$i}].active"]);
            }
        });
    }

    protected function categoriesForKeyword(?string $query): array
    {
        if (!$query) {
            return [];
        }

        $needle = strtolower(trim($query));

        $matches = [];

        foreach (self::TRADE_SYNONYMS as $category => $aliases) {
            if (strtolower($category) === $needle || in_array($needle, $aliases, true)) {
                $matches[] = $category;
            }
        }

        if (empty($matches)) {
            $matches = $this->matchIntentKeywords($needle);
        }

        return $matches;
    }

    /**
     * Scan a free-text query (e.g. "my sink is leaking", "sira ang gripo")
     * for intent keywords and return all matching service categories.
     */
    protected function matchIntentKeywords(string $needle): array
    {
        $matches = [];

        foreach (self::INTENT_KEYWORDS as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (preg_match('/\b' . preg_quote($keyword, '/') . '\b/i', $needle)) {
                    $matches[] = $category;
                    break;
                }
            }
        }

        return $matches;
    }

    /**
     * Pull a barangay mention out of a natural-language query.
     * Handles "plumber in Bolbok", "malapit sa Luna", "near Brgy. Talon",
     * or a bare barangay mention anywhere in the text.
     */
    protected function extractLocationFromQuery(?string $query): ?string
    {
        if (!$query) {
            return null;
        }

        if (preg_match('/\b(?:in|sa|near|malapit sa|barangay|brgy\.?)\s+([a-z][a-z\-\s]{1,30})/i', $query, $m)) {
            $found = TuyBarangays::parseLocation(trim($m[1]));

            if ($found !== null) {
                return $found;
            }
        }

        return TuyBarangays::parseLocation($query);
    }

    protected function formatWorker(User $u, ?float $distance): array
    {
        return [
            'id'          => $u->id,
            'name'        => $u->name,
            'category'    => $u->service_category ?? 'General',
            'avatar'      => $u->avatar ? Storage::url($u->avatar) : null,
            'initials'    => strtoupper(substr($u->first_name, 0, 1) . substr($u->last_name, 0, 1)),
            'rating'      => $u->workerProfile?->average_rating ?? 0,
            'reviews'     => $u->reviews_received_count,
            'distance'    => $u->residence,
            'distance_km' => $distance !== null ? round($distance, 1) : null,
            'price'       => $u->workerProfile?->hourly_rate ?? 0,
            'verified'    => $u->workerProfile?->government_id_verified ?? false,
            'skills'      => $u->workerProfile?->skills ?? [],
            'works'       => $u->workerProfile?->portfolios?->take(3)->map(fn($p) => [
                'photo'   => $p->photo_path ? Storage::url($p->photo_path) : null,
                'caption' => $p->caption,
            ])->toArray() ?? [],
        ];
    }
}