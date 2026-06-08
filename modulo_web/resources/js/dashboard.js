import {Chart, registerables} from 'chart.js';

Chart.register(...registerables);

const C = {
    primary: '#10b981',
    primaryDark: '#059669',
    primaryLight: '#34d399',
    secondary: '#64748b',
    accent: '#fbbf24',
    danger: '#ef4444',
};

const MULTI_PALETTE = [
    C.primary, C.accent, C.secondary, C.danger,
    C.primaryLight, C.primaryDark,
    '#8b5cf6', '#f97316', '#06b6d4', '#ec4899',
];

const GRID_COLOR = 'rgba(100,116,139,0.08)';
const INT_TICKS = {stepSize: 1, precision: 0};
const TOOLTIP = {
    backgroundColor: '#0f172a',
    titleColor: '#e2e8f0',
    bodyColor: '#94a3b8',
    padding: 12,
    cornerRadius: 8,
    displayColors: false,
};
const AXIS_STYLE = {
    ticks: {color: C.secondary},
    border: {display: false},
};

const alpha = (hex, a) =>
    hex + Math.round(a * 255).toString(16).padStart(2, '0');


function buildLineChart(canvas, data, {color = C.primary, label = '', unit = ''} = {}) {
    const ctx = canvas.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, canvas.clientHeight || 260);
    gradient.addColorStop(0, alpha(color, 0.28));
    gradient.addColorStop(1, alpha(color, 0.0));

    return new Chart(canvas, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label,
                data: data.values,
                borderColor: color,
                backgroundColor: gradient,
                pointBackgroundColor: '#fff',
                pointBorderColor: color,
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                borderWidth: 2.5,
                fill: true,
                tension: 0.4,
                spanGaps: false,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {duration: 400, easing: 'easeOutQuart'},
            plugins: {
                legend: {display: false},
                tooltip: {
                    ...TOOLTIP,
                    callbacks: {
                        label: ctx => `  ${ctx.parsed.y !== null ? ctx.parsed.y : '—'} ${unit}`,
                    },
                },
            },
            scales: {
                x: {
                    grid: {display: false}, ...AXIS_STYLE,
                    ticks: {...AXIS_STYLE.ticks, maxRotation: 45, minRotation: 0}
                },
                y: {
                    beginAtZero: true,
                    ticks: {...INT_TICKS, color: C.secondary},
                    grid: {color: GRID_COLOR},
                    border: {display: false}
                },
            },
        },
    });
}

function buildWeightChart(canvas, data) {
    const ctx = canvas.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, canvas.clientHeight || 260);
    gradient.addColorStop(0, alpha(C.accent, 0.28));
    gradient.addColorStop(1, alpha(C.accent, 0.0));

    return new Chart(canvas, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Peso médio (kg)',
                data: data.values,
                borderColor: C.accent,
                backgroundColor: gradient,
                pointBackgroundColor: '#fff',
                pointBorderColor: C.accent,
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
                borderWidth: 2.5,
                fill: true,
                tension: 0.4,
                spanGaps: false,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {duration: 600, easing: 'easeOutQuart'},
            plugins: {
                legend: {display: false},
                tooltip: {
                    ...TOOLTIP,
                    callbacks: {
                        label: ctx => ctx.parsed.y !== null
                            ? `  ${ctx.parsed.y.toFixed(1).replace('.', ',')} kg`
                            : '  Sem dados',
                    },
                },
            },
            scales: {
                x: {
                    grid: {display: false}, ...AXIS_STYLE,
                    ticks: {...AXIS_STYLE.ticks, maxRotation: 45, minRotation: 0}
                },
                y: {
                    ticks: {color: C.secondary, callback: v => `${v} kg`},
                    grid: {color: GRID_COLOR},
                    border: {display: false},
                },
            },
        },
    });
}

function buildDoughnutChart(canvas, data) {
    return new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.values,
                backgroundColor: MULTI_PALETTE.slice(0, data.values.length),
                borderWidth: 3,
                borderColor: '#ffffff',
                hoverOffset: 8,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            animation: {duration: 700, easing: 'easeOutQuart'},
            plugins: {
                legend: {
                    position: 'right',
                    labels: {boxWidth: 12, boxHeight: 12, padding: 16, font: {size: 12}, color: '#0f172a'},
                },
                tooltip: {
                    ...TOOLTIP,
                    callbacks: {
                        label: ctx => `  ${ctx.label}: ${ctx.parsed} aplicações`,
                    },
                },
            },
        },
    });
}

function buildBarChart(canvas, data, {color = C.primary, unit = 'animais', indexAxis = 'x'} = {}) {
    const isH = indexAxis === 'y';
    return new Chart(canvas, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.values,
                backgroundColor: alpha(color, 0.80),
                hoverBackgroundColor: color,
                borderWidth: 0,
                borderRadius: 8,
                borderSkipped: false,
            }],
        },
        options: {
            indexAxis,
            responsive: true,
            maintainAspectRatio: false,
            animation: {duration: 600, easing: 'easeOutQuart'},
            plugins: {
                legend: {display: false},
                tooltip: {
                    ...TOOLTIP,
                    callbacks: {
                        label: ctx => `  ${isH ? ctx.parsed.x : ctx.parsed.y} ${unit}`,
                    },
                },
            },
            scales: {
                x: isH
                    ? {
                        beginAtZero: true,
                        ticks: {...INT_TICKS, color: C.secondary},
                        grid: {color: GRID_COLOR},
                        border: {display: false}
                    }
                    : {grid: {display: false}, ...AXIS_STYLE},
                y: isH
                    ? {grid: {display: false}, ...AXIS_STYLE}
                    : {
                        beginAtZero: true,
                        ticks: {...INT_TICKS, color: C.secondary},
                        grid: {color: GRID_COLOR},
                        border: {display: false}
                    },
            },
        },
    });
}

function buildWeightByVaccineChart(canvas, data) {
    const colors = MULTI_PALETTE.slice(0, data.values.length);
    return new Chart(canvas, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.values,
                backgroundColor: colors.map(c => alpha(c, 0.80)),
                hoverBackgroundColor: colors,
                borderWidth: 0,
                borderRadius: 8,
                borderSkipped: false,
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            animation: {duration: 600, easing: 'easeOutQuart'},
            plugins: {
                legend: {display: false},
                tooltip: {
                    ...TOOLTIP,
                    callbacks: {
                        label: ctx => `  ${ctx.parsed.x.toFixed(1).replace('.', ',')} kg`,
                    },
                },
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {color: C.secondary, callback: v => `${v} kg`},
                    grid: {color: GRID_COLOR},
                    border: {display: false},
                },
                y: {grid: {display: false}, ...AXIS_STYLE},
            },
        },
    });
}

function buildRadarChart(canvas, data) {
    const gradient = canvas.getContext('2d').createLinearGradient(0, 0, 0, canvas.clientHeight || 260);
    gradient.addColorStop(0, alpha(C.primary, 0.35));
    gradient.addColorStop(1, alpha(C.primary, 0.05));

    return new Chart(canvas, {
        type: 'radar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Vacinações',
                data: data.values,
                borderColor: C.primary,
                backgroundColor: gradient,
                pointBackgroundColor: C.primary,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                borderWidth: 2,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {duration: 700, easing: 'easeOutQuart'},
            plugins: {
                legend: {display: false},
                tooltip: {
                    ...TOOLTIP,
                    callbacks: {
                        label: ctx => `  ${ctx.parsed.r} vacinações`,
                    },
                },
            },
            scales: {
                r: {
                    beginAtZero: true,
                    ticks: {stepSize: 1, precision: 0, color: C.secondary, backdropColor: 'transparent'},
                    grid: {color: GRID_COLOR},
                    angleLines: {color: GRID_COLOR},
                    pointLabels: {color: C.secondary, font: {size: 12}},
                },
            },
        },
    });
}

function buildStackedBarChart(canvas, data) {
    const datasets = data.datasets.map((ds, i) => ({
        label: ds.label,
        data: ds.values,
        backgroundColor: alpha(MULTI_PALETTE[i % MULTI_PALETTE.length], 0.82),
        hoverBackgroundColor: MULTI_PALETTE[i % MULTI_PALETTE.length],
        borderWidth: 0,
        borderRadius: 4,
        borderSkipped: false,
    }));

    return new Chart(canvas, {
        type: 'bar',
        data: {labels: data.labels, datasets},
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {duration: 600, easing: 'easeOutQuart'},
            plugins: {
                legend: {
                    position: 'top',
                    labels: {boxWidth: 12, boxHeight: 12, padding: 16, font: {size: 12}, color: '#0f172a'},
                },
                tooltip: {
                    ...TOOLTIP,
                    displayColors: true,
                    callbacks: {
                        label: ctx => `  ${ctx.dataset.label}: ${ctx.parsed.y} vacinas`,
                    },
                },
            },
            scales: {
                x: {stacked: true, grid: {display: false}, ...AXIS_STYLE},
                y: {
                    stacked: true,
                    beginAtZero: true,
                    ticks: {...INT_TICKS, color: C.secondary},
                    grid: {color: GRID_COLOR},
                    border: {display: false},
                },
            },
        },
    });
}


function initPeriodFilter(periods, chart) {
    const buttons = document.querySelectorAll('.period-btn');
    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            const period = btn.dataset.period;
            const data = periods[period];
            if (!data) return;

            // Update active button
            buttons.forEach(b => b.classList.remove('period-btn--active'));
            btn.classList.add('period-btn--active');

            // Update chart data
            chart.data.labels = data.labels;
            chart.data.datasets[0].data = data.values;
            chart.update('active');
        });
    });
}


function initRecentSearch() {
    const input = document.getElementById('recent-search');
    const table = document.getElementById('recent-table');
    if (!input || !table) return;

    const rows = Array.from(table.querySelectorAll('tbody tr'));

    input.addEventListener('input', () => {
        const q = input.value.toLowerCase().trim();
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = q === '' || text.includes(q) ? '' : 'none';
        });
    });
}


function initDashboardCharts() {
    const raw = window.__dashboardData;
    if (!raw) return;

    Chart.defaults.font.family = "'Outfit', sans-serif";
    Chart.defaults.color = C.secondary;

    const el = id => document.getElementById(id);

    // Monthly vaccinations — starts at 12m, period buttons switch dataset
    const lineCanvas = el('chart-monthly-vaccinations');
    if (lineCanvas) {
        const lineChart = buildLineChart(lineCanvas, raw.periods['12m'], {label: 'Vacinações', unit: 'vacinações'});
        initPeriodFilter(raw.periods, lineChart);
    }

    if (el('chart-vaccine-types'))
        buildDoughnutChart(el('chart-vaccine-types'), raw.vaccineTypes);

    if (el('chart-weight-evolution'))
        buildWeightChart(el('chart-weight-evolution'), raw.weightEvolution);

    if (el('chart-cattle-per-vet'))
        buildBarChart(el('chart-cattle-per-vet'), raw.cattlePerVet, {unit: 'animais'});

    if (el('chart-vaccines-per-workstation'))
        buildBarChart(el('chart-vaccines-per-workstation'), raw.vaccinesPerWorkstation, {
            color: C.accent,
            unit: 'vacinas',
            indexAxis: 'y'
        });

    if (el('chart-weight-by-vaccine'))
        buildWeightByVaccineChart(el('chart-weight-by-vaccine'), raw.weightByVaccineType);

    if (el('chart-weight-by-workstation'))
        buildWeightByVaccineChart(el('chart-weight-by-workstation'), raw.weightByWorkstation);

    if (el('chart-seasonal'))
        buildRadarChart(el('chart-seasonal'), raw.seasonalVaccinations);

    if (el('chart-vaccine-type-by-workstation'))
        buildStackedBarChart(el('chart-vaccine-type-by-workstation'), raw.vaccineTypeByWorkstation);

    initRecentSearch();
}


function initAnimalCharts() {
    const raw = window.__animalData;
    if (!raw) return;

    Chart.defaults.font.family = "'Outfit', sans-serif";
    Chart.defaults.color = C.secondary;

    const el = id => document.getElementById(id);

    if (el('chart-animal-weight'))
        buildWeightChart(el('chart-animal-weight'), raw.weightOverTime);

    if (el('chart-animal-vaccines'))
        buildDoughnutChart(el('chart-animal-vaccines'), raw.vaccineTypes);
}


function initVaccineTypeCharts() {
    const raw = window.__vaccineTypeData;
    if (!raw) return;

    Chart.defaults.font.family = "'Outfit', sans-serif";
    Chart.defaults.color = C.secondary;

    const el = id => document.getElementById(id);

    if (el('chart-vt-monthly'))
        buildBarChart(el('chart-vt-monthly'), raw.monthly, {unit: 'aplicações'});

    if (el('chart-vt-coverage'))
        buildDoughnutChart(el('chart-vt-coverage'), raw.coverage);

    if (el('chart-vt-weight'))
        buildWeightChart(el('chart-vt-weight'), raw.weight);
}


if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initDashboardCharts();
        initAnimalCharts();
        initVaccineTypeCharts();
    });
} else {
    initDashboardCharts();
    initAnimalCharts();
    initVaccineTypeCharts();
}
