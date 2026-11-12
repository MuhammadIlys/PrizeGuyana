
<script setup>
import { computed } from 'vue'
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Filler,
    Tooltip,
    Legend,
} from 'chart.js'
import { Line } from 'vue-chartjs'

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Filler,
    Tooltip,
    Legend
)

const props = defineProps({
    revenueTrend: {
        type: Array,
        default: () => [],
    },
})

const formatMoney = (value, currency) => {
    try {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency || 'USD',
            maximumFractionDigits: 2,
        }).format(Number(value || 0))
    } catch {
        return `${currency || 'USD'} ${Number(value || 0).toFixed(2)}`
    }
}

const currencies = computed(() => [
    ...new Set(props.revenueTrend.map(item => item.currency).filter(Boolean)),
])

const dates = computed(() => [
    ...new Set(props.revenueTrend.map(item => item.date)),
].sort())

const chartOptions = (currency) => ({
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
        intersect: false,
        mode: 'index',
    },
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            backgroundColor: '#0f172a',
            borderColor: '#334155',
            borderWidth: 1,
            titleColor: '#f8fafc',
            bodyColor: '#fbbf24',
            padding: 12,
            callbacks: {
                label: (context) =>
                    formatMoney(context.parsed.y, currency),
            },
        },
    },
    scales: {
        x: {
            grid: {
                display: false,
            },
            ticks: {
                color: '#64748b',
                maxTicksLimit: 8,
                maxRotation: 0,
            },
            border: {
                display: false,
            },
        },
        y: {
            beginAtZero: true,
            grid: {
                color: 'rgba(148, 163, 184, 0.10)',
                drawTicks: false,
            },
            ticks: {
                color: '#64748b',
                padding: 10,
                callback: (value) =>
                    new Intl.NumberFormat('en-US', {
                        notation: 'compact',
                        maximumFractionDigits: 1,
                    }).format(value),
            },
            border: {
                display: false,
            },
        },
    },
    elements: {
    line: {
        tension: 0.4,
        borderWidth: 2.5,
        fill: true,
    },
    point: {
        radius: 4,
        hoverRadius: 7,
        hitRadius: 12,
        backgroundColor: '#f59e0b',
        borderColor: '#ffffff',
        borderWidth: 2,
    },
},
})

const charts = computed(() => {
    return currencies.value.map(currency => {
        const values = dates.value.map(date => {
            const entry = props.revenueTrend.find(
                item => item.date === date && item.currency === currency
            )

            return Number(entry?.revenue || 0)
        })

        return {
            currency,
            data: {
                labels: dates.value.map(date =>
                    new Date(`${date}T00:00:00`).toLocaleDateString(
                        'en-US',
                        { month: 'short', day: 'numeric' }
                    )
                ),
                datasets: [{
                    label: currency,
                    data: values,
                    borderColor: '#f59e0b',
                    backgroundColor: (context) => {
                        const chart = context.chart
                        const { ctx, chartArea } = chart

                        if (!chartArea) return 'rgba(245, 158, 11, 0.12)'

                        const gradient = ctx.createLinearGradient(
                            0,
                            chartArea.top,
                            0,
                            chartArea.bottom
                        )

                        gradient.addColorStop(0, 'rgba(245, 158, 11, 0.30)')
                        gradient.addColorStop(1, 'rgba(245, 158, 11, 0.01)')

                        return gradient
                    },
                    fill: true,
                }],
            },
            options: chartOptions(currency),
        }
    })
})
</script>

<template>
    <div v-if="charts.length" class="space-y-8">
        <div
            v-for="chart in charts"
            :key="chart.currency"
            class="space-y-3"
        >
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-amber-400"></span>
                    <span class="text-sm font-semibold text-white">
                        {{ chart.currency }}
                    </span>
                </div>
                <span class="text-xs text-slate-500">
                    Daily revenue
                </span>
            </div>

            <div class="h-64 w-full">
                <Line
                    :data="chart.data"
                    :options="chart.options"
                />
            </div>
        </div>
    </div>

    <div
        v-else
        class="flex h-64 flex-col items-center justify-center text-center"
    >
        <p class="text-sm font-medium text-slate-300">
            No revenue data available
        </p>
        <p class="mt-1 text-xs text-slate-500">
            Paid orders will appear here once transactions are completed.
        </p>
    </div>
</template>
