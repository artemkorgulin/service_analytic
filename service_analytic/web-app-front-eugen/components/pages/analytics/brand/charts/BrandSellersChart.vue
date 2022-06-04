<template>
    <div class="sellers_container">
        <div class="sellers_charts">
            <BaseChart
                v-if="datasetsSales && labels.length > 0"
                ref="sellersSales"
                :chart-height="256"
                :chart-width="256"
                :chart-plugins="chartPluginsSales"
                :chart-type="chartProductsType"
                :chart-id="chartId[0]"
                :chart-data="chartDataSales"
                :chart-options="chartOptionsSellers"
            />
            <BaseChart
                v-if="datasetsRevenue && labels.length > 0"
                ref="sellersRevenue"
                :chart-height="256"
                :chart-width="256"
                :chart-plugins="chartPluginsRevenue"
                :chart-type="chartProductsType"
                :chart-id="chartId[1]"
                :chart-data="chartDataRevenue"
                :chart-options="chartOptionsSellers"
            />
        </div>
        <div id="sellers_legend"></div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';
    import chartBrandMixin from '~mixins/chartBrand.mixin';
    import BaseChart from '~/components/ui/BaseChart';
    import { externalTooltipHandler } from '~/components/pages/product/Charts/plugins/tooltips';

    export default {
        name: 'BrandSellersChart',
        components: {
            BaseChart,
        },
        mixins: [chartBrandMixin],
        data: () => ({
            chartId: ['sellersSalesId', 'sellersRevenueId'],
        }),
        computed: {
            ...mapGetters('analytics', ['chartDataSellers']),
            labels() {
                return this.chartDataSellers.map(({ supplier_name }) => supplier_name);
            },
            datasetsSales() {
                return [
                    {
                        backgroundColor: this.chartDataSellers.map(
                            ({ backgroundColor }) => backgroundColor
                        ),
                        data: this.chartDataSellers.map(({ sales }) => sales),
                    },
                ];
            },
            datasetsRevenue() {
                return [
                    {
                        backgroundColor: this.chartDataSellers.map(
                            ({ backgroundColor }) => backgroundColor
                        ),
                        data: this.chartDataSellers.map(({ revenue }) => revenue),
                    },
                ];
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
            chartOptionsSellers() {
                return {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                        htmlLegend: {
                            containerID: 'sellers_legend',
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
    .sellers {
        &_container {
            display: flex;
            align-items: center;
            justify-content: space-around;
            padding: 50px 0;

            @media screen and (max-width: 960px){
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

    #sellers_legend {
        display: flex;
        justify-content: center;
        min-width: 420px;
    }
</style>
