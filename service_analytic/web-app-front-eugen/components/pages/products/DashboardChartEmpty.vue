<template>
    <BaseChart
        ref="empty"
        :chart-height="140"
        :chart-width="140"
        :chart-options="chartOptions"
        :chart-type="chartProductsType"
        :chart-id="chartId"
        :chart-data="chartData"
        :chart-plugins="chartPlugin"
    />
</template>

<script>
    import BaseChart from '~/components/ui/BaseChart';
    export default {
        name: 'DashboardChartEmpty',
        components: {
            BaseChart,
        },
        data: () => ({
            chartProductsType: 'doughnut',
            chartId: 'emptyChartId',
            backgroundColors: ['#c8cfd9'],
        }),
        computed: {
            chartData() {
                return {
                    labels: [],
                    datasets: [
                        {
                            backgroundColor: this.backgroundColors,
                            data: [1],
                        },
                    ],
                };
            },
            chartOptions() {
                return {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            enabled: false,
                        },
                    },
                };
            },
            chartPlugin() {
                return [
                    {
                        beforeDraw: chart => {
                            const width = chart.width;
                            const height = chart.height;
                            const ctx = chart.ctx;
                            ctx.restore();
                            ctx.textBaseline = 'middle';
                            const textX = Math.round(
                                (width - ctx.measureText('Нет данных').width) / 2
                            );
                            const textY = height / 2;
                            ctx.fillText('Нет данных', textX, textY);
                            ctx.save();
                        },
                    },
                ];
            },
        },
    };
</script>
