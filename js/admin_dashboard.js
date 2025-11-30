/**
 * Admin Platform Dashboard JavaScript
 * Handles Chart.js visualizations for analytics
 */

// Chart data will be set via inline script
let bookingTrends, statusBreakdown, regionalStats, categoryPerformance, regionalRevenue;

// Initialize chart data from window object
function initChartData() {
    if (window.dashboardData) {
        bookingTrends = window.dashboardData.bookingTrends;
        statusBreakdown = window.dashboardData.statusBreakdown;
        regionalStats = window.dashboardData.regionalStats;
        categoryPerformance = window.dashboardData.categoryPerformance;
        regionalRevenue = window.dashboardData.regionalRevenue;
    }
}

function initializeCharts() {
    // Booking Trends Chart
    const bookingCtx = document.getElementById('bookingChart');
    if (!bookingCtx) return;
    
    const bookingData = bookingTrends || [];
    const labels = bookingData.map(item => {
        const [year, month] = item.month.split('-');
        const date = new Date(year, month - 1);
        return date.toLocaleDateString('en-US', { month: 'short', year: '2-digit' });
    });
    const bookings = bookingData.map(item => item.bookings);

    new Chart(bookingCtx.getContext('2d'), {
        type: 'line',
        data: {
            labels: labels.length ? labels : ['No data'],
            datasets: [{
                label: 'Bookings',
                data: bookings.length ? bookings : [0],
                borderColor: '#1b4332',
                backgroundColor: 'rgba(27, 67, 50, 0.1)',
                tension: 0.4,
                fill: true,
                borderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Bookings: ' + context.parsed.y;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    },
                    title: {
                        display: true,
                        text: 'Number of Bookings'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Month'
                    }
                }
            }
        }
    });

    // Revenue Trends Chart
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        const revenue = bookingData.map(item => parseFloat(item.revenue || 0));

        new Chart(revenueCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels.length ? labels : ['No data'],
                datasets: [{
                    label: 'Revenue (GHS)',
                    data: revenue.length ? revenue : [0],
                    backgroundColor: 'rgba(27, 67, 50, 0.8)',
                    borderColor: '#1b4332',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Revenue: GHS ' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'GHS ' + value.toLocaleString();
                            }
                        },
                        title: {
                            display: true,
                            text: 'Revenue (GHS)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    }
                }
            }
        });
    }

    // Booking Status Breakdown Pie Chart
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        const statusData = statusBreakdown || {};
        const statusLabels = Object.keys(statusData);
        const statusCounts = Object.values(statusData);
        const totalStatus = statusCounts.reduce((a, b) => a + b, 0);
        const statusColors = {
            'pending': '#fef3c7',
            'confirmed': '#dbeafe',
            'completed': '#dcfce7',
            'cancelled': '#fee2e2',
            'in_progress': '#f3e8ff'
        };

        new Chart(statusCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: statusLabels.map(s => s.charAt(0).toUpperCase() + s.slice(1)),
                datasets: [{
                    data: statusCounts,
                    backgroundColor: statusLabels.map(s => statusColors[s] || '#e2e8f0'),
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 12,
                            font: {
                                size: 12
                            },
                            generateLabels: function(chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    return data.labels.map((label, i) => {
                                        const value = data.datasets[0].data[i];
                                        const percent = totalStatus > 0 ? ((value / totalStatus) * 100).toFixed(1) : 0;
                                        return {
                                            text: label + ': ' + value + ' (' + percent + '%)',
                                            fillStyle: data.datasets[0].backgroundColor[i],
                                            strokeStyle: data.datasets[0].borderColor,
                                            lineWidth: data.datasets[0].borderWidth,
                                            hidden: false,
                                            index: i
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const percent = totalStatus > 0 ? ((value / totalStatus) * 100).toFixed(1) : 0;
                                return label + ': ' + value + ' bookings (' + percent + '% of total)';
                            }
                        }
                    },
                }
            }
        });
    }

    // Regional Provider Distribution Chart
    const regionalCtx = document.getElementById('regionalChart');
    if (regionalCtx) {
        const regionalData = regionalStats || [];
        const regionalLabels = regionalData.map(r => r.region);
        const regionalCounts = regionalData.map(r => parseInt(r.provider_count));
        const totalRegional = regionalCounts.reduce((a, b) => a + b, 0);
        const regionalColors = ['#1b4332', '#2d6a4f', '#40916c', '#52b788', '#74c69d'];

        new Chart(regionalCtx.getContext('2d'), {
            type: 'pie',
            data: {
                labels: regionalLabels,
                datasets: [{
                    data: regionalCounts,
                    backgroundColor: regionalColors.slice(0, regionalLabels.length),
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 12,
                            font: {
                                size: 12
                            },
                            generateLabels: function(chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    return data.labels.map((label, i) => {
                                        const value = data.datasets[0].data[i];
                                        const percent = totalRegional > 0 ? ((value / totalRegional) * 100).toFixed(1) : 0;
                                        return {
                                            text: label + ': ' + value + ' (' + percent + '%)',
                                            fillStyle: data.datasets[0].backgroundColor[i],
                                            strokeStyle: data.datasets[0].borderColor,
                                            lineWidth: data.datasets[0].borderWidth,
                                            hidden: false,
                                            index: i
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const percent = totalRegional > 0 ? ((value / totalRegional) * 100).toFixed(1) : 0;
                                return label + ': ' + value + ' providers (' + percent + '% of total)';
                            }
                        }
                    },
                }
            }
        });
    }

    // Service Category Performance Chart
    const categoryCtx = document.getElementById('categoryChart');
    if (categoryCtx) {
        const categoryData = categoryPerformance || [];
        const categoryLabels = categoryData.map(c => c.category_name);
        const categoryBookings = categoryData.map(c => parseInt(c.booking_count || 0));
        const categoryServices = categoryData.map(c => parseInt(c.service_count || 0));
        const categoryRevenue = categoryData.map(c => parseFloat(c.revenue || 0));

        new Chart(categoryCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: categoryLabels,
                datasets: [{
                    label: 'Bookings',
                    data: categoryBookings,
                    backgroundColor: 'rgba(27, 67, 50, 0.8)',
                    borderColor: '#1b4332',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            afterLabel: function(context) {
                                const index = context.dataIndex;
                                return [
                                    'Services: ' + categoryServices[index],
                                    'Revenue: GHS ' + categoryRevenue[index].toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})
                                ];
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            callback: function(value) {
                                return value + ' bookings';
                            }
                        },
                        title: {
                            display: true,
                            text: 'Number of Bookings'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Service Categories'
                        }
                    }
                }
            }
        });
    }

    // Regional Revenue Breakdown Chart
    const regionalRevenueCtx = document.getElementById('regionalRevenueChart');
    if (regionalRevenueCtx) {
        const regionalRevenueData = regionalRevenue || [];
        const regionalRevenueLabels = regionalRevenueData.map(r => r.region);
        const regionalRevenueAmounts = regionalRevenueData.map(r => parseFloat(r.revenue || 0));
        const regionalBookingCounts = regionalRevenueData.map(r => parseInt(r.booking_count || 0));
        const regionalProviderCounts = regionalRevenueData.map(r => parseInt(r.provider_count || 0));
        const totalRevenue = regionalRevenueAmounts.reduce((a, b) => a + b, 0);

        new Chart(regionalRevenueCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: regionalRevenueLabels,
                datasets: [{
                    label: 'Revenue (GHS)',
                    data: regionalRevenueAmounts,
                    backgroundColor: 'rgba(27, 67, 50, 0.8)',
                    borderColor: '#1b4332',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            afterLabel: function(context) {
                                const index = context.dataIndex;
                                const percent = totalRevenue > 0 ? ((regionalRevenueAmounts[index] / totalRevenue) * 100).toFixed(1) : 0;
                                return [
                                    'Providers: ' + regionalProviderCounts[index],
                                    'Bookings: ' + regionalBookingCounts[index],
                                    'Share: ' + percent + '% of total revenue'
                                ];
                            },
                            label: function(context) {
                                return 'Revenue: GHS ' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'GHS ' + value.toLocaleString();
                            }
                        },
                        title: {
                            display: true,
                            text: 'Revenue (GHS)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Regions'
                        }
                    }
                }
            }
        });
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initChartData();
    initializeCharts();
});

