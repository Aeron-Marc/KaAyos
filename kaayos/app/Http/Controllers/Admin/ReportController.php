<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExportReportRequest;
use App\Models\Booking;
use App\Models\User;
use App\Models\WorkerDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $reportData = null;
        $reportType = $request->input('type', 'bookings');
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        if ($request->anyFilled(['type', 'date_from', 'date_to'])) {
            $reportData = match ($reportType) {
                'bookings' => $this->bookingsReport($dateFrom, $dateTo),
                'payments' => $this->paymentsReport($dateFrom, $dateTo),
                'verifications' => $this->verificationsReport($dateFrom, $dateTo),
                default => null,
            };
        }

        return view('admin.reports.index', compact('reportData', 'reportType', 'dateFrom', 'dateTo'));
    }

    public function export(ExportReportRequest $request)
    {
        $type = $request->input('type');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $data = match ($type) {
            'bookings' => $this->bookingsReport($dateFrom, $dateTo),
            'payments' => $this->paymentsReport($dateFrom, $dateTo),
            'verifications' => $this->verificationsReport($dateFrom, $dateTo),
            default => [],
        };

        $fileName = "{$type}_report_{$dateFrom}_to_{$dateTo}.csv";

        return response()->streamDownload(function () use ($data, $type) {
            $output = fopen('php://output', 'w');

            if ($type === 'bookings' && isset($data['rows'])) {
                fputcsv($output, ['ID', 'Client', 'Worker', 'Service', 'Status', 'Price', 'Scheduled', 'Created']);
                foreach ($data['rows'] as $row) {
                    fputcsv($output, $row);
                }
            } elseif ($type === 'payments' && isset($data['rows'])) {
                fputcsv($output, ['Booking ID', 'Client', 'Worker', 'Price', 'Completed At']);
                foreach ($data['rows'] as $row) {
                    fputcsv($output, $row);
                }
            } elseif ($type === 'verifications' && isset($data['rows'])) {
                fputcsv($output, ['Provider', 'Email', 'Document Type', 'Status', 'Reviewed At']);
                foreach ($data['rows'] as $row) {
                    fputcsv($output, $row);
                }
            }

            fclose($output);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }

    private function bookingsReport($dateFrom, $dateTo): array
    {
        $bookings = Booking::with(['client', 'worker'])
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->latest()
            ->get();

        return [
            'summary' => [
                'total' => $bookings->count(),
                'completed' => $bookings->where('status', 'completed')->count(),
                'cancelled' => $bookings->where('status', 'cancelled')->count(),
                'active' => $bookings->whereIn('status', ['pending', 'confirmed', 'in_progress'])->count(),
            ],
            'rows' => $bookings->map(fn ($b) => [
                $b->id,
                $b->client->name ?? 'N/A',
                $b->worker->name ?? 'N/A',
                $b->service_category,
                $b->status,
                number_format((float) $b->price, 2),
                $b->scheduled_at?->format('Y-m-d H:i'),
                $b->created_at->format('Y-m-d H:i'),
            ])->toArray(),
        ];
    }

    private function paymentsReport($dateFrom, $dateTo): array
    {
        $completed = Booking::with(['client', 'worker'])
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->latest()
            ->get();

        return [
            'summary' => [
                'total_completed' => $completed->count(),
                'total_revenue' => $completed->sum('price'),
                'average_booking_value' => $completed->avg('price'),
            ],
            'rows' => $completed->map(fn ($b) => [
                $b->id,
                $b->client->name ?? 'N/A',
                $b->worker->name ?? 'N/A',
                number_format((float) $b->price, 2),
                $b->completed_at?->format('Y-m-d H:i'),
            ])->toArray(),
        ];
    }

    private function verificationsReport($dateFrom, $dateTo): array
    {
        $docs = WorkerDocument::with(['user', 'reviewedBy'])
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->latest()
            ->get();

        return [
            'summary' => [
                'total' => $docs->count(),
                'pending' => $docs->where('status', 'pending')->count(),
                'verified' => $docs->where('status', 'verified')->count(),
                'rejected' => $docs->where('status', 'rejected')->count(),
            ],
            'rows' => $docs->map(fn ($d) => [
                $d->user->name ?? 'N/A',
                $d->user->email ?? 'N/A',
                $d->document_type,
                $d->status,
                $d->reviewed_at?->format('Y-m-d H:i'),
            ])->toArray(),
        ];
    }
}
