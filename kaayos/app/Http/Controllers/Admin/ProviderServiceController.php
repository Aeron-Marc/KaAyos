<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProviderService;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;

class ProviderServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = ProviderService::with(['user', 'service.category']);

        if ($search = $request->input('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        if ($serviceId = $request->input('service_id')) {
            $query->where('service_id', $serviceId);
        }

        $providerServices = $query->latest()->paginate(20)->withQueryString();
        $services = Service::active()->with('category')->get();

        return view('admin.provider_services.index', compact('providerServices', 'services'));
    }
}
