<template>
    <div class="tabs-prices mt-10">
        <div class="tabs-prices__control mb-7">
            <v-text-field-money
                v-model="dataPriceFieldMin"
                class="tabs-prices__control-range light-outline"
                label="Min"
                :properties="{
                    prefix: '',
                    readonly: false,
                    disabled: false,
                    dense: true,
                    outlined: true,
                    clearable: false,
                    hideDetails: true,
                    placeholder: ' ',
                }"
                :options="{
                    locale: 'ru-RU',
                    length: 7,
                    precision: 0,
                    empty: null,
                }"
            />

            <v-range-slider
                :value="[dataPriceFieldMin, dataPriceFieldMax]"
                class="align-center mr-4 ml-4"
                :min="minRange"
                :max="maxRange"
                :ripple="false"
                :thumb-size="0"
                hide-details
                @change="rangeHadler"
            >
                <template #thumb-label="{ value }">
                    <span class="tabs-prices__control-label">
                        {{ currencyFormatter(value, sign) }}
                    </span>
                </template>
            </v-range-slider>

            <v-text-field-money
                v-model="dataPriceFieldMax"
                class="tabs-prices__control-range light-outline"
                label="Max"
                :properties="{
                    prefix: '',
                    readonly: false,
                    disabled: false,
                    dense: true,
                    outlined: true,
                    clearable: false,
                    hideDetails: true,
                    placeholder: ' ',
                }"
                :options="{
                    locale: 'ru-RU',
                    length: 7,
                    precision: 0,
                    empty: null,
                }"
            />

            <v-text-field
                v-model="segments"
                class="tabs-prices__control-input light-outline"
                type="number"
                dense
                outlined
                hide-details
                label="Количество сегментов"
            ></v-text-field>
        </div>
        <BaseChart
            ref="priceChart"
            :chart-id="chartId"
            :chart-data="chartData"
            :chart-options="chartOptions"
            :chart-plugins="chartPlugin"
            :chart-height="420"
        />
    </div>
</template>

<script>
    /* eslint-disable */
    import { mapState, mapMutations, mapActions } from 'vuex';
    import BaseChart from '~/components/ui/BaseChart';
    import { debounce } from 'lodash';
    import { currencyFormatter } from '~/components/pages/analytics/brand/helpers/currencyFormatter';
    import { externalTooltipHandler } from '~/components/pages/product/Charts/plugins/tooltips';
    import { setChartScale } from '~utils/helpers';

    export default {
        name: 'BrandPricesChart',
        components: {
            BaseChart,
        },
        data: () => ({
            filter: {
                range: [],
                segments: 25,
            },
            sign: '₽',
            chartId: 'priceChart',
            firstAssignRange: false,
            prevRange: undefined,
        }),
        computed: {
            ...mapState('analytics', ['prices', 'priceRange', 'isLoading', 'dataPrices']),
            minRange() {
                if (this.priceRange.length > 0) {
                    return this.priceRange[0];
                }
                return 0;
            },
            maxRange() {
                if (this.priceRange.length > 0) {
                    return this.priceRange[1];
                }
                return 200000;
            },
            labels() {
                return this.prices.map(
                    ({ min_range, max_range }) => `${parseInt(min_range)}-${parseInt(max_range)} ₽`
                );
            },
            datasets() {
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
            },
            dataPriceFieldMin: {
                get() {
                    return this.dataPrices.min;
                },
                set(value) {
                    this.setDataPrices({
                        min: Number(value),
                        max: this.dataPrices.max,
                        segment: this.dataPrices.segment,
                    });

                    this.debounceHandleRange();
                },
            },
            dataPriceFieldMax: {
                get() {
                    return this.dataPrices.max;
                },
                set(value) {
                    this.setDataPrices({
                        min: this.dataPrices.min,
                        max: Number(value),
                        segment: this.dataPrices.segment,
                    });

                    this.debounceHandleRange();
                },
            },
            segments: {
                get() {
                    return this.dataPrices.segment;
                },
                set(value) {
                    this.setDataPrices({
                        min: this.dataPrices.min,
                        max: this.dataPrices.max,
                        segment: Number(value),
                    });

                    this.debounceHandleRange();
                },
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
        created() {
            this.debounceHandleRange = debounce(() => {
                this.$emit('rangeChanged');
            }, 500);
        },
        mounted() {
            this.filter.range = this.priceRange;
        },
        watch: {
            priceRange: {
                handler(newVal) {
                    this.filter.range = newVal;
                },
                deep: true,
            },
        },
        methods: {
            ...mapMutations('analytics', ['setDataPrices']),
            currencyFormatter: currencyFormatter,
            rangeHadler(value) {
                this.setDataPrices({
                    min: value[0],
                    max: value[1],
                    segment: this.dataPrices.segment,
                });

                this.$emit('rangeChanged');
            },
        },
    };
</script>

<style lang="scss" scoped>
    .tabs-prices {
        padding: 16px;

        &__control {
            display: flex;

            &-label {
                display: flex;
                margin-bottom: 40px;
                padding: 6.5px 12px;
                border-radius: 200px;
                background: rgba(113, 11, 255, 0.08);
                text-align: center;
                white-space: nowrap;
                font-weight: bold;
                font-size: 14px;
                line-height: 19px;
                color: $primary-color;
            }

            &-range {
                max-width: 96px;
            }

            &-input {
                max-width: 200px;
                margin-left: 16px;
            }
        }

        .v-slider__track-background .primary {
            border-color: $border-color !important;
            background-color: $border-color !important;
        }
    }
</style>
