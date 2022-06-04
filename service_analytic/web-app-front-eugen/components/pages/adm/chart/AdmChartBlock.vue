<template>
    <div :class="$style.AdmChartBlock">
        <div :class="$style.filtersWrapper">
            <h3 v-if="!mobile" :class="$style.chartHeading">График</h3>
            <div :class="$style.controlsWrapper">
                <VFadeTransition group tag="div" :class="$style.controlsInner">
                    <template v-if="isShow">
                        <InputDate
                            v-if="!mobile"
                            key="date"
                            v-model="dates"
                            :class="$style.datePicker"
                            :facet="dateFacet"
                            :value="dates"
                            hide-details
                        />
                        <VBtnToggle
                            key="btn"
                            v-model="periodSelected"
                            :class="$style.periodPicker"
                            :borderless="mobile"
                            dense
                        >
                            <VBtn
                                v-for="period in periods"
                                :key="period.title"
                                :class="$style.periodPickerBtn"
                                :disabled="period.disabled"
                            >
                                {{ period.title }}
                            </VBtn>
                        </VBtnToggle>
                    </template>
                </VFadeTransition>
                <VBtn
                    v-if="!mobile"
                    outlined
                    text
                    :class="$style.toggleBtn"
                    :disabled="!isFetched"
                    @click="handleToggle"
                >
                    <SvgIcon
                        name="outlined/chevrondown"
                        :class="[$style.chevron, isShow && $style.reverce]"
                    />
                </VBtn>
            </div>
        </div>
        <VExpandTransition>
            <div v-if="isShow">
                <BaseChart
                    ref="chart"
                    :class="$style.chart"
                    :chart-data="chartData"
                    :chart-options="chartOptions"
                />
                <AdmChartControls
                    v-model="selectedChartFields"
                    :class="$style.admChartControls"
                    :items="chartCounters"
                />
            </div>
        </VExpandTransition>
    </div>
</template>

<script>
    import { ru } from 'date-fns/locale';
    import { getShortNumber, isUnset } from '~utils/helpers';
    import BaseChart from '~/components/ui/BaseChart';
    /* eslint-disable no-extra-parens */
    const CHART_COLORS = {
        red: 'rgb(255, 99, 132)',
        orange: 'rgb(255, 159, 64)',
        yellow: 'rgb(255, 205, 86)',
        green: 'rgb(75, 192, 192)',
        blue: 'rgb(54, 162, 235)',
        purple: 'rgb(153, 102, 255)',
        grey: 'rgb(201, 203, 207)',
    };
    const CHART_FIELDS = [
        {
            name: 'Популярность',
            slug: 'popularity',
            color: CHART_COLORS.red,
            backdropColor: 'rgba(255, 99, 132,0.75)',
            yAxisID: 'y',
        },
        {
            name: 'Показы',
            slug: 'shows',
            color: CHART_COLORS.orange,
            backdropColor: 'rgba(255, 159, 64,0.75)',
            yAxisID: 'y1',
        },
        {
            name: 'Выкупленных показов, %',
            slug: 'purchased_shows',
            color: CHART_COLORS.yellow,
            backdropColor: 'rgba(255, 205, 86,0.75)',
            yAxisID: 'y2',
        },
        {
            name: 'СРМ, ₽',
            slug: 'avg_1000_shows_price',
            color: CHART_COLORS.green,
            backdropColor: 'rgba(75, 192, 192,0.75)',
            yAxisID: 'y3',
        },
        {
            name: 'Клики',
            slug: 'clicks',
            color: CHART_COLORS.blue,
            backdropColor: 'rgba(255, 99, 132,0.75)',
            yAxisID: 'y4',
        },
        {
            name: 'CTR, %',
            slug: 'ctr',
            color: CHART_COLORS.purple,
            backdropColor: 'rgba(153, 102, 255,0.75)',
            yAxisID: 'y5',
        },
        {
            name: 'СРС, ₽',
            slug: 'avg_click_price',
            color: CHART_COLORS.grey,
            backdropColor: 'rgba(201, 203, 20,0.75)',
            yAxisID: 'y6',
        },
    ];
    const PERIODS = [
        { title: 'Вчера', slug: 'yesterday' },
        { title: 'Неделя', slug: 'week' },
        { title: 'Месяц', slug: 'month' },
        { title: 'Квартал', slug: 'quater' },
        { title: 'Год', slug: 'year' },
    ];
    export default {
        name: 'AdmChartBlock',
        components: { BaseChart },
        CHART_FIELDS,
        PERIODS,
        props: {
            value: {
                type: Array,
                default: () => [],
            },
            items: {
                type: Array,
                default: () => [],
            },
            dateFacet: {
                type: Object,
                default: () => ({}),
            },
            counters: {
                type: Object,
                default: () => ({}),
            },
            isFetched: {
                type: Boolean,
                default: true,
            },
            mobile: {
                type: Boolean,
                default: false,
            },
        },
        data() {
            return {
                selectedChartFields: [0],
                periodSelected: null,
                dates: [],
                perPage: 25,
                isShow: Boolean(this.mobile),
            };
        },
        computed: {
            filtersToRequest() {
                return {
                    campaigns: [this.$route.params.id],
                    ...(this.dates?.length > 1 ? { from: this.dates[0], to: this.dates[1] } : null),
                    per_page: this.perPage,
                };
            },
            chartCounters() {
                if (!this.counters) {
                    return this.$options.CHART_FIELDS;
                }
                return this.$options.CHART_FIELDS.map(item => {
                    item.text = this.counters[item.slug];
                    return item;
                });
            },
            selectedChartFieldsObject() {
                return this.$options.CHART_FIELDS.filter((item, index) =>
                    this.selectedChartFields.includes(index)
                );
            },
            chartItems() {
                return this.items;
            },
            chartData() {
                if (!this.chartItems) {
                    return {};
                }
                return {
                    labels: this.chartItems.map(item => item.date),
                    datasets: this.selectedChartFieldsObject.map(item => ({
                        label: item.name,
                        data: this.chartItems.map(it => it[item.slug]),
                        borderColor: item.color,
                        backgroundColor: item.color,
                        fill: false,
                        pointRadius: 0,
                        yAxisID: item.yAxisID,
                    })),
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
                    scales: this.selectedChartFieldsObject.reduce(
                        (acc, val) => {
                            acc[val.yAxisID] = {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                id: val.yAxisID,
                                backdropColor: val.backdropColor,
                                color: val.color,

                                grid: {
                                    display: false,
                                    color: val.color,
                                    // color: val.backdropColor,
                                },
                                ticks: {
                                    color: val.color,
                                    backdropColor: val.backdropColor,
                                    callback(value, index, values) {
                                        return getShortNumber(value);
                                    },
                                },
                            };
                            return acc;
                        },
                        {
                            x: {
                                // TODO make different display format
                                type: 'time',
                                // https://date-fns.org/docs/format
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
                                // ticks:{

                                // }
                            },
                        }
                    ),
                };
            },
            periods() {
                return this.$options.PERIODS;
            },
        },
        watch: {
            periodSelected: {
                immediate: true,
                handler(val) {
                    if (isUnset(val)) {
                        this.dates = [];
                        return;
                    }
                    const today = new Date();
                    const DAY_MS = 1000 * 3600 * 24;
                    switch (val) {
                        case 0:
                            // YESTERDAY
                            this.dates = [new Date(Math.abs(today - DAY_MS)), today];
                            break;
                        case 1:
                            // WEAK
                            this.dates = [new Date(Math.abs(today - DAY_MS * 7)), today];
                            break;
                        case 2:
                            // MONTH
                            this.dates = [new Date(Math.abs(today - DAY_MS * 30)), today];
                            break;
                        case 3:
                            // QUATER
                            this.dates = [new Date(Math.abs(today - DAY_MS * 90)), today];
                            break;
                        case 4:
                            // YEAR
                            this.dates = [new Date(Math.abs(today - DAY_MS * 365)), today];
                            break;
                        default:
                            break;
                    }
                },
            },
            dates(val) {
                this.periodSelected = null;
                return this.$emit('input', val);
            },
            // viewSelected(val) {
            //     if (val === 1) {
            //         return this.$nextTick(() => this.handleSetScrollAreaHeight());
            //     }
            // },
        },
        methods: {
            handleToggle() {
                this.isShow = !this.isShow;
            },
        },
    };
</script>

<style lang="scss" module>
    .AdmChartBlock {
        gap: 16px;

        @include md {
            gap: 0;
            // padding-top: 12px;
        }
    }

    .periodPicker {
        @include md {
            display: flex;
            flex: 1;
            width: 100%;
            flex-basis: 100%;
        }
    }

    .periodPickerBtn {
        flex: 1;
        font-size: 14px !important;
        font-style: normal !important;
        line-height: 19px !important;
        color: $base-600 !important;
        font-weight: bold !important;

        &:global(.v-btn--active) {
            background-color: $white !important;
            box-shadow: 0 4px 32px rgba(146, 85, 85, 0.06) !important;
            color: $base-900 !important;

            &:before {
                display: none;
            }
        }
    }

    .chevron {
        transition: $primary-transition;

        &.reverce {
            transform: rotate(180deg);
        }
    }

    .controlsWrapper {
        display: flex;
        gap: 8px;

        @include md {
            width: 100%;
        }
    }

    .controlsInner {
        display: flex;
        gap: 8px;

        @include md {
            width: 100%;
        }
    }

    .toggleBtn {
        min-width: 40px !important;
        padding-right: 0 !important;
        padding-left: 0 !important;
    }

    .chartHeading {
        @extend %text-h5;

        margin-right: auto;
        font-size: 24px;
    }

    .datePicker {
        width: 300px;
    }

    .filtersWrapper {
        display: flex;
        align-items: center;
        gap: 16px;

        @include md {
            padding: 12px 16px;
            background-color: $white;
        }
    }

    .chart {
        height: 170px;
        margin-top: 16px;

        @include md {
            height: 226px;
            margin-top: 0;
            padding: 0 16px;
            padding-bottom: 12px;
            background-color: $white;
        }
    }

    .admChartControls {
        margin-top: 8px;
        background-color: $white;
    }
</style>
