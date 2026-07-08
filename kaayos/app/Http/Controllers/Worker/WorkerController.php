<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\WorkerProfile;
use App\Support\WorkerDocuments;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WorkerController extends Controller
{
    protected function shared(): array
    {
        return [
            'stats'         => $this->getStats(),
            'jobRequests'   => $this->getJobRequests(),
            'schedule'      => $this->getSchedule(),
            'pastSchedule'  => $this->getPastSchedule(),
            'notifications' => $this->getNotifications(),
            'conversations' => $this->getConversations(),
            'earnings'      => $this->getEarnings(),
            'documents'     => $this->getDocuments(),
        ];
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $weekStart = now()->startOfWeek();

        $weeklyEarnings = $user->bookingsAsWorker()->completed()
            ->where('completed_at', '>=', $weekStart)
            ->sum('price') ?? 0;

        $activeJobs = $user->bookingsAsWorker()
            ->whereIn('status', [Booking::STATUS_ACCEPTED, Booking::STATUS_EN_ROUTE, Booking::STATUS_IN_PROGRESS])
            ->count();

        $totalCompleted = $user->bookingsAsWorker()->completed()->count();

        return [
            ['label' => 'Earnings This Week', 'value' => '₱' . number_format($weeklyEarnings), 'icon' => 'fa-coins', 'accent' => true],
            ['label' => 'Active Jobs',       'value' => $activeJobs,                         'icon' => 'fa-briefcase'],
            ['label' => 'Rating',            'value' => '0.0 ★',                            'icon' => 'fa-star'],
            ['label' => 'Completed Jobs',    'value' => $totalCompleted,                     'icon' => 'fa-circle-check'],
        ];
    }

    protected function getJobRequests(?string $filter = null): array
    {
        $query = auth()->user()->bookingsAsWorker()->with('client');

        if ($filter && in_array($filter, Booking::STATUSES)) {
            $query->where('status', $filter);
        }

        return $query->latest()
            ->get()
            ->map(function ($booking) {
                $labelMap = [
                    Booking::STATUS_NEW        => 'New',
                    Booking::STATUS_ACCEPTED   => 'Accepted',
                    Booking::STATUS_EN_ROUTE   => 'En Route',
                    Booking::STATUS_IN_PROGRESS => 'In Progress',
                    Booking::STATUS_COMPLETED  => 'Completed',
                ];

                return [
                    'id'           => $booking->id,
                    'client'       => $booking->client->name ?? 'Unknown',
                    'client_phone' => $booking->client->phone ?? 'N/A',
                    'client_email' => $booking->client->email ?? 'N/A',
                    'service'      => $booking->service_category,
                    'description'  => $booking->description ?? 'No details provided.',
                    'date'         => $booking->scheduled_at->format('M d, Y · h:i A'),
                    'location'     => $booking->address,
                    'status'       => $labelMap[$booking->status] ?? ucfirst($booking->status),
                    'raw_status'   => $booking->status,
                    'price'        => $booking->price ?? 0,
                    'created'      => $booking->created_at->format('M d, Y · h:i A'),
                    'booking_ref'  => $booking->booking_ref ?? 'BK-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT),
                ];
            })
            ->toArray();
    }

    protected function getSchedule(): array
    {
        return auth()->user()->bookingsAsWorker()
            ->whereIn('status', [
                Booking::STATUS_NEW,
                Booking::STATUS_ACCEPTED,
                Booking::STATUS_EN_ROUTE,
                Booking::STATUS_IN_PROGRESS,
            ])
            ->with('client')
            ->orderBy('scheduled_at')
            ->get()
            ->map(function ($booking) {
                $labelMap = [
                    Booking::STATUS_NEW        => 'Pending',
                    Booking::STATUS_ACCEPTED   => 'Confirmed',
                    Booking::STATUS_EN_ROUTE   => 'En Route',
                    Booking::STATUS_IN_PROGRESS => 'In Progress',
                ];

                return [
                    'id'       => $booking->id,
                    'client'   => $booking->client->name ?? 'Unknown',
                    'service'  => $booking->service_category,
                    'date'     => $booking->scheduled_at->format('M d, Y'),
                    'time'     => $booking->scheduled_at->format('g:i A'),
                    'location' => $booking->address,
                    'status'   => $labelMap[$booking->status] ?? ucfirst($booking->status),
                    'raw_status' => $booking->status,
                ];
            })
            ->toArray();
    }

    protected function getPastSchedule(): array
    {
        return auth()->user()->bookingsAsWorker()
            ->where('status', Booking::STATUS_COMPLETED)
            ->with('client')
            ->latest('completed_at')
            ->get()
            ->map(function ($booking) {
                return [
                    'id'         => $booking->id,
                    'client'     => $booking->client->name ?? 'Unknown',
                    'service'    => $booking->service_category,
                    'date'       => $booking->completed_at?->format('M d, Y') ?? $booking->scheduled_at->format('M d, Y'),
                    'time'       => $booking->completed_at?->format('g:i A') ?? '',
                    'location'   => $booking->address,
                    'status'     => 'Completed',
                    'raw_status' => Booking::STATUS_COMPLETED,
                ];
            })
            ->toArray();
    }

    protected function getNotifications(): array
    {
        return auth()->user()->notifications
            ->map(function ($notif) {
                $data = $notif->data;

                $typeMap = [
                    'App\Notifications\NewBooking'         => 'booking',
                    'App\Notifications\BookingConfirmed'   => 'booking',
                    'App\Notifications\BookingCompleted'   => 'booking',
                    'App\Notifications\NewMessage'         => 'message',
                    'App\Notifications\NewReview'          => 'review',
                    'App\Notifications\PayoutProcessed'    => 'earnings',
                    'App\Notifications\DocumentVerified'   => 'system',
                    'App\Notifications\DocumentRejected'   => 'system',
                ];

                return [
                    'type'   => $typeMap[$notif->type] ?? 'system',
                    'title'  => $data['title'] ?? 'Notification',
                    'desc'   => $data['message'] ?? '',
                    'time'   => $notif->created_at->diffForHumans(),
                    'unread' => is_null($notif->read_at),
                ];
            })
            ->toArray();
    }

    protected function getConversations(): array
    {
        $user = auth()->user();

        $conversations = [];
        $isFirst = true;

        $bookings = $user->bookingsAsWorker()
            ->with('client', 'messages.sender')
            ->latest()
            ->get();

        $grouped = $bookings->groupBy('client_id');

        foreach ($grouped as $clientId => $clientBookings) {
            $client = $clientBookings->first()->client;
            $latestBooking = $clientBookings->first();

            $allMessages = collect();
            foreach ($clientBookings as $booking) {
                $allMessages = $allMessages->merge($booking->messages);
            }

            $allMessages = $allMessages->sortBy('created_at');

            $messages = $allMessages->map(fn($msg) => [
                'from' => $msg->sender_id === $user->id ? 'me' : 'them',
                'text' => $msg->message,
                'time' => $msg->created_at->diffForHumans(),
            ])
                ->values()
                ->toArray();

            $lastMsg = $allMessages->last();
            $unreadCount = $allMessages->where('receiver_id', $user->id)->whereNull('read_at')->count();

            $initials = strtoupper(
                ($client->first_name ? $client->first_name[0] : '') .
                ($client->last_name ? $client->last_name[0] : '')
            );

            $conversations[] = [
                'active'       => $isFirst,
                'booking_id'   => $latestBooking->id,
                'client_id'    => $client->id,
                'initials'     => $initials ?: '?',
                'name'         => $client->name ?? 'Unknown',
                'time'         => $lastMsg?->created_at->diffForHumans() ?? $latestBooking->scheduled_at->diffForHumans(),
                'preview'      => $lastMsg?->message ?? '',
                'unread_count' => $unreadCount,
                'messages'     => $messages,
            ];

            $isFirst = false;
        }

        return $conversations;
    }

    protected function getEarnings(): array
    {
        $user = auth()->user();
        $now = now();

        $completed = $user->bookingsAsWorker()->completed()->get();

        $total = (int) ($completed->sum('price') ?? 0);
        $thisMonth = (int) ($completed->filter(function ($b) use ($now) {
            return $b->completed_at
                && $b->completed_at->month === $now->month
                && $b->completed_at->year === $now->year;
        })->sum('price') ?? 0);

        $pendingPayout = (int) ($user->bookingsAsWorker()
            ->whereIn('status', [Booking::STATUS_ACCEPTED, Booking::STATUS_EN_ROUTE, Booking::STATUS_IN_PROGRESS])
            ->sum('price') ?? 0);

        $count = $completed->count();
        $avgPerJob = $count > 0 ? round($total / $count) : 0;

        $payouts = $completed->sortByDesc('completed_at')->values()
            ->map(function ($booking) {
                return [
                    'date'   => $booking->completed_at?->format('M d, Y') ?? 'N/A',
                    'client' => $booking->client->name ?? 'Unknown',
                    'job'    => $booking->service_category,
                    'amount' => $booking->price ?? 0,
                    'status' => 'Completed',
                ];
            })
            ->toArray();

        return [
            'total'          => $total,
            'this_month'     => $thisMonth,
            'pending_payout' => $pendingPayout,
            'avg_per_job'    => $avgPerJob,
            'payouts'        => $payouts,
        ];
    }

    protected function getDocuments(): array
    {
        $types = WorkerDocuments::types();

        $userDocs = auth()->user()->workerDocuments->keyBy('document_type');

        return array_map(function ($type) use ($userDocs) {
            $userDoc = $userDocs->get($type['name']);

            return [
                'name'        => $type['name'],
                'description' => $type['description'],
                'icon'        => $type['icon'],
                'status'      => $userDoc
                    ? ($userDoc->status === 'verified' ? 'Verified'
                        : ($userDoc->status === 'pending' ? 'Pending' : 'Not Submitted'))
                    : 'Not Submitted',
                'file'        => $userDoc?->file_path
                    ? basename($userDoc->file_path)
                    : null,
                'id'          => $userDoc?->id,
            ];
        }, $types);
    }

    public function dashboard(): View
    {
        return view('worker.dashboard.overview', $this->shared());
    }

    public function notifications(): View
    {
        return view('worker.dashboard.notifications', $this->shared());
    }

    public function jobs(Request $request): View
    {
        $filter = $request->query('filter');
        $data = $this->shared();
        $data['jobRequests'] = $this->getJobRequests($filter);
        $data['activeFilter'] = $filter;

        return view('worker.jobs.index', $data);
    }

    public function schedule(): View
    {
        return view('worker.schedule.index', $this->shared());
    }

    public function messages(): View
    {
        return view('worker.messages.index', $this->shared());
    }

    public function sendMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
            'message'    => ['required', 'string', 'max:2000'],
        ]);

        $booking = Booking::findOrFail($validated['booking_id']);

        if ($booking->worker_id !== auth()->id()) {
            abort(403);
        }

        $msg = Message::create([
            'booking_id'  => $booking->id,
            'sender_id'   => auth()->id(),
            'receiver_id' => $booking->client_id,
            'message'     => $validated['message'],
        ]);

        return response()->json([
            'success' => true,
            'message' => [
                'from' => 'me',
                'text' => $msg->message,
                'time' => $msg->created_at->diffForHumans(),
            ],
        ]);
    }

    public function earnings(): View
    {
        return view('worker.earnings.index', $this->shared());
    }

    public function exportEarnings()
    {
        $payouts = $this->getEarnings()['payouts'];

        $headers = [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="earnings-export.csv"',
        ];

        $callback = function () use ($payouts) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Date', 'Client', 'Job', 'Amount (₱)', 'Status']);

            foreach ($payouts as $row) {
                fputcsv($handle, [
                    $row['date'],
                    $row['client'],
                    $row['job'],
                    number_format($row['amount'], 2),
                    $row['status'],
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function profile(): View
    {
        $user = auth()->user();

        $profile = $user->workerProfile ?? WorkerProfile::create([
            'user_id' => $user->id,
        ]);

        $user->load('workerDocuments');

        $documents = $this->getDocuments();

        return view('worker.profile.index', array_merge(
            $this->shared(),
            [
                'workerProfile' => $profile,
                'portfolios'    => $profile->portfolios()->latest()->get(),
                'documents'     => $documents,
            ]
        ));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'first_name'         => ['required', 'string', 'max:100'],
            'last_name'          => ['required', 'string', 'max:100'],
            'phone'              => ['nullable', 'string', 'max:20', 'regex:/^(?:\+63|0)[0-9]{10}$/'],
            'city'               => ['nullable', 'string', 'max:255'],
            'language'           => ['required', 'string', Rule::in(['English', 'Filipino'])],
            'service_category'   => ['nullable', 'string', 'max:255'],
            'bio'                => ['nullable', 'string', 'max:2000'],
            'skills'             => ['nullable', 'string'],
            'spoken_languages'   => ['nullable', 'string'],
            'hourly_rate'        => ['nullable', 'numeric', 'min:0'],
            'available_days'     => ['nullable', 'string', 'max:255'],
            'preferred_hours'    => ['nullable', 'string', 'max:255'],
            'availability'       => ['nullable', 'json'],
            'service_areas'      => ['nullable', 'string'],
            'years_of_experience'=> ['nullable', 'integer', 'min:0', 'max:100'],
            'service_radius'     => ['nullable', 'integer', 'min:0', 'max:500'],
            'service_zone'       => ['nullable', 'string'],
        ]);

        $user->update([
            'first_name'       => $data['first_name'],
            'last_name'        => $data['last_name'],
            'name'             => $data['first_name'] . ' ' . $data['last_name'],
            'phone'            => $data['phone'] ?? null,
            'city'             => $data['city'] ?? null,
            'language'         => $data['language'],
            'service_category' => $data['service_category'] ?? null,
        ]);

        $profile = $user->workerProfile ?? new WorkerProfile(['user_id' => $user->id]);

        $profile->fill([
            'bio'                => $data['bio'] ?? null,
            'skills'             => isset($data['skills']) ? array_map('trim', explode(',', $data['skills'])) : null,
            'spoken_languages'   => isset($data['spoken_languages']) ? array_map('trim', explode(',', $data['spoken_languages'])) : null,
            'hourly_rate'        => $data['hourly_rate'] ?? null,
            'available_days'     => $data['available_days'] ?? null,
            'preferred_hours'    => $data['preferred_hours'] ?? null,
            'availability'       => isset($data['availability']) ? json_decode($data['availability'], true) : null,
            'service_areas'      => isset($data['service_areas']) ? array_map('trim', explode(',', $data['service_areas'])) : null,
            'years_of_experience'=> $data['years_of_experience'] ?? null,
            'service_radius'     => $data['service_radius'] ?? null,
            'service_zone'       => isset($data['service_zone']) ? array_map('trim', explode(',', $data['service_zone'])) : null,
        ]);

        $profile->save();

        return redirect()
            ->route('worker.profile')
            ->with('success', 'Profile saved successfully.');
    }

    public function uploadAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        return redirect()
            ->route('worker.profile')
            ->with('success', 'Profile photo updated.');
    }

    public function uploadPortfolio(Request $request): RedirectResponse
    {
        $request->validate([
            'photo'   => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'caption' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();
        $profile = $user->workerProfile ?? WorkerProfile::create(['user_id' => $user->id]);

        $path = $request->file('photo')->store('portfolios', 'public');

        $profile->portfolios()->create([
            'photo_path' => $path,
            'caption'    => $request->input('caption'),
        ]);

        return redirect()
            ->route('worker.profile')
            ->with('success', 'Work photo added to portfolio.');
    }

    public function deletePortfolio($id): RedirectResponse
    {
        $profile = auth()->user()->workerProfile;

        if (!$profile) {
            return redirect()->route('worker.profile')->with('error', 'Profile not found.');
        }

        $portfolio = $profile->portfolios()->findOrFail($id);

        Storage::disk('public')->delete($portfolio->photo_path);
        $portfolio->delete();

        return redirect()
            ->route('worker.profile')
            ->with('success', 'Portfolio photo removed.');
    }

    public function uploadDocument(Request $request): RedirectResponse
    {
        $request->validate([
            'document_type' => ['required', 'string'],
            'file'          => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);

        $user = $request->user();

        $doc = $user->workerDocuments()
            ->firstOrNew(['document_type' => $request->input('document_type')]);

        if ($doc->file_path) {
            Storage::disk('public')->delete($doc->file_path);
        }

        $path = $request->file('file')->store('documents', 'public');

        $doc->file_path = $path;
        $doc->status = 'pending';
        $doc->verified_at = null;
        $doc->save();

        return redirect()
            ->route('worker.profile')
            ->with('success', 'Document uploaded successfully. Awaiting verification.');
    }

    public function documents(): View
    {
        return view('worker.documents.index', $this->shared());
    }
}
