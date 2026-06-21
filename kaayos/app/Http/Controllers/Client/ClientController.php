<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Support\ClientSampleData;
use Illuminate\View\View;

class ClientController extends Controller
{
    protected function shared(): array
    {
        return [
            'categories' => ClientSampleData::categories(),
            'workers' => ClientSampleData::workers(),
            'bookings' => ClientSampleData::bookings(),
            'notifications' => ClientSampleData::notifications(),
            'conversations' => ClientSampleData::conversations(),
            'reviews' => ClientSampleData::reviews(),
            'stats' => ClientSampleData::stats(),
        ];
    }

    public function dashboard(): View
    {
        return view('client.dashboard.overview', $this->shared());
    }

    public function notifications(): View
    {
        return view('client.dashboard.notifications', $this->shared());
    }

    public function bookings(): View
    {
        return view('client.bookings.index', $this->shared());
    }

    public function messages(): View
    {
        return view('client.messages.index', $this->shared());
    }

    public function reviews(): View
    {
        return view('client.reviews.index', $this->shared());
    }

    public function profile(): View
    {
        return view('client.account.profile', $this->shared());
    }
}
