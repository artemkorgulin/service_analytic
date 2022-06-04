<template>
    <div class="product-content__panel product-content__panel--full-width mx-0 mt-4">
        <div class="filters d-flex justify-space-between align-start">
            <div class="filters_container d-flex align-center justify-space-between pa-0 mb-4">
                <div class="d-flex align-center">
                    <h3 class="filters_heading mr-3">Динамика позиций</h3>
                    <VTabs
                        v-if="isSelectedMp.id !== 1"
                        key="btn"
                        v-model="requestSelected"
                        dense
                        mandatory
                        dark
                        class="filters_tabs"
                        height="36"
                        hide-slider
                    >
                        <VTab
                            v-for="request in requestTypes"
                            :key="request.title"
                            v-ripple="false"
                            active-class="white black--text"
                            class="filters_tabs__tab"
                        >
                            {{ request.title }}
                        </VTab>
                    </VTabs>
                </div>
                <div class="filters_controls">
                    <VCol class="pa-0 d-flex filters_controls-wrapper">
                        <ElSelect v-model="selectedPeriod" placeholder="Период">
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
                        align="right"
                        format="d MMM yyyy"
                    ></ElDatePicker>
                </div>
            </div>
        </div>
        <template v-if="isSelectedMp.id === 1">
            <div v-if="isChartData">
                <BaseChart
                    :chart-height="400"
                    class="chart"
                    :chart-id="chartId[0]"
                    :chart-data="chartDataOzon"
                    :chart-options="chartOptionsOzon"
                    :chart-plugins="chartPluginPositions"
                />
                <div id="legend-container-ozon" />
            </div>
            <div v-else class="chart_no-data">
                <img src="/images/no_chart.svg" alt="no-chart" />
                <span class="mt-4">Нет данных</span>
            </div>
        </template>
        <VTabsItems v-else v-model="requestSelected">
            <VTabItem>
                <template v-if="getPositions && getPositions.length > 0">
                    <BaseChart
                        :chart-height="400"
                        class="chart"
                        :chart-id="chartId[0]"
                        :chart-data="chartDataPositions"
                        :chart-options="chartOptionsPositions"
                        :chart-plugins="chartPluginPositions"
                    />
                    <div id="legend-container" />
                </template>
                <template v-else-if="!getPositions || getPositions.length === 0">
                    <div class="chart_no-data">
                        <img src="/images/no_chart.svg" alt="no-chart" />
                        <span class="mt-4">Нет данных</span>
                    </div>
                </template>
            </VTabItem>
            <VTabItem>
                <template v-if="getSearchRequests && getSearchRequests.length > 0">
                    <BaseChart
                        :chart-height="400"
                        class="chart"
                        :chart-id="chartId[1]"
                        :chart-data="chartDataRequests"
                        :chart-options="chartOptionsRequests"
                        :chart-plugins="chartPluginRequests"
                    />
                    <DataTable
                        :columns="labelsRequests"
                        :rows="datasetsRequests"
                        @update="updateChartRequests"
                    />
                </template>
                <template v-else-if="!getSearchRequests || getSearchRequests.length === 0">
                    <div class="chart_no-data">
                        <img src="/images/no_chart.svg" alt="no-chart" />
                        <span class="mt-4">Нет данных</span>
                    </div>
                </template>
            </VTabItem>
        </VTabsItems>
    </div>
</template>

<script>
    const colors = [
        '#e6194b',
        '#3cb44b',
        '#ffe119',
        '#4363d8',
        '#f58231',
        '#911eb4',
        '#46f0f0',
        '#f032e6',
        '#bcf60c',
        '#fabebe',
        '#008080',
        '#e6beff',
        '#9a6324',
        '#fffac8',
        '#800000',
        '#aaffc3',
        '#808000',
        '#ffd8b1',
        '#000075',
        '#808080',
    ];

    import { mapState, mapActions } from 'vuex';
    import chartMixin from '~mixins/chart.mixin';
    import verticalLineHook from '~/components/pages/product/Charts/plugins/verticalLineHook';
    import { htmlLegendPlugin } from '~/components/pages/product/Charts/plugins/legend';
    import { externalTooltipHandler } from '~/components/pages/product/Charts/plugins/tooltips';
    import _ from 'lodash';
    import { ru } from 'date-fns/locale';
    import BaseChart from '~/components/ui/BaseChart';
    import DataTable from '~/components/pages/product/Charts/components/DataTable';
    import { closestIndexTo } from 'date-fns';

    export default {
        name: 'ProductChartPositions',
        components: { BaseChart, DataTable },
        mixins: [chartMixin],
        props: {
            selectedTab: {
                type: Number,
                default: 0,
            },
            productSku: {
                type: [Number, String, Object],
                default: null,
            },
            productIdLocal: {
                type: [Number, String],
                default: null,
            },
            productPositions: {
                type: [Object],
                default: null,
            },
        },
        data: () => ({
            requestSelected: null,
            groupKeys: ['category', 'search'],
            chartId: ['chartPositionsId', 'chartRequestsId'],
            filteredDatasetsRequests: [],
            filteredDatasetsPositions: [],
            selectedCharts: [],
            selectedLabels: [],
            selectedDatasets: [],
        }),
        computed: {
            ...mapState({
                getPositions: state => state.marketPlaceChart.marketplace.positions,
                getSearchRequests: state => state.marketPlaceChart.marketplace.search_request,
            }),
            ...mapState('marketPlaceChart', ['datesStartEnd']),
            requestTypes() {
                return [
                    { title: 'Категории', value: 'category' },
                    { title: 'Запросы', value: 'requests' },
                ];
            },
            labelsPositions() {
                return this.createLabels(this.getPositions);
            },
            labelsRequests() {
                return this.createLabels(this.getSearchRequests);
            },
            labelsOzon: {
                get() {
                    if (this.productPositions) {
                        return Object.values(this.productPositions)[0].map(({ date }) => date);
                    }
                    return [];
                },
                set(val) {
                    return val;
                },
            },
            datasetsPositions() {
                return this.createDatasets(
                    this.getPositions,
                    this.labelsPositions,
                    this.groupKeys[0]
                );
            },
            datasetsRequests() {
                return this.createDatasets(
                    this.getSearchRequests,
                    this.labelsRequests,
                    this.groupKeys[1]
                );
            },
            datasetsOzon: {
                get() {
                    if (this.productPositions) {
                        return Object.entries(this.productPositions).map(
                            ([label, data], index) => ({
                                label,
                                fill: false,
                                borderColor: colors[index],
                                backgroundColor: colors[index],
                                position: 'left',
                                tension: 0.5,
                                data: data.map(({ position }) => position),
                            })
                        );
                    }
                    return [];
                },
                set(val) {
                    return val;
                },
            },
            chartDataPositions() {
                return {
                    labels: this.labelsPositions,
                    datasets: this.datasetsPositions,
                };
            },
            chartDataRequests() {
                return {
                    labels: this.labelsRequests,
                    datasets: this.filteredDatasetsRequests,
                };
            },
            chartDataOzon() {
                return {
                    labels: this.selectedLabels,
                    datasets: this.selectedDatasets,
                };
            },
            chartOptionsPositions() {
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
                        y: {
                            min: 1,
                            reverse: true,
                            ticks: {
                                callback: function (value) {
                                    return value.toLocaleString().replace(/,/g, ' ');
                                },
                            },
                        },
                    },
                    plugins: {
                        legend: {
                            display: false,
                        },
                        htmlLegend: {
                            containerID: 'legend-container',
                        },
                        tooltip: {
                            enabled: false,
                            position: 'nearest',
                            external: externalTooltipHandler,
                        },
                    },
                };
            },
            chartOptionsRequests() {
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
                        y: {
                            min: 1,
                            reverse: true,
                            ticks: {
                                callback: function (value) {
                                    return value.toLocaleString().replace(/,/g, ' ');
                                },
                            },
                        },
                    },
                    plugins: {
                        legend: {
                            display: false,
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
                        y: {
                            min: 1,
                            reverse: true,
                            ticks: {
                                callback: function (value) {
                                    return value.toLocaleString().replace(/,/g, ' ');
                                },
                            },
                        },
                    },
                    plugins: {
                        legend: {
                            display: false,
                        },
                        htmlLegend: {
                            containerID: 'legend-container-ozon',
                        },
                        tooltip: {
                            enabled: false,
                            external: externalTooltipHandler,
                        },
                    },
                };
            },
            chartPluginPositions() {
                return [verticalLineHook, htmlLegendPlugin];
            },
            chartPluginRequests() {
                return [verticalLineHook];
            },
            isChartData() {
                return (
                    this.chartDataOzon.datasets.length > 0 &&
                    this.chartDataOzon.datasets
                        .map(({ data }) => data)
                        .every(item => item.length > 0)
                );
            },
            labelsOzonDates() {
                return this.labelsOzon.map(el => new Date(el));
            },
        },
        watch: {
            selectedTab(val) {
                if (this.isSelectedMp.id === 2) {
                    this.requestSelected = val - 1;
                }
            },
            selectedDates(val) {
                if (this.isSelectedMp.id === 1) {
                    if (val) {
                        const [start_date, end_date] = val;
                        const startIndex = closestIndexTo(start_date, this.labelsOzonDates) || 0;
                        const endIndex =
                            closestIndexTo(end_date, this.labelsOzonDates) ||
                            this.labelsOzon.length - 1;
                        if (startIndex !== -1 && endIndex !== -1) {
                            this.selectedLabels = this.labelsOzon.slice(startIndex, endIndex + 1);
                            this.selectedDatasets = this.datasetsOzon.map(dataset => ({
                                ...dataset,
                                data: dataset.data.slice(startIndex, endIndex + 1),
                            }));
                        } else {
                            this.selectedLabels = this.labelsOzon;
                            this.selectedDatasets = this.datasetsOzon;
                        }
                    } else if (val === null) {
                        this.selectedLabels = this.labelsOzon;
                        this.selectedDatasets = this.datasetsOzon;
                    }
                }
                this.handlePeriod(val);
                if (val === null) {
                    this.selectedPeriod = null;
                }
            },
            chartParams(val) {
                if (val) {
                    if (this.isSelectedMp.id === 2) {
                        this.fetchDataChartWB({ sku: this.productSku, params: val });
                    } else {
                        this.fetchDataChartPositionsOzon({ id: this.productIdLocal, params: val });
                    }
                }
            },
        },
        created() {
            if (this.isSelectedMp.id === 2) {
                this.requestSelected = 1;
            }
        },
        methods: {
            ...mapActions({
                fetchDataChartPositionsOzon: 'marketPlaceChart/fetchDataChartPositionsOzon',
            }),
            addMissingDates(dates, arr, groupKey) {
                const find = date => arr.find(x => x.date === date);
                const make = date => ({
                    groupKey: arr[0][groupKey],
                    date,
                    position: null,
                });
                const get = date => find(date) || make(date);

                return dates.map(get).sort((a, b) => new Date(a.date) - new Date(b.date));
            },
            createLabels(data) {
                if (data) {
                    return this.getAllDatesInRange(this.datesStartEnd);
                }
                return [];
            },
            createDatasets(data, labels, groupKey) {
                const datasets = [];
                if (data) {
                    const group = _.chain(data).groupBy(groupKey).value();
                    Object.values(group).forEach(item => {
                        const data = [];
                        const obj = this.addMissingDates(labels, item, groupKey).reduce(
                            (obj, value) => {
                                data.push(value.position);
                                return {
                                    ...obj,
                                    fill: false,
                                    tension: 0.5,
                                    label: value[groupKey] || value.groupKey,
                                    data,
                                };
                            },
                            {}
                        );
                        datasets.push(obj);
                    });
                    return datasets
                        .filter(({ label }) => label)
                        .map((dataset, index) => ({
                            ...dataset,
                            backgroundColor: colors[index],
                            borderColor: colors[index],
                        }));
                }
            },
            updateChartPositions(val) {
                const index = this.selectedCharts.findIndex(item => val === item);
                if (index === -1) {
                    this.selectedCharts.push(val);
                } else {
                    this.selectedCharts.splice(index, 1);
                }
                this.filteredDatasetsPositions = this.datasetsPositions.filter(({ label }) =>
                    this.selectedCharts.includes(label)
                );
            },
            updateChartRequests(val) {
                this.filteredDatasetsRequests = this.datasetsRequests.filter(({ label }) =>
                    val.some(obj => obj.label === label)
                );
            },
            getAllDatesInRange(datesStartEnd) {
                const [start, end] = datesStartEnd;
                const arr = [];
                for (let dt = new Date(start); dt <= end; dt.setDate(dt.getDate() + 1)) {
                    const [date] = new Date(dt).toISOString().split('T');
                    arr.push(date);
                }
                return arr;
            },
        },
    };
</script>

<style scoped lang="scss">
    .filters {
        gap: 16px;

        @include md {
            padding: 12px 16px;
            background-color: $white;
        }

        &_container {
            width: 100%;
        }

        &_heading {
            @extend %text-h5;

            font-size: 20px;
        }

        &_controls {
            display: flex;
            gap: 8px;

            &-wrapper {
                width: 115px;
                max-width: 115px;
            }
        }

        &_tabs {
            max-width: fit-content;
            border-radius: 8px;
            border: 2px solid black;

            &__tab {
                height: 36px;
                border-radius: 5px;
                border: 2px solid black;
                text-transform: unset;
                letter-spacing: normal;

                &:before {
                    opacity: 0 !important;
                }
            }
        }
    }

    span {
        cursor: default;
    }

    .chart {
        height: 400px;
        margin-top: 16px;

        @include md {
            height: 226px;
            margin-top: 0;
            padding: 0 16px 12px;
            background-color: $white;
        }

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
    }

    .theme--dark.v-tabs > .v-tabs-bar .v-tab:not(.v-tab--active) {
        color: $white;

        &:hover {
            background: $base-900;
        }
    }
</style>
