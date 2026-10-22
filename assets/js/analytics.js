/**
 * ezyTailor - Chart.js Business Analytics Engine
 */

const Analytics = {
    revenueChart: null,
    categoryChart: null,
    paymentChart: null,
    currentRange: 'month',

    init() {
        this.bindEvents();
        this.loadData(this.currentRange);
    },

    bindEvents() {
        // Time filter buttons
        document.querySelectorAll('[data-analytics-range]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                document.querySelectorAll('[data-analytics-range]').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                this.currentRange = btn.getAttribute('data-analytics-range');
                this.loadData(this.currentRange);
            });
        });
    },

    loadData(range) {
        fetch((window.APP_BASE_URL || '') + `/api/analytics.php?range=${range}`)
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    this.renderMetrics(res.metrics);
                    this.renderCharts(res.charts);
                }
            })
            .catch(err => console.error(err));
    },

    renderMetrics(m) {
        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.textContent = val;
        };

        setVal('stat_total_value', 'Rs ' + Number(m.total_order_value).toLocaleString());
        setVal('stat_total_received', 'Rs ' + Number(m.total_received).toLocaleString());
        setVal('stat_total_unpaid', 'Rs ' + Number(m.total_unpaid).toLocaleString());
        setVal('stat_total_orders', m.total_orders);
        setVal('stat_ready_orders', m.ready_orders_count || 0);
        setVal('stat_active_customers', m.active_customers_count || 0);

        // Progress percentage
        const progressEl = document.getElementById('stat_collection_progress');
        if (progressEl) {
            const percent = (m.total_order_value > 0) ? Math.round((m.total_received / m.total_order_value) * 100) : 0;
            progressEl.style.width = percent + '%';
            const percentText = document.getElementById('stat_collection_percent');
            if (percentText) percentText.textContent = percent + '% Collected';
        }
    },

    renderCharts(data) {
        // Theme color extraction
        const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim() || '#5C6B50';

        // 1. Revenue & Orders Trend
        const revCanvas = document.getElementById('revenueChartCanvas');
        if (revCanvas) {
            if (this.revenueChart) this.revenueChart.destroy();

            this.revenueChart = new Chart(revCanvas, {
                type: 'line',
                data: {
                    labels: data.revenue_trend.labels,
                    datasets: [
                        {
                            label: 'Revenue (Rs)',
                            data: data.revenue_trend.revenue,
                            borderColor: primaryColor,
                            backgroundColor: 'rgba(92, 107, 80, 0.12)',
                            tension: 0.35,
                            fill: true,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Orders Count',
                            data: data.revenue_trend.orders,
                            borderColor: '#D97706',
                            backgroundColor: '#D97706',
                            type: 'bar',
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            grid: { color: 'rgba(0,0,0,0.05)' }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            grid: { drawOnChartArea: false },
                            ticks: { precision: 0 }
                        }
                    }
                }
            });
        }

        // 2. Garment Categories Pie / Donut
        const catCanvas = document.getElementById('categoryChartCanvas');
        if (catCanvas) {
            if (this.categoryChart) this.categoryChart.destroy();

            this.categoryChart = new Chart(catCanvas, {
                type: 'doughnut',
                data: {
                    labels: data.categories.labels,
                    datasets: [{
                        data: data.categories.counts,
                        backgroundColor: [
                            '#5C6B50', '#1E3A8A', '#D97706', '#0D5C75', '#7B1113', '#6366F1', '#10B981'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }

        // 3. Payment Status Pie
        const payCanvas = document.getElementById('paymentChartCanvas');
        if (payCanvas) {
            if (this.paymentChart) this.paymentChart.destroy();

            this.paymentChart = new Chart(payCanvas, {
                type: 'pie',
                data: {
                    labels: data.payment_status.labels,
                    datasets: [{
                        data: data.payment_status.counts,
                        backgroundColor: ['#10B981', '#F59E0B', '#EF4444']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('revenueChartCanvas')) {
        Analytics.init();
    }
});
