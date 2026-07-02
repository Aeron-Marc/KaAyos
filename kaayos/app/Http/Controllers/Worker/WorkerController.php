<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\WorkerProfile;
use App\Support\WorkerDocuments;
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
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->count();

        $totalCompleted = $user->bookingsAsWorker()->completed()->count();

        return [
            ['label' => 'Earnings This Week', 'value' => '₱' . number_format($weeklyEarnings), 'icon' => 'fa-coins', 'accent' => true],
            ['label' => 'Active Jobs',       'value' => $activeJobs,                         'icon' => 'fa-briefcase'],
            ['label' => 'Rating',            'value' => '0.0 ★',                            'icon' => 'fa-star'],
            ['label' => 'Completed Jobs',    'value' => $totalCompleted,                     'icon' => 'fa-circle-check'],
        ];
    }

    protected function getJobRequests(): array
    {
        return auth()->user()->bookingsAsWorker()
            ->with('client')
            ->latest()
            ->get()
            ->map(function ($booking) {
                $statusMap = [
                    'pending'    => 'Pending',
                    'confirmed'  => 'Accepted',
                    'in_progress'=> 'Accepted',
                    'completed'  => 'Completed',
                    'cancelled'  => 'Cancelled',
                ];

                return [
                    'id'       => $booking->id,
                    'client'   => $booking->client->name ?? 'Unknown',
                    'service'  => $booking->service_category,
                    'date'     => $booking->scheduled_at->format('M d, Y · h:i A'),
                    'location' => $booking->address,
                    'status'   => $statusMap[$booking->status] ?? ucfirst($booking->status),
                    'price'    => $booking->price ?? 0,
                ];
            })
            ->toArray();
    }

    protected function getSchedule(): array
    {
        return auth()->user()->bookingsAsWorker()
            ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->with('client')
            ->orderBy('scheduled_at')
            ->get()
            ->map(function ($booking) {
                return [
                    'id'       => $booking->id,
                    'client'   => $booking->client->name ?? 'Unknown',
                    'service'  => $booking->service_category,
                    'date'     => $booking->scheduled_at->format('M d, Y'),
                    'time'     => $booking->scheduled_at->format('g:i A'),
                    'location' => $booking->address,
                    'status'   => in_array($booking->status, ['confirmed', 'in_progress']) ? 'Confirmed' : 'Pending',
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
        return [];
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
            ->whereIn('status', ['confirmed', 'in_progress'])
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

    public function jobs(): View
    {
        return view('worker.jobs.index', $this->shared());
    }

    public function schedule(): View
    {
        return view('worker.schedule.index', $this->shared());
    }

    public function messages(): View
    {
        return view('worker.messages.index', $this->shared());
    }

    public function earnings(): View
    {
        return view('worker.earnings.index', $this->shared());
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
            'email'              => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
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
            'service_areas'      => ['nullable', 'string'],
            'years_of_experience'=> ['nullable', 'integer', 'min:0', 'max:100'],
            'service_radius'     => ['nullable', 'integer', 'min:0', 'max:500'],
            'service_zone'       => ['nullable', 'string'],
        ]);

        $user->update([
            'first_name'       => $data['first_name'],
            'last_name'        => $data['last_name'],
            'name'             => $data['first_name'] . ' ' . $data['last_name'],
            'email'            => $data['email'],
            'phone'            => $data['phone'] ?: null,
            'city'             => $data['city'] ?: null,
            'language'         => $data['language'],
            'service_category' => $data['service_category'] ?: null,
        ]);

        $profile = $user->workerProfile ?? new WorkerProfile(['user_id' => $user->id]);

        $profile->fill([
            'bio'                => $data['bio'] ?: null,
            'skills'             => $data['skills'] ? array_map('trim', explode(',', $data['skills'])) : null,
            'spoken_languages'   => $data['spoken_languages'] ? array_map('trim', explode(',', $data['spoken_languages'])) : null,
            'hourly_rate'        => $data['hourly_rate'] ?: null,
            'available_days'     => $data['available_days'] ?: null,
            'preferred_hours'    => $data['preferred_hours'] ?: null,
            'service_areas'      => $data['service_areas'] ? array_map('trim', explode(',', $data['service_areas'])) : null,
            'years_of_experience'=> $data['years_of_experience'] ?: null,
            'service_radius'     => $data['service_radius'] ?: null,
            'service_zone'       => $data['service_zone'] ? array_map('trim', explode(',', $data['service_zone'])) : null,
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
