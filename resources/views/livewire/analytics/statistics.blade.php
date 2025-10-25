<div class="space-y-8" x-data x-init="$nextTick(() => {
    // Ensure charts load when Alpine component initializes
    if (typeof initializeCharts === 'function') {
        setTimeout(() => initializeCharts(), 150);
    }
})">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Analytics & Statistics</h1>
        <p class="text-gray-600 dark:text-gray-300 mt-2">Comprehensive insights into member sobriety journey data</p>
    </div>

    <!-- Sobriety Dates by Month Chart -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Sobriety Dates by Month</h2>
        <div class="h-80">
            <canvas id="sobrietyDatesChart" width="400" height="200"></canvas>
        </div>
    </div>

    <!-- Longest Sobriety Chart -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Longest Sobriety (Top 10)</h2>
        <div class="h-80">
            <canvas id="sobrietyChart" width="400" height="200"></canvas>
        </div>
    </div>

    <!-- Members with Multiple Sobriety Dates (Relapses) Chart -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Members with Multiple Sobriety Dates</h2>
        <div class="h-80">
            <canvas id="relapsesChart" width="400" height="200"></canvas>
        </div>
    </div>

    <!-- Sobriety Date Distribution Chart -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Relapse Distribution</h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Percentage of members with single vs multiple sobriety dates</p>
        <div class="h-80">
            <canvas id="distributionChart" width="400" height="200"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function initializeCharts() {
            // Sobriety Dates by Month Chart
            const sobrietyDatesCtx = document.getElementById('sobrietyDatesChart').getContext('2d');
            const sobrietyDatesData = @json($sobrietyDatesByMonth);

            new Chart(sobrietyDatesCtx, {
                type: 'bar',
                data: {
                    labels: Object.keys(sobrietyDatesData),
                    datasets: [{
                        label: 'Number of Sobriety Dates',
                        data: Object.values(sobrietyDatesData),
                        backgroundColor: 'rgba(239, 68, 68, 0.6)',
                        borderColor: 'rgba(239, 68, 68, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Distribution of Sobriety Start Dates Throughout the Year'
                        }
                    }
                }
            });

            // Longest Sobriety Chart
            const sobrietyCtx = document.getElementById('sobrietyChart').getContext('2d');
            const sobrietyData = @json($longestSobriety);

            new Chart(sobrietyCtx, {
                type: 'bar',
                data: {
                    labels: sobrietyData.map(item => item.name),
                    datasets: [{
                        label: 'Years Sober',
                        data: sobrietyData.map(item => item.years),
                        backgroundColor: 'rgba(34, 197, 94, 0.6)',
                        borderColor: 'rgba(34, 197, 94, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value + ' years';
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Members with Longest Sobriety (Years)'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const item = sobrietyData[context.dataIndex];
                                    return `${item.years} years (${item.formatted})`;
                                }
                            }
                        }
                    }
                }
            });

            // Members with Relapses Chart
            const relapsesCtx = document.getElementById('relapsesChart').getContext('2d');
            const relapsesData = @json($membersWithRelapses);

            if (relapsesData.length > 0) {
                new Chart(relapsesCtx, {
                    type: 'bar',
                    data: {
                        labels: relapsesData.map(item => item.name),
                        datasets: [{
                            label: 'Number of Sobriety Dates',
                            data: relapsesData.map(item => item.count),
                            backgroundColor: 'rgba(251, 146, 60, 0.6)',
                            borderColor: 'rgba(251, 146, 60, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: 'Members with Multiple Sobriety Dates'
                            }
                        }
                    }
                });
            } else {
                // Show message if no data
                const canvas = document.getElementById('relapsesChart');
                const ctx = canvas.getContext('2d');
                ctx.font = '16px Arial';
                ctx.fillStyle = '#6b7280';
                ctx.textAlign = 'center';
                ctx.fillText('No members with multiple sobriety dates', canvas.width/2, canvas.height/2);
            }

            // Sobriety Date Distribution Pie Chart
            const distributionCtx = document.getElementById('distributionChart').getContext('2d');
            const distributionData = @json($sobrietyDistribution);

            if (distributionData.total_count > 0) {
                new Chart(distributionCtx, {
                    type: 'pie',
                    data: {
                        labels: ['Single Sobriety Date', 'Multiple Sobriety Dates'],
                        datasets: [{
                            data: [distributionData.single_percentage, distributionData.multiple_percentage],
                            backgroundColor: [
                                'rgba(34, 197, 94, 0.8)',   // Green for single
                                'rgba(239, 68, 68, 0.8)'    // Red for multiple
                            ],
                            borderColor: [
                                'rgba(34, 197, 94, 1)',
                                'rgba(239, 68, 68, 1)'
                            ],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true
                                }
                            },
                            title: {
                                display: true,
                                text: 'Relapse Percentage',
                                padding: 20
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label;
                                        const percentage = context.parsed;
                                        const count = label.includes('Single') ? distributionData.single_count : distributionData.multiple_count;
                                        return `${label}: ${percentage}% (${count} members)`;
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                // Show message if no data
                const canvas = document.getElementById('distributionChart');
                const ctx = canvas.getContext('2d');
                ctx.font = '16px Arial';
                ctx.fillStyle = '#6b7280';
                ctx.textAlign = 'center';
                ctx.fillText('No sobriety data available', canvas.width/2, canvas.height/2);
            }
        }

        // Initialize charts when DOM is ready OR when Livewire component is loaded
        function ensureChartsLoad() {
            // Destroy existing charts if they exist to prevent conflicts
            const chartIds = ['sobrietyDatesChart', 'sobrietyChart', 'relapsesChart', 'distributionChart'];
            chartIds.forEach(id => {
                const existingChart = Chart.getChart(id);
                if (existingChart) {
                    existingChart.destroy();
                }
            });

            // Initialize charts after a small delay to ensure DOM is ready
            setTimeout(() => {
                initializeCharts();
            }, 100);
        }

        // Multiple event listeners to ensure charts load in all scenarios
        document.addEventListener('DOMContentLoaded', ensureChartsLoad);
        document.addEventListener('livewire:navigated', ensureChartsLoad);
        document.addEventListener('livewire:load', ensureChartsLoad);

        // Also try to load when the component is ready
        document.addEventListener('alpine:init', ensureChartsLoad);

        // Fallback: if page is already loaded, initialize immediately
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            ensureChartsLoad();
        }
    </script>
</div>
