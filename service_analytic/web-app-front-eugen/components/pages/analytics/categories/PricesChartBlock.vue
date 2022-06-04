<template>
    <div class="tabs-prices mt-10">
        <BaseChart
            ref="priceChart"
            :chart-id="chartId"
            :chart-data="chartData"
            :chart-options="chartOptions"
            :chart-plugins="chartPlugin"
        />
    </div>
</template>

<script>
    import BaseChart from '~/components/ui/BaseChart';
    import { externalTooltipHandler } from '~/components/pages/product/Charts/plugins/tooltips';
    import { setChartScale } from '~utils/helpers';

    export default {
        name: 'PricesChartBlock',
        components: {
            BaseChart,
        },
        props: {
            prices: {
                type: Array,
                default: () => [],
            },
        },
        data: () => ({
            filter: {
                range: [0, 200000],
                segments: 25,
            },
            chartId: 'priceChart',
        }),
        computed: {
            labels() {
                try {
                    return this.prices.map(
                        ({ min_range, max_range }) =>
                            `${Math.floor(min_range)}-${Math.floor(max_range)} ₽`
                    );
                } catch {
                    return [];
                }
            },
            datasets() {
                try {
                    return [
                        {
                            type: 'line',
                            label: 'Выручка',
                            data: this.prices.map(({ revenue }) => Math.floor(revenue)),
                            fill: false,
                            borderColor: '#3DC1CA',
                            backgroundColor: '#3DC1CA',
                            tension: 0.5,
                            borderWidth: 2,
                            yAxisID: 'y0',
                        },
                        {
                            type: 'bar',
                            label: 'Товаров',
                            data: this.prices.map(({ products }) => products),
                            borderColor: 'rgba(252, 110, 144, 0.4)',
                            backgroundColor: 'rgba(252, 110, 144, 0.4)',
                            maxBarThickness: '21.47',
                            yAxisID: 'y1',
                        },
                        {
                            type: 'bar',
                            label: 'Средняя цена',
                            data: this.prices.map(({ avg_price }) => Math.floor(avg_price)),
                            borderColor: 'rgba(164, 111, 232, 0.4)',
                            backgroundColor: 'rgba(164, 111, 232, 0.4)',
                            maxBarThickness: '21.47',
                            yAxisID: 'y2',
                        },
                    ];
                } catch (error) {
                    console.error(error);
                    return [];
                }
            },
            chartData() {
                return {
                    labels: this.labels,
                    datasets: this.datasets,
                };
            },
            chartOptions() {
                return {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        y0: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            min: 0,
                            ticks: {
                                beginAtZero: true,
                                callback: function (value) {
                                    return setChartScale(value);
                                },
                                precision: 0,
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            min: 0,
                            ticks: {
                                beginAtZero: true,
                                callback: function (value) {
                                    return setChartScale(value);
                                },
                                precision: 0,
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                        },
                        y2: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            min: 0,
                            ticks: {
                                beginAtZero: true,
                                callback: function (value) {
                                    return setChartScale(value);
                                },
                                precision: 0,
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                        },
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                            },
                        },
                        tooltip: {
                            enabled: false,
                            external: externalTooltipHandler,
                        },
                    },
                };
            },
            chartPlugin() {
                return [
                    {
                        afterDraw: chart => {
                            const yAxis = chart.scales;
                            try {
                                const setColor = (ticks, target, field, value) => {
                                    ticks[target].forEach(item => {
                                        item[field] = value;
                                    });
                                };

                                this.datasets.forEach(item => {
                                    const { yAxisID, backgroundColor: color } = item;
                                    setColor(yAxis[yAxisID], '_labelItems', 'color', color);
                                });
                            } catch (error) {
                                console.error(error);
                            }
                        },
                    },
                    {
                        beforeDraw: chart => {
                            if (chart.tooltip?._active?.length) {
                                const x = chart.tooltip._active[0].element.x;
                                const yAxis = chart.scales['y0'];
                                const ctx = chart.ctx;
                                ctx.save();
                                ctx.beginPath();
                                ctx.moveTo(x, yAxis.top);
                                ctx.lineTo(x, yAxis.bottom);
                                ctx.lineWidth = 1;
                                ctx.setLineDash([5, 3]);
                                ctx.strokeStyle = 'rgba(0, 0, 0, 1)';
                                ctx.stroke();
                                ctx.restore();
                            }
                        },
                    },
                ];
            },
        },
    };
</script>

<style lang="scss" scoped>
    .tabs-prices {
        padding: 16px;

        .tabs-prices__control {
            display: flex;

            .tabs-prices__control-range {
                max-width: 96px;
            }

            .tabs-prices__control-input {
                max-width: 200px;
                margin-left: 16px;
            }
        }

        .v-slider__track-background.primary {
            border-color: #e9edf2 !important;
            background-color: #e9edf2 !important;
        }
    }
</style>
