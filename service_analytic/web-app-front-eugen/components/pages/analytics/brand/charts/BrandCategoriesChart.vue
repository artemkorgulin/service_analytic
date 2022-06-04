<template>
    <div class="categories_container">
        <div class="categories_charts">
            <BaseChart
                v-if="datasetsProducts && labels.length > 0"
                ref="categoriesProducts"
                :chart-height="256"
                :chart-width="256"
                :chart-plugins="chartPluginsProducts"
                :chart-type="chartProductsType"
                :chart-id="chartId[0]"
                :chart-data="chartDataProducts"
                :chart-options="chartOptionsCategories"
            />
            <BaseChart
                v-if="datasetsSales && labels.length > 0"
                ref="categoriesSales"
                :chart-height="256"
                :chart-width="256"
                :chart-plugins="chartPluginsSales"
                :chart-type="chartProductsType"
                :chart-id="chartId[1]"
                :chart-data="chartDataSales"
                :chart-options="chartOptionsCategories"
            />
            <BaseChart
                v-if="datasetsRevenue && labels.length > 0"
                ref="categoriesRevenue"
                :chart-height="256"
                :chart-width="256"
                :chart-plugins="chartPluginsRevenue"
                :chart-type="chartProductsType"
                :chart-id="chartId[2]"
                :chart-data="chartDataRevenue"
                :chart-options="chartOptionsCategories"
            />
        </div>
        <div id="categories_legend"></div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';
    import chartBrandMixin from '~mixins/chartBrand.mixin';
    import BaseChart from '~/components/ui/BaseChart';
    import { externalTooltipHandler } from '~/components/pages/product/Charts/plugins/tooltips';

    export default {
        name: 'BrandCategoriesChart',
        components: {
            BaseChart,
        },
        mixins: [chartBrandMixin],
        data: () => ({
            chartId: ['categoriesProductsId', 'categoriesRevenueId', 'categoriesSalesId'],
        }),
        computed: {
            ...mapGetters('analytics', ['chartDataCategories']),
            labels() {
                return this.chartDataCategories.map(({ category }) => category);
            },
            datasetsProducts() {
                return [
                    {
                        backgroundColor: this.chartDataCategories.map(
                            ({ backgroundColor }) => backgroundColor
                        ),
                        data: this.chartDataCategories.map(({ products }) => products),
                    },
                ];
            },
            datasetsSales() {
                return [
                    {
                        backgroundColor: this.chartDataCategories.map(
                            ({ backgroundColor }) => backgroundColor
                        ),
                        data: this.chartDataCategories.map(({ sales }) => sales),
                    },
                ];
            },
            datasetsRevenue() {
                return [
                    {
                        backgroundColor: this.chartDataCategories.map(
                            ({ backgroundColor }) => backgroundColor
                        ),
                        data: this.chartDataCategories.map(({ revenue }) => revenue),
                    },
                ];
            },
            chartDataProducts() {
                return {
                    labels: this.labels,
                    datasets: this.datasetsProducts,
                };
            },
            chartDataSales() {
                return {
                    labels: this.labels,
                    datasets: this.datasetsSales,
                };
            },
            chartDataRevenue() {
                return {
                    labels: this.labels,
                    datasets: this.datasetsRevenue,
                };
            },
            chartPluginsProducts() {
                return this.createChartPlugin('Товары');
            },
            chartOptionsCategories() {
                return {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                        htmlLegend: {
                            containerID: 'categories_legend',
                        },
                        tooltip: {
                            enabled: false,
                            external: externalTooltipHandler,
                        },
                    },
                };
            },
        },
    };
</script>

<style scoped lang="scss">
    .categories {
        &_container {
            display: flex;
            align-items: center;
            justify-content: space-around;
            padding: 50px 0;

            @media screen and (max-width: 960px) {
                flex-direction: column;
                padding: 50px 0 0 0;
            }
        }

        &_charts {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(246px, 1fr));
            gap: 16px;
            width: 100%;
        }
    }

    #categories_legend {
        display: flex;
        justify-content: center;
        min-width: 420px;
    }
</style>
