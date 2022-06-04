<template>
    <div class="product-content__panel product-content__panel--full-width mx-0 mb-4">
        <div class="d-flex align-center justify-space-between mb-4">
            <h3 v-if="isSelectedMp === 1" class="heading mr-3 mb-4">График просмотров и продаж</h3>
            <h3 v-else class="heading mr-3 mb-4">График продаж</h3>
            <div class="heading_controls">
                <VCol class="pa-0 d-flex heading_controls-wrapper">
                    <ElSelect v-model="selectedPeriod" placeholder="Период" class="custom">
                        <ElOption
                            v-for="period in periods"
                            :key="period.value"
                            :label="period.label"
                            :value="period.value"
                        ></ElOption>
                    </ElSelect>
                </VCol>
                <ElDatePicker
                    v-model="selectedDates"
                    type="daterange"
                    format="d MMM yyyy"
                    align="right"
                ></ElDatePicker>
            </div>
        </div>
        <template v-if="!isAnalyticsLoading && getSales && isChartData">
            <template v-if="isSelectedMp.id === 1">
                <BaseChart
                    ref="chartSales"
                    class="chart"
                    :chart-height="400"
                    :chart-id="chartId"
                    :chart-plugins="chartPluginSales"
                    :chart-data="chartData"
                    :chart-options="chartOptionsOzon"
                />
            </template>
            <template v-else>
                <BaseChart
                    ref="chartSales"
                    class="chart"
                    :chart-height="400"
                    :chart-id="chartId"
                    :chart-plugins="chartPluginSales"
                    :chart-data="chartData"
                    :chart-options="chartOptions"
                />
            </template>
        </template>
        <template v-else>
            <div v-if="isAnalyticsLoading" class="progress-wrap d-flex align-center justify-center">
                <v-progress-circular indeterminate size="120" color="primary"></v-progress-circular>
            </div>
            <div class="chart_no-data">
                <img src="/images/no_chart.svg" alt="no-chart" />
                <span class="mt-4">Нет данных</span>
            </div>
        </template>
    </div>
</template>

<script>
    const colors = ['#EA5E7F', '#5774DD', '#3DCAC1', '#A46FE8', '#FC6E90', '#F4E450'];

    import { mapState, mapGetters, mapActions } from 'vuex';
    import chartMixin from '~mixins/chart.mixin';
    import { setChartScale } from '~utils/helpers';
    import { externalTooltipHandler } from '~/components/pages/product/Charts/plugins/tooltips';
    import { ru } from 'date-fns/locale';
    import BaseChart from '~/components/ui/BaseChart';

    export default {
        name: 'ProductChartSales',
        components: { BaseChart },
        mixins: [chartMixin],
        props: {
            productSku: {
                type: [Number, String, Object],
                default: null,
            },
            productIdLocal: {
                type: [Number, String],
                default: null,
            },
            options: {
                type: Array,
                default: () => [],
            },
        },
        data: () => ({
            chartId: 'chartSales',
        }),
        computed: {
            ...mapState('product', ['isAnalyticsLoading']),
            ...mapState({
                getSales: state => state.marketPlaceChart.marketplace.sales,
            }),
            ...mapGetters({
                productHasOptions: 'isProductHasOptions',
                activeOptionIndex: 'product/getActiveOptionIndex',
            }),
            labelsSales() {
                return Array.from(
                    new Set(this.getSales.map(({ graph }) => Object.keys(graph)).flat()).values()
                );
            },
            datasetsSales() {
                return this.getSales.map((item, index) => ({
                    label: item.title,
                    fill: false,
                    borderColor: colors[index],
                    backgroundColor: colors[index],
                    data: Object.values(item.graph),
                    yAxisID: `y${index}`,
                    position: 'left',
                    tension: 0.5,
                }));
            },
            chartData() {
                return {
                    labels: this.labelsSales,
                    datasets: this.datasetsSales,
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
                    stacked: true,
                    scales: {
                        x: {
                            display: true,
                            type: 'time',
                            adapters: {
                                date: {
                                    locale: ru,
                                },
                            },
                            time: {
                                unit: 'day',
                                tooltipFormat: 'd MMMM',
                            },
                            title: {
                                displayFormat: { day: 'd MMMM' },
                            },
                        },
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
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'right',
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
            chartOptionsOzon() {
                return {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    borderWidth: 2,
                    stacked: true,
                    scales: {
                        x: {
                            display: true,
                            type: 'time',
                            adapters: {
                                date: {
                                    locale: ru,
                                },
                            },
                            time: {
                                unit: 'day',
                                tooltipFormat: 'd MMMM',
                            },
                            title: {
                                displayFormat: { day: 'd MMMM' },
                            },
                        },
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
                            position: 'right',
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
            chartPluginSales() {
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

                                this.datasetsSales.forEach(item => {
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
            isChartData() {
                return (
                    this.chartData.datasets.length > 0 &&
                    this.chartData.datasets.map(({ data }) => data).every(item => item.length > 0)
                );
            },
            productIdWB() {
                return this.productHasOptions
                    ? this.options[this.activeOptionIndex].nmId
                    : this.productSku;
            },
        },
        watch: {
            selectedDates(val) {
                this.handlePeriod(val);
                if (val === null) {
                    this.selectedPeriod = null;
                }
            },
            chartParams(val) {
                this.fetchDataChart(val);
            },
            productIdWB(val) {
                this.fetchDataChart(val);
            },
        },
        methods: {
            ...mapActions({
                fetchDataChartSalesOzon: 'marketPlaceChart/fetchDataChartSalesOzon',
            }),
            fetchDataChart(val) {
                if (val) {
                    if (this.isSelectedMp.id === 2) {
                        this.fetchDataChartWB({ sku: this.productIdWB, params: val });
                    } else {
                        this.fetchDataChartSalesOzon({
                            id: this.productIdLocal,
                            params: val,
                        });
                    }
                }
            },
        },
    };
</script>

<style scoped lang="scss">
    .heading {
        @extend %text-h5;

        font-size: 20px;
        cursor: default;

        &_controls {
            display: flex;
            gap: 8px;

            &-wrapper {
                width: 115px;
                max-width: 115px;
            }
        }
    }

    span {
        cursor: default;
    }

    .chart {
        height: 400px;
        margin-top: 16px;

        &_no-data {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            height: 400px;
            border-radius: 8px;
            border: 1px solid $base-500;
            font-size: 14px;
            color: $base-700;
            font-weight: 400;

            img {
                width: 51px;
                height: 44px;
                object-fit: cover;
            }
        }

        @include md {
            height: 226px;
            margin-top: 0;
            padding: 0 16px 12px;
            background-color: $white;
        }
    }
</style>
