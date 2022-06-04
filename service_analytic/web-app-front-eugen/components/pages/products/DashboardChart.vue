<template>
    <div class="charts mb-4">
        <h2 class="charts__title mb-4">
            Данные за 30 дней. Нажмите на сегмент, чтобы отфильтровать таблицу
        </h2>
        <div class="charts__container">
            <div class="charts__item">
                <div class="charts__item-header mb-4">
                    <h3 class="charts__item-header_title">
                        {{ chartTitle[0] }}
                    </h3>
                    <span
                        v-if="dashboardFilter.optimization"
                        class="charts__item-header_filter"
                        @click="resetFilter"
                    >
                        <SvgIcon
                            name="outlined/checked"
                            style="width: 12px; height: 12px; margin-right: 10px"
                            data-right
                        />
                        Фильтр
                    </span>
                </div>
                <div class="d-flex flex-column">
                    <div class="d-flex justify-space-between chart-data">
                        <VProgressCircular
                            v-if="isLoading.optimization"
                            indeterminate
                            size="50"
                            color="accent"
                            class="charts__item-loader"
                        />
                        <BaseChart
                            v-if="isChartDataOptimization"
                            ref="optimization"
                            :chart-height="140"
                            :chart-width="140"
                            :chart-options="chartOptions"
                            :chart-type="chartProductsType"
                            :chart-id="chartId[0]"
                            :chart-data="chartDataOptimization"
                        />
                        <DashboardChartEmpty v-else-if="!isChartDataOptimization" />
                        <v-radio-group v-model="selectedOptimizationId" mandatory class="mr-14">
                            <v-radio
                                v-for="item in radioGroupOptimization"
                                :key="item.id"
                                v-ripple="false"
                                class="charts__item-radio"
                                :label="item.label"
                                :value="item.id"
                            ></v-radio>
                        </v-radio-group>
                    </div>
                    <p class="charts__item-info mt-4">
                        Товары, бренды или категории представлены по степени оптимизации карточек.
                        Отредактируйте карточки из красного сегмента, чтобы повысить степень
                        оптимизации
                    </p>
                </div>
            </div>
            <div class="charts__item">
                <div class="charts__item-header mb-4">
                    <h3 class="charts__item-header_title">
                        {{ chartTitle[1] }}
                    </h3>
                    <span
                        v-if="dashboardFilter.revenue"
                        class="charts__item-header_filter"
                        @click="resetFilter"
                    >
                        <SvgIcon
                            name="outlined/checked"
                            style="width: 12px; height: 12px; margin-right: 10px"
                            data-right
                        />
                        Фильтр
                    </span>
                </div>
                <div class="d-flex flex-column">
                    <div class="d-flex justify-space-between chart-data">
                        <VProgressCircular
                            v-if="isLoading.revenue"
                            indeterminate
                            size="50"
                            color="accent"
                            class="charts__item-loader"
                        />
                        <BaseChart
                            v-if="isChartDataRevenue"
                            ref="revenue"
                            :chart-height="140"
                            :chart-width="140"
                            :chart-options="chartOptions"
                            :chart-type="chartProductsType"
                            :chart-id="chartId[1]"
                            :chart-data="chartDataRevenue"
                        />
                        <DashboardChartEmpty v-else-if="!isChartDataRevenue" />
                        <v-radio-group v-model="selectedRevenueId" mandatory class="mr-14">
                            <v-radio
                                v-for="item in radioGroupRevenue"
                                :key="item.id"
                                v-ripple="false"
                                class="charts__item-radio"
                                :label="item.label"
                                :value="item.id"
                            ></v-radio>
                        </v-radio-group>
                    </div>
                    <p class="charts__item-info mt-4">
                        Товары, бренды или категории представлены по доле в общей сумме продаж. В
                        красном сегменте находятся товары, которые приносят меньше всего выручки
                    </p>
                </div>
            </div>
            <div class="charts__item">
                <div class="charts__item-header mb-4">
                    <h3 class="charts__item-header_title">
                        {{ chartTitle[2] }}
                    </h3>
                    <span
                        v-if="dashboardFilter.ordered"
                        class="charts__item-header_filter"
                        @click="resetFilter"
                    >
                        <SvgIcon
                            name="outlined/checked"
                            style="width: 12px; height: 12px; margin-right: 10px"
                            data-right
                        />
                        Фильтр
                    </span>
                </div>
                <div class="d-flex flex-column">
                    <div class="d-flex justify-space-between chart-data">
                        <VProgressCircular
                            v-if="isLoading.ordered"
                            indeterminate
                            size="50"
                            color="accent"
                            class="charts__item-loader"
                        />
                        <BaseChart
                            v-if="isChartDataOrdered"
                            ref="ordered"
                            :chart-height="140"
                            :chart-width="140"
                            :chart-options="chartOptions"
                            :chart-type="chartProductsType"
                            :chart-id="chartId[2]"
                            :chart-data="chartDataOrdered"
                        />
                        <DashboardChartEmpty v-else-if="!isChartDataOrdered" />
                        <v-radio-group v-model="selectedOrderedId" mandatory class="mr-14">
                            <v-radio
                                v-for="item in radioGroupOrdered"
                                :key="item.id"
                                v-ripple="false"
                                class="charts__item-radio"
                                :label="item.label"
                                :value="item.id"
                            ></v-radio>
                        </v-radio-group>
                    </div>
                    <p class="charts__item-info mt-4">
                        Товары, бренды или категории представлены по доле в общем количестве
                        заказов. В красном сегменте находятся товары, которые реже всего заказывают
                        покупатели
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    /* eslint-disable */
    import { mapState, mapGetters, mapActions, mapMutations } from 'vuex';
    import BaseChart from '~/components/ui/BaseChart';
    import DashboardChartEmpty from '~/components/pages/products/DashboardChartEmpty';
    import { externalTooltipHandler } from '~/components/pages/product/Charts/plugins/tooltips';

    export default {
        name: 'DashboardChart',
        components: {
            BaseChart,
            DashboardChartEmpty,
        },
        data: () => ({
            chartProductsType: 'doughnut',
            selectedOptimizationId: null,
            selectedRevenueId: null,
            selectedOrderedId: null,
            radioGroupOptimization: [
                { id: 1, label: 'Товары', type: 'product_optimization' },
                { id: 2, label: 'Бренды', type: 'brand_optimization' },
                { id: 3, label: 'Категории', type: 'category_optimization' },
            ],
            radioGroupRevenue: [
                { id: 4, label: 'Товары', type: 'product_revenue' },
                { id: 5, label: 'Бренды', type: 'brand_revenue' },
                { id: 6, label: 'Категории', type: 'category_revenue' },
            ],
            radioGroupOrdered: [
                { id: 7, label: 'Товары', type: 'product_ordered' },
                { id: 8, label: 'Бренды', type: 'brand_ordered' },
                { id: 9, label: 'Категории', type: 'category_ordered' },
            ],
            chartTitle: ['Степень оптимизации', 'Продажи в руб.', 'Продажи в шт.'],
            chartId: ['optimizationId', 'revenueId', 'orderedId'],
            backgroundColors: [
                { key: 'bad', backgroundColor: '#FC6E90' },
                { key: 'good', backgroundColor: '#20C274' },
                { key: 'normal', backgroundColor: '#F4E450' },
            ],
        }),
        computed: {
            ...mapState('productsDashboard', ['optimization', 'revenue', 'ordered', 'isLoading']),
            ...mapState('products', ['dashboardFilter']),
            ...mapGetters(['isSelectedMp']),
            selectedOptimization() {
                return this.selectedOptimizationId
                    ? this.radioGroupOptimization.find(
                          ({ id }) => id === this.selectedOptimizationId
                      )
                    : null;
            },
            selectedRevenue() {
                return this.selectedRevenueId
                    ? this.radioGroupRevenue.find(({ id }) => id === this.selectedRevenueId)
                    : null;
            },
            selectedOrdered() {
                return this.selectedOrderedId
                    ? this.radioGroupOrdered.find(({ id }) => id === this.selectedOrderedId)
                    : null;
            },
            chartDataOptimization() {
                const data = [];
                Object.keys(this.optimization).forEach(key =>
                    data.push({
                        key,
                        value: this.optimization[key]['count'],
                        dashboard: this.selectedOptimization?.type,
                    })
                );

                const dataset = data.map(item => ({
                    ...item,
                    ...this.backgroundColors.find(({ key }) => key === item.key),
                }));

                return {
                    labels: Object.values(this.optimization).map(({ percent }) => `${percent} %`),
                    datasets: [
                        {
                            backgroundColor: dataset.map(({ backgroundColor }) => backgroundColor),
                            data: dataset.map(({ value }) => value),
                            dashboard: dataset.map(({ dashboard }) => dashboard),
                        },
                    ],
                };
            },
            isChartDataOptimization() {
                return this.isChartData(this.optimization);
            },
            chartDataRevenue() {
                const data = [];
                Object.keys(this.revenue).forEach(key =>
                    data.push({
                        key,
                        value: this.revenue[key]['count'],
                        dashboard: this.selectedRevenue?.type,
                    })
                );

                const dataset = data.map(item => ({
                    ...item,
                    ...this.backgroundColors.find(({ key }) => key === item.key),
                }));

                const sum = this.computeSum(this.revenue);
                const computedPercent = (count, sum) => Math.round((count * 100) / sum);

                return {
                    labels: Object.values(this.revenue).map(
                        ({ count }) => `${computedPercent(count, sum)} %`
                    ),
                    datasets: [
                        {
                            backgroundColor: dataset.map(({ backgroundColor }) => backgroundColor),
                            data: dataset.map(({ value }) => value),
                            dashboard: dataset.map(({ dashboard }) => dashboard),
                        },
                    ],
                };
            },
            isChartDataRevenue() {
                return this.isChartData(this.revenue);
            },
            chartDataOrdered() {
                const data = [];
                Object.keys(this.ordered).forEach(key =>
                    data.push({
                        key,
                        value: this.ordered[key]['count'],
                        dashboard: this.selectedOrdered?.type,
                    })
                );

                const dataset = data.map(item => ({
                    ...item,
                    ...this.backgroundColors.find(({ key }) => key === item.key),
                }));

                const sum = this.computeSum(this.ordered);
                const computedPercent = (count, sum) => Math.round((count * 100) / sum);

                return {
                    labels: Object.values(this.ordered).map(
                        ({ count }) => `${computedPercent(count, sum)} %`
                    ),
                    datasets: [
                        {
                            backgroundColor: dataset.map(({ backgroundColor }) => backgroundColor),
                            data: dataset.map(({ value }) => value),
                            dashboard: dataset.map(({ dashboard }) => dashboard),
                        },
                    ],
                };
            },
            isChartDataOrdered() {
                return this.isChartData(this.ordered);
            },
            chartOptions() {
                return {
                    responsive: true,
                    maintainAspectRatio: false,
                    onClick: (e, activeEls) => {
                        const dataIndex = activeEls[0].index;
                        const selectedColor = e.chart.data.datasets[0].backgroundColor[dataIndex];
                        const selectedDashboard = e.chart.data.datasets[0].dashboard[dataIndex];
                        const selectedSegment = this.backgroundColors.find(
                            ({ backgroundColor }) => backgroundColor === selectedColor
                        )['key'];
                        const marketplace = this.isSelectedMp.key;
                        this.fetchFilteredData(selectedDashboard, selectedSegment, marketplace);
                    },
                    onHover: (e, activeEls) => {
                        if (activeEls?.length > 0) {
                            e.native.target.style.cursor = 'pointer';
                        } else {
                            e.native.target.style.cursor = 'auto';
                        }
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
        },
        watch: {
            selectedOptimizationId() {
                const { type } = this.selectedOptimization;
                this.fetchData(type);
            },
            selectedRevenueId() {
                const { type } = this.selectedRevenue;
                this.fetchData(type);
            },
            selectedOrderedId() {
                const { type } = this.selectedOrdered;
                this.fetchData(type);
            },
        },
        methods: {
            ...mapActions('productsDashboard', ['fetchDashboardData']),
            ...mapActions('products', ['loadFilteredDashboardData', 'LOAD_PRODUCTS']),
            ...mapMutations('products', ['setResetDashboardFilters']),
            async fetchData(type) {
                await this.fetchDashboardData({ type });
            },
            async fetchFilteredData(dashboard, segment, marketplace) {
                await this.loadFilteredDashboardData({ dashboard, segment, marketplace });
            },
            isChartData(data) {
                if (data) {
                    const array = Object.values(data).map(({ count }) => count);
                    return array.some(item => Number(item) !== 0);
                }
                return false;
            },
            computeSum(data) {
                return Object.values(data)
                    .map(({ count }) => count)
                    .reduce((acc, item) => acc + item, 0);
            },
            async resetFilter() {
                this.setResetDashboardFilters();
                await this.LOAD_PRODUCTS({
                    type: 'common',
                    reload: true,
                    marketplace: this.isSelectedMp.key,
                });
            },
        },
    };
</script>

<style scoped lang="scss">
    .chart-data {
        flex-wrap: wrap;
    }

    .charts {
        display: flex;
        flex-direction: column;
        padding: 28px;
        border-radius: 24px;
        background-color: $color-light-background;

        @include cardShadow;

        &__title {
            font-family: $heading-font-family;
            font-size: 16px;
            font-style: normal;
            font-weight: 400;
            line-height: 24px;
            color: $base-700;
        }

        &__container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 19px;
            grid-template-rows: auto;

            @media screen and (max-width: 800px) {
                grid-template-columns: repeat(1, 1fr);
            }
        }

        &__item {
            position: relative;
            display: flex;
            flex-direction: column;
            padding: 23px 31px;
            border-radius: 24px;
            background-color: $color-light-background;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);

            &-loader {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
            }

            &-header {
                display: flex;
                justify-content: space-between;

                &_title {
                    min-height: 54px;
                    font-family: $heading-font-family;
                    font-size: 20px;
                    font-style: normal;
                    font-weight: 500;
                    line-height: 27px;
                }

                &_filter {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding: 5px 13px;
                    background-color: $white;
                    font-family: $heading-font-family;
                    font-size: 16px;
                    font-style: normal;
                    font-weight: 400;
                    line-height: 24px;
                    color: $primary-500;
                    cursor: pointer;
                }
            }

            &-radio {
                font-family: $heading-font-family;
                font-size: 16px;
                font-style: normal;
                font-weight: 400;
            }

            &-info {
                font-family: $heading-font-family;
                font-weight: 400;
                font-size: 14px;
                line-height: 19px;
                color: $base-700;
            }
        }
    }
</style>
