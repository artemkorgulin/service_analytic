<template>
    <div class="action-call">
        <Page :title="title" :select="select" :is-display-main-tpl="userActiveAccounts.length > 0">
            <PendingOverlay v-if="!dataLoaded" class="action-call__pending" />
            <div v-else-if="dataLoaded && !dataEmpty" class="action-call__body">
                <div class="action-call__alerts">
                    <span class="action-call__alerts-header">
                        <h2 class="pl-2 pb-2">
                            {{ getTroublesSummary.name }}
                        </h2>
                        <a
                            :href="getTroublesSummary.link"
                            target="_blank"
                            rel="noopener"
                            class="action-call__alerts-link"
                        >
                            <span>{{ headerLinkText }}</span>
                            <SvgIcon
                                name="outlined/link"
                                class="action-call__alerts-link-icon"
                                data-right
                            />
                        </a>
                    </span>
                    <template
                        v-if="
                            getTroublesSummary['troubles'] && getTroublesSummary['troubles'].length
                        "
                    >
                        <se-alert
                            v-for="(message, i) in getTroublesSummary['troubles']"
                            :key="i"
                            type="alert"
                        >
                            {{ message }}
                        </se-alert>
                    </template>
                    <PendingOverlay
                        v-if="productRequestPending && getTroublesSummary.name"
                        :circle-size="50"
                        class="action-call__pending action-call__pending_absolute"
                    />
                </div>
                <div class="action-call__charts">
                    <div class="action-call__charts-container">
                        <BaseChart
                            v-for="(chart, i) in chartsData"
                            :key="i"
                            ref="chartsData"
                            :chart-height="getChartHeight(chartsData, i)"
                            class="chart"
                            :chart-id="`chart-${i}`"
                            :chart-data="chartsData[i]"
                            :chart-options="chartOptions[i]"
                            :chart-plugins="chartPlugins"
                        />
                        <PendingOverlay
                            v-if="productRequestPending"
                            :circle-size="80"
                            class="action-call__pending action-call__pending_absolute"
                        />
                    </div>
                    <MultipleChartLegend
                        class="mt-auto"
                        :items="charts"
                        @itemClick="handleLegendSelectionChange"
                    />
                </div>
                <ActionCallTable />
            </div>
            <div v-else class="action-call__empty">
                <VImg max-width="410" src="/images/some-chart.svg" contain />
                <p class="mt-7">
                    Товары для отображения отсутствуют или добавлены в
                    <br />
                    отслеживание совсем недавно.
                    <br />
                    Добавьте товары в отслеживание, и завтра здесь
                    <br />
                    отобразится информация
                </p>
            </div>
        </Page>
    </div>
</template>

<script>
    /* eslint-disable no-unused-vars, consistent-this */

    import Vue from 'vue';
    import { mapState, mapActions, mapGetters } from 'vuex';
    import chartMixin from '~mixins/chart.mixin';
    import { externalTooltipMultitableHandler } from '~/components/pages/product/Charts/plugins/tooltips';
    import { ru } from 'date-fns/locale';
    import BaseChart from '~/components/ui/BaseChart';
    import Page from '~/components/ui/SeInnerPage';

    export default {
        name: 'ActionCall',
        components: {
            BaseChart,
            Page,
        },
        mixins: [chartMixin],
        data() {
            return {
                title: {
                    isActive: true,
                    text: 'Сигналы к действию',
                },
                select: {
                    isActive: true,
                },
                barPositionX: undefined,
                allCharts: [],
                chartHeight: {
                    last: 148,
                    notLast: 128,
                },
                charts: [
                    [
                        {
                            key: 'sales_user',
                            color: '#0259FF',
                            label: 'Продажи ₽ Ваш товар',
                            ref: 'sales',
                            active: true,
                        },
                        {
                            key: 'sales_top36',
                            color: '#92B7FF',
                            label: 'Продажи ₽ Топ 36',
                            active: true,
                        },
                    ],
                    [
                        {
                            key: 'optimization',
                            color: '#20C274',
                            label: 'Степень оптимизации',
                            active: true,
                        },
                    ],
                    [
                        {
                            key: 'rating_user',
                            color: '#0259FF',
                            label: 'Рейтинг Ваш товар',
                            active: false,
                        },
                        {
                            key: 'rating_top36',
                            color: '#92B7FF',
                            label: 'Рейтинг Топ 36',
                            active: false,
                        },
                    ],
                    [
                        {
                            key: 'feedbacks_user',
                            color: '#F4E450',
                            label: 'Количество отзывов Ваш товар',
                            active: false,
                        },
                        {
                            key: 'feedbacks_top36',
                            color: '#FFF597',
                            label: 'Количество отзывов Топ 36',
                            active: false,
                        },
                    ],
                    [
                        {
                            key: 'avg_position_category_user',
                            color: '#3DC1CA',
                            label: 'Средняя позиция в категории Ваш товар',
                            active: true,
                        },
                    ],
                    [
                        {
                            key: 'avg_position_search_user',
                            color: '#F4A950',
                            label: 'Средняя позиция в поиске Ваш товар',
                            active: false,
                        },
                    ],
                    [
                        {
                            key: 'images_user',
                            color: '#A46FE8',
                            label: 'Количество изображений Ваш товар',
                            active: false,
                        },
                        {
                            key: 'images_top36',
                            color: '#A46FE8',
                            label: 'Количество изображений Топ 36',
                            active: false,
                        },
                    ],
                ],
            };
        },
        async fetch() {
            try {
                this.fetchTableData();
            } catch {
                this.$notify.create({
                    message: 'Произошла ошибка! Попробуйте перезагрузить страницу.',
                    type: 'negative',
                });
            }
        },
        head() {
            return {
                title: 'Сигналы к действию',
            };
        },
        computed: {
            ...mapState('action-call', [
                'activeProductId',
                'activeProduct',
                'total',
                'troublesSummary',
                'dataLoaded',
                'dataEmpty',
                'productRequestPending',
            ]),
            ...mapGetters('action-call', ['getTroublesSummary']),
            ...mapGetters(['isSelectedMp', 'userActiveAccounts']),
            chartsData() {
                return this.charts.reduce((acc, curr) => {
                    const data = curr.map(curr => this.activeProduct[curr.key]);
                    const datasets = [];
                    data.forEach((el, i) => {
                        if (el && curr[i].active) {
                            const dataset = {
                                backgroundColor: curr[i].color,
                                borderColor: curr[i].color,
                                fill: false,
                                label: curr[i].label,
                                tension: 0.5,
                                data: el,
                            };
                            if (i > 0) {
                                dataset.borderDash = [10, 10];
                            }
                            datasets.push(dataset);
                        }
                    });
                    if (datasets.length) {
                        acc.push({
                            context: {
                                allChartLines: this.getTooltipDefs(),
                                getAllChartData: () => this.activeProduct,
                                keyToColorMatch: this.keyToColorMatch,
                            },
                            datasets,
                            labels: this.activeProduct.dates,
                        });
                    }
                    return acc;
                }, []);
            },
            chartPlugins() {
                return [
                    {
                        beforeDraw: chart => {
                            if (chart.tooltip?._active?.length) {
                                this.allCharts.forEach(iteratedChart => {
                                    if (iteratedChart.id !== chart.id) {
                                        iteratedChart.draw();
                                    }
                                });
                            }
                        },
                        afterEvent: chart => {
                            if (!chart.tooltip?._active?.length) {
                                this.allCharts.forEach(iteratedChart => {
                                    iteratedChart.draw();
                                });
                            }
                        },
                    },
                ];
            },
            chartOptions() {
                const options = [];

                this.chartsData.forEach((el, index) => {
                    const option = {
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
                                ticks: {
                                    display: false,
                                },
                            },
                            y: {
                                ticks: {
                                    display: false,
                                    callback: function (value) {
                                        return value.toLocaleString().replace(/,/g, ' ');
                                    },
                                    padding: 10,
                                },
                            },
                        },
                        plugins: {
                            legend: {
                                display: false,
                            },
                            tooltip: {
                                enabled: false,
                                external: externalTooltipMultitableHandler,
                                externalContext: this,
                            },
                            datalabels: {
                                display: false,
                            },
                        },
                        layout: {
                            padding: {
                                right: 25,
                                left: 25,
                                bottom: 0,
                            },
                        },
                    };
                    option.scales.x.ticks.display = () => index === this.chartsData.length - 1;
                    options.push(option);
                });
                return options;
            },
            keyToColorMatch() {
                return this.getTooltipDefs().reduce((acc, curr) => {
                    acc[curr.key] = curr.color;
                    return acc;
                }, {});
            },
            headerLinkText() {
                return this.activeProduct.id || 'ссылка';
            }
        },
        watch: {
            chartsData(val) {
                if (val.length) {
                    setTimeout(() => {
                        this.allCharts = this.$refs.chartsData.map(el => el._data._chart);
                    }, 0);
                }
            },
            'isSelectedMp.userMpId'() {
                this.fetchTableData();
            },
        },
        methods: {
            ...mapActions('action-call', ['fetchTableData', 'fetchProductData']),
            getChartHeight(arr, index) {
                const { last, notLast } = this.chartHeight;
                return index === arr.length - 1 ? last : notLast;
            },
            handleLegendSelectionChange(args) {
                const { chartIndex, innerIndex, value } = args;
                Vue.set(this.charts[chartIndex][innerIndex], 'active', !value);
                // TODO: get rid of setTimeout
                setTimeout(() => {
                    this.allCharts = this.$refs.chartsData.map(el => el._data._chart);
                    this.$refs.chartsData[this.$refs.chartsData.length - 2].$refs.canvas.style.height = `${this.chartHeight.notLast}px`;
                }, 100);
            },
            getTooltipDefs() {
                return this.charts.flat();
            },
        },
    };
</script>

<style lang="scss" scoped>
    /* stylelint-disable selector-pseudo-element-no-unknown */
    .action-call {
        @include flex-grid-y;

        &__pending {
            @include flex-center;

            height: 100%;

            &.action-call__pending_absolute {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                background-color: $white;
                opacity: 0.6;
                pointer-events: none;
            }
        }

        &__body {
            @include cardShadow;
            @include flex-grid-y;

            padding: 16px;
            border-radius: 24px;
            background: #fff;
        }

        &__alerts {
            @include flex-grid-y;

            position: relative;
            gap: 8px;

            &-header {
                @include flex-grid-x;

                h2 {
                    display: inline;
                    font-size: 24px;
                    font-weight: 500;
                    line-height: 33px;
                }
            }

            &-link {
                display: inline-flex;
                flex-wrap: nowrap;
                padding-top: 8px;
                text-decoration: none;
                font-size: 12px;
                line-height: 16px;
                color: $color-purple-primary;

                &-icon {
                    width: 14px !important;
                    height: 14px !important;
                    margin-left: 6px;
                }
            }
        }

        &__charts {
            position: relative;
            display: flex;
            flex-direction: column;
            min-height: 350px;

            &-legend {
                display: flex;

                & > div {
                    flex: 1;
                }
            }
        }

        &__empty {
            @include flex-center;

            flex-direction: column;
            height: 100%;
            text-align: center;
            font-size: 16px;
            line-height: 24px;
            color: $color-gray-dark-800;

            &::v-deep .v-image {
                flex: none;
            }
        }
    }
</style>
