<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingMaterial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookingMaterialController extends Controller
{
    /**
     * Add a BOM line item. Workers may manage items while the job is active;
     * admins may manage them at any time (dispute fixes).
     */
    public function store(Request $request, Booking $booking): JsonResponse
    {
        $denied = $this->guardManage($booking);
        if ($denied) {
            return $denied;
        }

        $validated = $this->validateItems($request);

        $material = $booking->materials()->create([
            'name'              => $validated['name'],
            'qty'               => $validated['qty'],
            'unit_price'        => $validated['unit_price'],
            'line_total'        => BookingMaterial::computeLineTotal((float) $validated['qty'], (float) $validated['unit_price']),
            'receipt_photo_path' => $this->storeReceipt($request),
        ]);

        BookingMaterial::recalcForBooking($booking->fresh());

        return response()->json([
            'success'          => true,
            'material'         => $material->fresh(),
            'materials_total'  => (float) $booking->fresh()->materials_total,
            'invoice_total'    => $booking->fresh()->invoice_total,
        ], 201);
    }

    public function update(Request $request, Booking $booking, BookingMaterial $material): JsonResponse
    {
        if ((int) $material->booking_id !== (int) $booking->id) {
            abort(404);
        }

        $denied = $this->guardManage($booking);
        if ($denied) {
            return $denied;
        }

        $validated = $this->validateItems($request);

        $receiptPath = $material->receipt_photo_path;
        if ($request->hasFile('receipt')) {
            if ($material->receipt_photo_path) {
                Storage::disk('public')->delete($material->receipt_photo_path);
            }
            $receiptPath = $this->storeReceipt($request);
        } elseif ($request->boolean('remove_receipt')) {
            if ($material->receipt_photo_path) {
                Storage::disk('public')->delete($material->receipt_photo_path);
            }
            $receiptPath = null;
        }

        $material->update([
            'name'               => $validated['name'],
            'qty'                => $validated['qty'],
            'unit_price'         => $validated['unit_price'],
            'line_total'         => BookingMaterial::computeLineTotal((float) $validated['qty'], (float) $validated['unit_price']),
            'receipt_photo_path' => $receiptPath,
        ]);

        BookingMaterial::recalcForBooking($booking->fresh());

        return response()->json([
            'success'          => true,
            'material'         => $material->fresh(),
            'materials_total'  => (float) $booking->fresh()->materials_total,
            'invoice_total'    => $booking->fresh()->invoice_total,
        ]);
    }

    public function destroy(Booking $booking, BookingMaterial $material): JsonResponse
    {
        if ((int) $material->booking_id !== (int) $booking->id) {
            abort(404);
        }

        $denied = $this->guardManage($booking);
        if ($denied) {
            return $denied;
        }

        if ($material->receipt_photo_path) {
            Storage::disk('public')->delete($material->receipt_photo_path);
        }
        $material->delete();

        BookingMaterial::recalcForBooking($booking->fresh());

        return response()->json([
            'success'         => true,
            'materials_total' => (float) $booking->fresh()->materials_total,
            'invoice_total'   => $booking->fresh()->invoice_total,
        ]);
    }

    /**
     * Worker must own the booking and the job must still be active;
     * admins bypass the status lock.
     */
    private function guardManage(Booking $booking): ?JsonResponse
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            return null;
        }

        if ($booking->worker_id !== $user->id) {
            abort(403);
        }

        if (!in_array($booking->status, Booking::MATERIALS_EDITABLE_STATUSES, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Materials can no longer be edited for this booking.',
            ], 422);
        }

        return null;
    }

    private function validateItems(Request $request): array
    {
        return $request->validate([
            'name'     => ['required', 'string', 'max:120'],
            'qty'      => ['required', 'numeric', 'min:0.01', 'max:9999'],
            'unit_price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'receipt'  => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'remove_receipt' => ['nullable', 'boolean'],
        ]);
    }

    private function storeReceipt(Request $request): ?string
    {
        return $request->hasFile('receipt')
            ? $request->file('receipt')->store('material-receipts', 'public')
            : null;
    }
}
