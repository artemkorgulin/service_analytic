<template>
    <div class="tabs-cat">
        <div class="tabs-cat__title">Все подкатегории</div>

        <div class="tabs-cat__loader">
            <div v-if="isLoadSubcategories" class="tabs-cat__loader-circular">
                <VProgressCircular indeterminate size="50" color="accent" />
            </div>
            <div v-else class="tabs-cat__charts">
                <BaseChart
                    v-if="subcategoriesList && labels.length > 0"
                    ref="categoriesProducts"
                    :chart-height="256"
                    :chart-width="256"
                    :chart-id="chartId[0]"
                    :chart-options="chartOptions"
                    :chart-type="chartProductsType"
                    :chart-data="chartDataProducts"
                    :chart-plugins="chartPluginsProducts"
                />
                <BaseChart
                    v-if="subcategoriesList && labels.length > 0"
                    ref="categoriesSales"
                    :chart-height="256"
                    :chart-width="256"
                    :chart-id="chartId[1]"
                    :chart-options="chartOptions"
                    :chart-type="chartProductsType"
                    :chart-data="chartDataSales"
                    :chart-plugins="chartPluginsSales"
                />
                <BaseChart
                    v-if="subcategoriesList && labels.length > 0"
                    ref="categoriesRevenue"
                    :chart-height="256"
                    :chart-width="256"
                    :chart-id="chartId[2]"
                    :chart-options="chartOptions"
                    :chart-type="chartProductsType"
                    :chart-data="chartDataRevenue"
                    :chart-plugins="chartPluginsRevenue"
                />
                <div id="categories-analysis_chart_legend"></div>
            </div>
            <SeTableAG :columns="subcategoriesColumns" :rows="subcategoriesList" :page-size="50" />
        </div>
    </div>
</template>
<script>
    // import SeTableAG from '~/components/ui/SeTableAG.vue';
    import BaseChart from '~/components/ui/BaseChart.vue';
    import { htmlLegendPlugin } from '~/components/pages/product/Charts/plugins/legend';
    import { externalTooltipHandler } from '~/components/pages/product/Charts/plugins/tooltips';
    import { mapActions, mapState, mapGetters } from 'vuex';

    export default {
        components: {
            BaseChart,
            // SeTableAG,
        },

        data: () => ({
            chartId: ['categoriesProductsId', 'categoriesRevenueId', 'categoriesSalesId'],
            chartProductsType: 'doughnut',
            colHide: ['subject'],
        }),

        computed: {
            ...mapState('categories-analitik', [
                'subcategories',
                'subcategoriesColums',
                'isLoadSubcategories',
            ]),
            ...mapGetters('categories-analitik', [
                'subcategoriesList',
                'chartDataSubcategories',
                'subcategoriesColumns',
            ]),

            labels() {
                return this.chartDataSubcategories.map(({ category }) => category);
            },

            columnDefs() {
                return this.subcategoriesColums.map(col => ({
                    ...col,
                    hide: this.colHide.includes(col.field),
                    sortable: true,
                }));
            },

            chartOptions() {
                return {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                        htmlLegend: {
                            containerID: 'categories-analysis_chart_legend',
                        },
                        tooltip: {
                            enabled: false,
                            external: externalTooltipHandler,
                        },
                    },
                };
            },

            chartDataProducts() {
                return {
                    labels: this.labels,
                    datasets: [
                        {
                            backgroundColor: this.chartDataSubcategories.map(
                                ({ backgroundColor }) => backgroundColor
                            ),
                            data: this.chartDataSubcategories.map(({ products }) =>
                                Number(products)
                            ),
                        },
                    ],
                };
            },
            chartDataSales() {
                return {
                    labels: this.labels,
                    datasets: [
                        {
                            backgroundColor: this.chartDataSubcategories.map(
                                ({ backgroundColor }) => backgroundColor
                            ),
                            data: this.chartDataSubcategories.map(({ sales }) => Number(sales)),
                        },
                    ],
                };
            },
            chartDataRevenue() {
                return {
                    labels: this.labels,
                    datasets: [
                        {
                            backgroundColor: this.chartDataSubcategories.map(
                                ({ backgroundColor }) => backgroundColor
                            ),
                            data: this.chartDataSubcategories.map(({ revenue }) => Number(revenue)),
                        },
                    ],
                };
            },

            chartPluginsProducts() {
                return this.createChartPlugin('Товары');
            },
            chartPluginsSales() {
                return this.createChartPlugin('Продажи');
            },
            chartPluginsRevenue() {
                return this.createChartPlugin('Выручка');
            },
        },

        async mounted() {
            await this.loadAnalyticsSubcategoriesWildberries();
        },

        methods: {
            ...mapActions('categories-analitik', ['loadAnalyticsSubcategoriesWildberries']),

            createChartPlugin(text) {
                return [
                    {
                        beforeDraw: chart => {
                            const width = chart.width;
                            const height = chart.height;
                            const ctx = chart.ctx;
                            ctx.restore();
                            ctx.font = '16px Manrope';
                            ctx.textBaseline = 'middle';
                            const textX = Math.round((width - ctx.measureText(text).width) / 2);
                            const textY = height / 2;
                            ctx.fillText(text, textX, textY);
                            ctx.save();
                        },
                    },
                    htmlLegendPlugin,
                ];
            },
        },
    };
</script>
<style lang="scss" scoped>
    .tabs-cat {
        padding: 16px;

        &__title {
            font-size: 20px;
            font-style: normal;
            font-weight: 500;
            line-height: 27px;
            color: #2f3640;
        }

        &__charts {
            display: flex;
            justify-content: space-around;
            padding: 50px 0;
        }

        &__loader {
            position: relative;

            &-circular {
                position: absolute;
                top: 0;
                left: 0;
                z-index: 99;
                display: flex;
                align-items: center;
                justify-content: center;
                width: 100%;
                height: 100%;
                background: rgba(255, 255, 255, 0.4);
            }
        }

        &__more-info {
            display: flex;
            justify-content: flex-end;
            padding-top: 16px;

            &-title {
                font-size: 12px;
                font-style: normal;
                font-weight: normal;
                line-height: 16px;
                color: #7e8793;
            }

            &-value {
                padding: 0 10px 0 4px;
                font-size: 12px;
                font-style: normal;
                font-weight: bold;
                line-height: 16px;
                color: #2f3640;
            }
        }
    }
</style>
