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
            'barangays'  => 22,
            'rating'     => '4.8',
        ];

        /*
         * Capstone Development Team.
         * To add a member: copy an array entry below. To add a photo, drop the
         * file in `public/images/team/` (e.g. aeron.jpg) and set 'photo'
         * to that file name (leave null to show the placeholder box).
         * Recommended size: 600x600 (square), JPG or PNG.
         */
        $developers = [
            [
                'name'     => 'Aeron Marc M. Salanguit',
                'role'     => 'Software Engineer',
                'photo'    => 'Aeronmarcsalanguit.jpg',
                'initials' => 'AS',
            ],
            [
                'name'     => 'Jan Emmanuel Formentos',
                'role'     => 'System Analyst',
                'photo'    => 'Janemmanuelformentos.jpg',
                'initials' => 'JF',
            ],
            [
                'name'     => 'John Dave Briones',
                'role'     => 'QA Tester',
                'photo'    => 'Johndavebriones.jpg',
                'initials' => 'JB',
            ],
            [
                'name'     => 'Asst. Prof. Benjie R. Samonte',
                'role'     => 'Adviser',
                'photo'    => 'Master Benj Samonte.jpg',
                'initials' => 'BS',
            ],
            // Copy the block below to add a 4th member:
            // [
            //     'name'     => 'Member Name',
            //     'role'     => 'Role / Position',
            //     'photo'    => null,
            //     'initials' => 'XX',
            // ],
        ];

        $team = User::where('role', 'admin')->get()->map(fn ($u) => [
            'name'     => $u->name,
            'email'    => $u->email,
            'avatar'   => $u->avatar ? \Illuminate\Support\Facades\Storage::url($u->avatar) : null,
            'initials' => strtoupper(substr($u->first_name, 0, 1) . substr($u->last_name, 0, 1)),
        ]);

        return view('pages.about', compact('stats', 'team', 'developers'));
    }

    public function contact()
    {
        $stats = [
            'workers'   => User::where('role', 'worker')->active()->count(),
            'jobs'      => Booking::count(),
            'barangays' => 22,
        ];

        return view('pages.contact', compact('stats'));
    }

    public function privacy()
    {
        return view('pages.privacy');
    }

    public function terms()
    {
        return view('pages.terms');
    }

    public function safety()
    {
        return view('pages.safety');
    }
}
