<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $testimonials = [
            [
                'name' => 'Ana Reyes',
                'role' => 'Homeowner, Tuy',
                'content' => '"Na-book ko agad si Mang Jose para sa sirang gripo ko. Wala pang isang oras, nasa bahay na at naayos agad. Sobrang convenient!"',
                'rating' => 5,
                'avatar_initials' => 'AR',
                'sort_order' => 1,
            ],
            [
                'name' => 'Mang Carlos',
                'role' => 'Electrician, Tuy',
                'content' => '"Dati referral lang ang kitaan. Ngayon, may regular akong booking galing sa KaAyos. Nakaipon na ako para sa bagong gamit."',
                'rating' => 5,
                'avatar_initials' => 'MC',
                'sort_order' => 2,
            ],
            [
                'name' => 'Dennis B.',
                'role' => 'Homeowner, Nasugbu',
                'content' => '"Yung AI matching nila, hindi biro. Inirecommend agad yung tamang worker para sa painting project namin. Sulit na sulit!"',
                'rating' => 5,
                'avatar_initials' => 'DB',
                'sort_order' => 3,
            ],
            [
                'name' => 'Luzviminda Flores',
                'role' => 'Homeowner, Lumbangan',
                'content' => '"Mabilis at maayos ang serbisyo. Na-book ko si Aling Cherry para sa deep cleaning, ang linis ng bahay ko! Siguradong balik-balikan ko ito."',
                'rating' => 5,
                'avatar_initials' => 'LF',
                'sort_order' => 4,
            ],
            [
                'name' => 'Jericho Cruz',
                'role' => 'Homeowner, Dalima',
                'content' => '"Maayos magtrabaho si Mang Roberto — yung mga sira naming cabinet inayos agad. Hindi na ako naghanap ng iba."',
                'rating' => 4,
                'avatar_initials' => 'JC',
                'sort_order' => 5,
            ],
        ];

        foreach ($testimonials as $data) {
            Testimonial::create($data);
        }
    }
}
