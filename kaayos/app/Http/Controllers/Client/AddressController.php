<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ClientAddress;
use App\Support\TuyBarangays;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AddressController extends Controller
{
    public function index()
    {
        $addresses = auth()->user()->addresses()
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get();

        return view('client.account.addresses', compact('addresses'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $this->validateAddress($request);

        $user = auth()->user();

        DB::transaction(function () use ($user, $validated) {
            $firstAddress = $user->addresses()->doesntExist();

            $isDefault = ($validated['is_default'] ?? false) || $firstAddress;

            if ($isDefault) {
                $user->addresses()->update(['is_default' => false]);
            }

            $user->addresses()->create([
                'label'      => $validated['label'],
                'house_no'   => $validated['house_no'],
                'barangay'   => $validated['barangay'],
                'latitude'   => $validated['latitude'] ?? null,
                'longitude'  => $validated['longitude'] ?? null,
                'is_default' => $isDefault,
            ]);
        });

        if ($request->expectsJson()) {
            $address = $user->addresses()->latest('id')->first();

            return response()->json([
                'success' => true,
                'message' => 'Address saved.',
                'address' => $this->addressPayload($address),
            ]);
        }

        return redirect()->route('client.account.addresses')
            ->with('success', 'Address saved.');
    }

    public function update(Request $request, ClientAddress $address): JsonResponse|RedirectResponse
    {
        if ((int) $address->user_id !== auth()->id()) {
            abort(404);
        }

        $validated = $this->validateAddress($request, $address);

        if ($validated['is_default'] ?? false) {
            $this->makeDefault($address);
        }

        $address->update([
            'label'     => $validated['label'],
            'house_no'  => $validated['house_no'],
            'barangay'  => $validated['barangay'],
            'latitude'  => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Address updated.',
                'address' => $this->addressPayload($address->fresh()),
            ]);
        }

        return redirect()->route('client.account.addresses')
            ->with('success', 'Address updated.');
    }

    public function destroy(Request $request, ClientAddress $address): JsonResponse|RedirectResponse
    {
        if ((int) $address->user_id !== auth()->id()) {
            abort(404);
        }

        $wasDefault = $address->is_default;
        $userId = $address->user_id;
        $address->delete();

        if ($wasDefault) {
            $successor = auth()->user()->addresses()->orderBy('id')->first();
            if ($successor) {
                $this->makeDefault($successor);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Address removed.']);
        }

        return redirect()->route('client.account.addresses')
            ->with('success', 'Address removed.');
    }

    public function setDefault(Request $request, ClientAddress $address): JsonResponse|RedirectResponse
    {
        if ((int) $address->user_id !== auth()->id()) {
            abort(404);
        }

        $this->makeDefault($address);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Default address updated.']);
        }

        return redirect()->route('client.account.addresses')
            ->with('success', 'Default address updated.');
    }

    private function validateAddress(Request $request, ?ClientAddress $address = null): array
    {
        return $request->validate([
            'label'     => ['required', 'string', 'max:30'],
            'house_no'  => ['required', 'string', 'max:255'],
            'barangay'  => ['required', 'string', Rule::in(TuyBarangays::allBarangays())],
            'latitude'  => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_default' => ['nullable', 'boolean'],
        ]);
    }

    private function makeDefault(ClientAddress $address): void
    {
        auth()->user()->addresses()
            ->where('id', '!=', $address->id)
            ->update(['is_default' => false]);

        $address->update(['is_default' => true]);
    }

    private function addressPayload(ClientAddress $address): array
    {
        return [
            'id'         => $address->id,
            'label'      => $address->label,
            'house_no'   => $address->house_no,
            'barangay'   => $address->barangay,
            'latitude'   => $address->latitude,
            'longitude'  => $address->longitude,
            'is_default' => (bool) $address->is_default,
            'full'       => $address->fullAddress(),
        ];
    }
}
