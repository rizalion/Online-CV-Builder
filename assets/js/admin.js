/**
 * Admin Panel — Chart.js Initialization
 */

document.addEventListener('DOMContentLoaded', () => {
    if (typeof Chart === 'undefined' || !window.adminChartData) return;

    const textColor = getComputedStyle(document.documentElement)
        .getPropertyValue('--text-secondary').trim() || '#64748b';
    const gridColor = getComputedStyle(document.documentElement)
        .getPropertyValue('--border-color').trim() || '#e2e8f0';

    // Downloads Over Time — Line Chart
    const dlCtx = document.getElementById('downloadsChart');
    if (dlCtx) {
        new Chart(dlCtx, {
            type: 'line',
            data: {
                labels: window.adminChartData.downloadsOverTime.labels,
                datasets: [{
                    label: 'Downloads',
                    data: window.adminChartData.downloadsOverTime.data,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: '#6366f1',
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    x: {
                        ticks: { color: textColor, font: { size: 11 } },
                        grid: { color: gridColor },
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { color: textColor, font: { size: 11 }, stepSize: 1 },
                        grid: { color: gridColor },
                    },
                },
            },
        });
    }

    // By Template — Doughnut Chart
    const tplCtx = document.getElementById('templateChart');
    if (tplCtx) {
        new Chart(tplCtx, {
            type: 'doughnut',
            data: {
                labels: window.adminChartData.byTemplate.labels,
                datasets: [{
                    data: window.adminChartData.byTemplate.data,
                    backgroundColor: ['#6366f1', '#0d9488', '#f97316', '#ec4899', '#8b5cf6'],
                    borderWidth: 0,
                    hoverOffset: 8,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: textColor,
                            font: { size: 12 },
                            padding: 16,
                            usePointStyle: true,
                        },
                    },
                },
            },
        });
    }
});
