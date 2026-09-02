<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index(Request $request)
    {
        $query = Testimonial::with('user');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $testimonials = $query->latest()->paginate(15)->withQueryString();
        $pendingCount = Testimonial::pending()->count();

        return view('admin.testimonials.index', compact('testimonials', 'pendingCount'));
    }

    public function show(Testimonial $testimonial)
    {
        $testimonial->load('user');

        return view('admin.testimonials.show', compact('testimonial'));
    }

    public function approve(Testimonial $testimonial)
    {
        $testimonial->update([
            'status'      => 'approved',
            'is_active'   => true,
            'admin_notes' => null,
        ]);

        return redirect()->back()
            ->with('success', "Testimonial from {$testimonial->name} has been approved and is now visible on the landing page.");
    }

    public function reject(Request $request, Testimonial $testimonial)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $testimonial->update([
            'status'      => 'rejected',
            'is_active'   => false,
            'admin_notes' => $request->input('rejection_reason'),
        ]);

        return redirect()->back()
            ->with('success', "Testimonial from {$testimonial->name} has been rejected.");
    }
}
