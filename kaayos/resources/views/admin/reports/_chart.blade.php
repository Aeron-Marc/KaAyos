@once
<script>
window.renderKaAyosReportChart = function (canvasId, data, opts) {
    opts = opts || {};
    var el = document.getElementById(canvasId);
    if (!el || !data || !data.labels || !data.labels.length || typeof Chart === 'undefined') return null;

    Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
    Chart.defaults.color = '#6B7280';

    var isDonut = data.type === 'doughnut';
    var isLine = data.type === 'line';

    var datasets = data.datasets.map(function (ds, index) {
        if (isDonut) {
            return { data: ds.data, backgroundColor: ds.colors, borderWidth: 2, borderColor: '#fff' };
        }
        return {
            label: ds.label,
            data: ds.data,
            borderColor: ds.color,
            backgroundColor: isLine ? ds.color + '26' : ds.color,
            borderWidth: 2,
            fill: isLine,
            tension: isLine ? .35 : 0,
            pointRadius: isLine ? 2 : 0,
            yAxisID: index === 1 ? 'y1' : 'y'
        };
    });

    var options = {
        responsive: true,
        maintainAspectRatio: false,
        animation: opts.animation === false ? false : undefined,
        interaction: { mode: 'index', intersect: false }
    };

    if (isDonut) {
        options.cutout = '68%';
        options.plugins = {
            legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 12, font: { size: 11 } } }
        };
    } else {
        options.scales = {
            x: { grid: { display: false } },
            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.04)' } }
        };
        if (data.datasets.length > 1) {
            options.scales.y1 = { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } };
        }
        options.plugins = { legend: { display: data.datasets.length > 1, position: 'top', labels: { usePointStyle: true, boxWidth: 8, padding: 14, font: { size: 11 } } } };
    }

    var chart = new Chart(el, { type: isDonut ? 'doughnut' : data.type, data: { labels: data.labels, datasets: datasets }, options: options });

    if (typeof opts.onRendered === 'function') {
        requestAnimationFrame(function () { opts.onRendered(chart); });
    }

    return chart;
};
</script>
@endonce
