<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExportReportRequest;
use App\Http\Requests\Admin\PrintReportRequest;
use App\Services\ReportService;
use App\Services\XlsxWriter;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public const PRESETS = [
        'today' => 'Today',
        '7d' => 'Last 7 Days',
        '30d' => 'Last 30 Days',
        'this_month' => 'This Month',
        'last_month' => 'Last Month',
        'this_year' => 'This Year',
        'all' => 'All Time',
    ];

    public function __construct(private ReportService $reports) {}

    public function index(Request $request)
    {
        $type = $request->input('type');
        $type = $type && in_array($type, ReportService::REPORT_KEYS, true) ? $type : null;

        [$from, $to, $preset] = $this->resolveRange($request);

        $preview = null;
        if ($type) {
            $preview = $this->reports->build($type, $from, $to, 100);
        }

        return view('admin.reports.index', [
            'groups' => $this->reports->groups(),
            'presets' => self::PRESETS,
            'type' => $type,
            'preset' => $preset,
            'from' => $from,
            'to' => $to,
            'preview' => $preview,
            'meta' => $type ? $this->reports->catalog()[$type] : null,
        ]);
    }

    public function export(ExportReportRequest $request)
    {
        $type = $request->input('type');
        $format = $request->input('format', 'csv');
        $from = $request->input('date_from');
        $to = $request->input('date_to');

        $data = $this->reports->build($type, $from, $to);

        $fileName = str_replace('_', '-', $type)."_report_{$from}_to_{$to}.{$format}";

        if ($format === 'xlsx') {
            $label = $this->reports->catalog()[$type]['label'];

            $binary = XlsxWriter::binary($label, $data['columns'], $data['rows'], [
                'title' => $label,
                'period' => "{$from} to {$to}",
            ]);

            return response()->streamDownload(function () use ($binary) {
                echo $binary;
            }, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        return response()->streamDownload(function () use ($data) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, $data['columns']);
            foreach ($data['rows'] as $row) {
                fputcsv($output, array_map(fn ($value) => $value === null ? '' : $value, $row));
            }
            fclose($output);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function print(PrintReportRequest $request)
    {
        $type = $request->input('type');
        $from = $request->input('date_from');
        $to = $request->input('date_to');

        $data = $this->reports->build($type, $from, $to);

        return view('admin.reports.print', [
            'catalog' => $this->reports->catalog()[$type],
            'type' => $type,
            'from' => $from,
            'to' => $to,
            'data' => $data,
            'generated_at' => now()->format('F d, Y h:i A'),
        ]);
    }

    private function resolveRange(Request $request): array
    {
        $preset = $request->input('preset');
        if ($preset && array_key_exists($preset, self::PRESETS)) {
            return array_merge($this->rangeForPreset($preset), [$preset]);
        }

        $from = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->input('date_to', now()->format('Y-m-d'));

        return [$from, $to, null];
    }

    private function rangeForPreset(string $preset): array
    {
        return match ($preset) {
            'today' => [now()->format('Y-m-d'), now()->format('Y-m-d')],
            '7d' => [now()->subDays(6)->format('Y-m-d'), now()->format('Y-m-d')],
            '30d' => [now()->subDays(29)->format('Y-m-d'), now()->format('Y-m-d')],
            'this_month' => [now()->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')],
            'last_month' => [now()->subMonth()->startOfMonth()->format('Y-m-d'), now()->subMonth()->endOfMonth()->format('Y-m-d')],
            'this_year' => [now()->startOfYear()->format('Y-m-d'), now()->format('Y-m-d')],
            'all' => ['2000-01-01', now()->format('Y-m-d')],
            default => [now()->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')],
        };
    }
}
