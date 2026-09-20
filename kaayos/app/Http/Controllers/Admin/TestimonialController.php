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

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $testimonials = $query->latest()->paginate(15)->withQueryString();

        return view('admin.testimonials.index', compact('testimonials'));
    }

    public function show(Testimonial $testimonial)
    {
        $testimonial->load('user');

        return view('admin.testimonials.show', compact('testimonial'));
    }

    public function updateStatus(Request $request, Testimonial $testimonial)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,pending,rejected'],
        ]);

        $testimonial->update([
            'status'    => $validated['status'],
            'is_active' => $validated['status'] === 'approved',
        ]);

        return back()->with('success', "Testimonial #{$testimonial->id} status updated to {$validated['status']}.");
    }

    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')
            ->with('success', "Testimonial #{$testimonial->id} has been deleted.");
    }
}
