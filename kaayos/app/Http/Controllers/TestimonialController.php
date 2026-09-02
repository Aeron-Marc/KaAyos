<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TestimonialController extends Controller
{
    public function index()
    {
        $testimonials = Testimonial::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        $pendingCount = Testimonial::where('user_id', Auth::id())->pending()->count();
        $approvedCount = Testimonial::where('user_id', Auth::id())->approved()->count();

        return view('testimonials.index', compact('testimonials', 'pendingCount', 'approvedCount'));
    }

    public function create()
    {
        $hasExisting = Testimonial::where('user_id', Auth::id())->exists();

        return view('testimonials.create', compact('hasExisting'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'content' => 'required|string|max:1000',
        ]);

        $user = Auth::user();
        $initials = strtoupper(substr($user->first_name ?? '', 0, 1) . substr($user->last_name ?? '', 0, 1));
        if (empty($initials)) {
            $initials = strtoupper(substr($user->name ?? 'U', 0, 2));
        }

        Testimonial::create([
            'user_id'          => $user->id,
            'name'             => $user->name,
            'role'             => $user->role === 'worker' ? 'Trabahador, ' . ($user->barangay ?? 'Tuy') : 'Homeowner, ' . ($user->barangay ?? 'Tuy'),
            'content'          => $validated['content'],
            'rating'           => $validated['rating'],
            'avatar_initials'  => $initials,
            'status'           => 'pending',
        ]);

        return redirect()->route('testimonials.index')
            ->with('success', 'Your testimonial has been submitted and is awaiting admin approval.');
    }
}
