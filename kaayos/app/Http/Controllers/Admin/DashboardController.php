<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminDashboardMetrics;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, AdminDashboardMetrics $metrics)
    {
        $period = $request->query('period');
        $queueSort = $this->queueSort($request);
        $data = $metrics->forPeriod(is_string($period) ? $period : null, $queueSort);

        return view('admin.dashboard', [
            'metrics' => $data,
            'period' => $data['period'],
            'targets' => config('dashboard.targets'),
            'updatedAt' => $data['generated_at'],
            'deltas' => $this->deltas($data['kpis']),
            'queueSort' => $data['action_queue']['sort'],
        ]);
    }

    public function exportQueue(Request $request, AdminDashboardMetrics $metrics)
    {
        $period = $request->query('period');
        $data = $metrics->forPeriod(is_string($period) ? $period : null, $this->queueSort($request));
        $items = $data['action_queue']['items'];

        $filename = 'kaayos-action-queue-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($items) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, [
                __('admin.dash.queue.csv.priority'),
                __('admin.dash.queue.csv.type'),
                __('admin.dash.queue.csv.item'),
                __('admin.dash.queue.csv.context'),
                __('admin.dash.queue.csv.status'),
                __('admin.dash.queue.csv.age_minutes'),
                __('admin.dash.queue.csv.url'),
            ]);

            foreach ($items as $item) {
                fputcsv($output, [
                    $item['priority'],
                    $item['type_label'],
                    $item['title'],
                    $item['context'],
                    __('status.'.$item['status']),
                    $item['age_minutes'],
                    $item['href'],
                ]);
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function queueSort(Request $request): string
    {
        $sort = $request->query('queue_sort');

        return is_string($sort) && in_array($sort, ['age', 'priority', 'type'], true) ? $sort : 'age';
    }

    /**
     * Period-over-period deltas for the KPI cards.
     * Rates diff in percentage points, money as absolute currency, counts as absolute.
     *
     * @return array<string, array{dir: string, text: string}|null>
     */
    private function deltas(array $kpi): array
    {
        $formatters = [
            'total_bookings' => fn (float $abs) => sprintf(
                '%s%s',
                $abs > 0 ? '+' : ($abs < 0 ? '-' : ''),
                number_format(abs($abs))
            ),
            'completion_rate' => fn (float $abs) => sprintf(
                '%s%d%%',
                $abs > 0 ? '+' : ($abs < 0 ? '-' : ''),
                (int) round(abs($abs))
            ),
            'cancellation_rate' => fn (float $abs) => sprintf(
                '%s%d%%',
                $abs > 0 ? '+' : ($abs < 0 ? '-' : ''),
                (int) round(abs($abs))
            ),
            'revenue' => fn (float $abs) => sprintf(
                '%s%s%s',
                $abs > 0 ? '+' : ($abs < 0 ? '-' : ''),
                "\u{20B1}",
                number_format(abs($abs), 2)
            ),
            'gross_collected' => fn (float $abs) => sprintf(
                '%s%s%s',
                $abs > 0 ? '+' : ($abs < 0 ? '-' : ''),
                "\u{20B1}",
                number_format(abs($abs), 2)
            ),
        ];

        $out = [];
        foreach ($formatters as $key => $format) {
            $current = $key === 'total_bookings'
                ? ($kpi['total_bookings'] ?? null)
                : ($kpi[$key]['value'] ?? null);
            $previous = $key === 'total_bookings'
                ? ($kpi['previous_total_bookings'] ?? null)
                : ($kpi[$key]['previous'] ?? null);

            if ($current === null || $previous === null) {
                $out[$key] = null;

                continue;
            }

            $abs = (float) $current - (float) $previous;

            $out[$key] = [
                'dir' => abs($abs) < 0.005 ? 'same' : ($abs > 0 ? 'up' : 'down'),
                'text' => $format($abs),
            ];
        }

        return $out;
    }
}
