<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\ProviderService;
use App\Models\Service;
use Illuminate\Http\Request;

class MyServicesController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $workerServices = $user->providerServices()
            ->with('service.category')
            ->orderBy('id')
            ->get();

        $availableServices = Service::active()
            ->with('category')
            ->whereNotIn('id', $workerServices->pluck('service_id'))
            ->orderBy('name')
            ->get();

        return view('worker.services.index', compact('workerServices', 'availableServices'));
    }

    public function add(Request $request)
    {
        $validated = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
        ]);

        $service = Service::active()->findOrFail($validated['service_id']);

        ProviderService::firstOrCreate(
            ['user_id' => auth()->id(), 'service_id' => $service->id],
            ['is_available' => true]
        );

        return redirect()->route('worker.services.index')
            ->with('success', $service->name . ' added to your services.');
    }

    public function updatePrice(Request $request, Service $service)
    {
        $validated = $request->validate([
            'custom_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
        ]);

        $link = auth()->user()->providerServices()->where('service_id', $service->id)->firstOrFail();

        $link->update([
            'custom_price' => $request->filled('custom_price')
                ? round((float) $validated['custom_price'], 2)
                : null,
        ]);

        return redirect()->route('worker.services.index')
            ->with('success', 'Price updated for ' . $service->name . '.');
    }

    public function toggle(Request $request, Service $service)
    {
        $link = auth()->user()->providerServices()->where('service_id', $service->id)->firstOrFail();

        $link->update(['is_available' => ! $link->is_available]);

        return redirect()->route('worker.services.index')
            ->with('success', $service->name . ' is now ' . ($link->is_available ? 'available' : 'hidden') . '.');
    }

    public function destroy(Request $request, Service $service)
    {
        $link = auth()->user()->providerServices()->where('service_id', $service->id)->firstOrFail();
        $link->delete();

        return redirect()->route('worker.services.index')
            ->with('success', $service->name . ' removed from your services.');
    }
}
