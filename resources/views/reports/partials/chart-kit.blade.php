{{--
    Shared report scripts: the period picker behaviour and window.ReportCharts,
    a thin Chart.js wrapper that applies the house chart style (fixed categorical
    order, 24px bars with 4px rounded data-ends, recessive grid, legend only for 2+ series).
--}}
<script>
document.querySelectorAll('[data-period-form]').forEach(function (form) {
    const select = form.querySelector('[data-period-select]');
    const custom = form.querySelector('[data-period-custom]');
    select.addEventListener('change', function () {
        if (select.value === 'custom') {
            custom.classList.remove('d-none');
            form.querySelector('input[name="from"]').focus();
            return;
        }
        form.querySelectorAll('input[type="date"]').forEach(function (input) { input.disabled = true; });
        form.submit();
    });
});

document.querySelectorAll('[data-table-filter]').forEach(function (input) {
    const table = document.querySelector(input.dataset.tableFilter);
    if (!table) { return; }
    input.addEventListener('input', function () {
        const q = input.value.toLowerCase().trim();
        table.querySelectorAll('tbody tr').forEach(function (row) {
            row.classList.toggle('d-none', q !== '' && !row.textContent.toLowerCase().includes(q));
        });
    });
});

window.ReportCharts = (function () {
    const palette = ['#2a78d6', '#eb6834', '#1baf7a', '#eda100', '#e87ba4', '#008300', '#6250d6', '#e34948'];
    const status = { good: '#0ca30c', warning: '#fab219', serious: '#ec835a', critical: '#d03b3b' };
    const ink = { muted: '#898781', secondary: '#52514e', grid: '#e1e0d9', baseline: '#c3c2b7', surface: '#ffffff' };

    function compact(v) {
        const a = Math.abs(v);
        if (a >= 1e9) { return (v / 1e9).toFixed(1).replace(/\.0$/, '') + 'B'; }
        if (a >= 1e6) { return (v / 1e6).toFixed(1).replace(/\.0$/, '') + 'M'; }
        if (a >= 1e3) { return (v / 1e3).toFixed(0) + 'K'; }
        return String(v);
    }

    function formatValue(v, money) {
        return money ? 'TSh ' + Math.round(v).toLocaleString('en-US') : Math.round(v).toLocaleString('en-US');
    }

    /**
     * opts: { labels, datasets: [{ label, data, color? }], horizontal, stacked, money }
     */
    function bars(canvas, opts) {
        if (!canvas || !window.Chart) { return null; }
        const horizontal = !!opts.horizontal;
        const stacked = !!opts.stacked;
        const valueAxis = horizontal ? 'x' : 'y';
        const categoryAxis = horizontal ? 'y' : 'x';
        const multi = opts.datasets.length > 1;

        const datasets = opts.datasets.map(function (ds, i) {
            return {
                label: ds.label,
                data: ds.data,
                backgroundColor: ds.color || palette[i],
                maxBarThickness: 24,
                borderRadius: stacked ? 0 : 4,
                borderSkipped: stacked ? false : 'start',
                borderWidth: stacked ? (horizontal ? { right: 2 } : { top: 2 }) : 0,
                borderColor: ink.surface,
            };
        });

        const scales = {};
        scales[categoryAxis] = { stacked: stacked, grid: { display: false }, border: { color: ink.baseline }, ticks: { color: ink.muted, autoSkip: true, maxRotation: 0 } };
        scales[valueAxis] = {
            stacked: stacked,
            beginAtZero: true,
            grid: { color: ink.grid, lineWidth: 1 },
            border: { display: false },
            ticks: { color: ink.muted, precision: 0, callback: function (v) { return opts.money ? compact(v) : (Number.isInteger(v) ? v : null); } },
        };

        return new Chart(canvas, {
            type: 'bar',
            data: { labels: opts.labels, datasets: datasets },
            options: {
                indexAxis: horizontal ? 'y' : 'x',
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false, axis: horizontal ? 'y' : 'x' },
                plugins: {
                    legend: { display: multi, position: 'top', align: 'end', labels: { color: ink.secondary, usePointStyle: true, pointStyle: 'rectRounded', boxWidth: 10 } },
                    tooltip: { callbacks: { label: function (ctx) { return ctx.dataset.label + ': ' + formatValue(horizontal ? ctx.parsed.x : ctx.parsed.y, opts.money); } } },
                },
                scales: scales,
            },
        });
    }

    return { palette: palette, status: status, ink: ink, bars: bars, compact: compact };
})();
</script>
