<template>
    <div ref="ProductStat" class="product-stat">
        <ProductStatElement v-for="(statisticEl, i) in statistics" :key="i" :el="statisticEl" />
    </div>
</template>

<script>
    import { mapState, mapGetters } from 'vuex';
    import ProductStatElement from '~/components/pages/product/ProductStatElement.vue';

    export default {
        name: 'ProductStat',
        components: {
            ProductStatElement,
        },
        props: {
            options: {
                type: [Array, Boolean],
                default: false,
            },
            rating: {
                type: [Number, Boolean],
                default: false,
            },
            metrics: {
                type: [Object, Boolean],
                default: false,
            },
        },
        data() {
            return {
                statisticsSubtitle: ['Среднее', 'Сумма'],
            };
        },
        computed: {
            ...mapState({
                getStatistics: state => state.marketPlaceChart.marketplace.statistics,
            }),
            ...mapGetters({
                productHasOptions: 'isProductHasOptions',
            }),
            statistics() {
                let res = [];
                const currentStatistics = this.getStatistics || {};

                if (this.productHasOptions && this.options.length) {
                    res = [
                        {
                            title: 'Позиция в категориях',
                            value: Math.floor(currentStatistics.position_end) || 0,
                            change: Math.floor(
                                currentStatistics.position_start - currentStatistics.position_end
                            ),
                            label: this.statisticsSubtitle[0].toUpperCase(),
                            iconName: 'position-gradient',
                        },
                        {
                            title: 'Позиция в поиске',
                            value: Math.floor(currentStatistics.search_request_end) || 0,
                            change: Math.floor(
                                currentStatistics.search_request_start -
                                    currentStatistics.search_request_end
                            ),
                            label: this.statisticsSubtitle[0].toUpperCase(),
                            iconName: 'search-gradient',
                        },
                        {
                            title: 'Заказы',
                            value: currentStatistics.total_sales || 0,
                            label: this.statisticsSubtitle[1].toUpperCase(),
                            iconName: 'bag-gradient',
                        },
                        {
                            title: 'Выручка, ₽',
                            value: currentStatistics.proceeds || 0,
                            label: this.statisticsSubtitle[1].toUpperCase(),
                            iconName: 'money-gradient',
                        },
                        {
                            title: 'Рейтинг',
                            value: currentStatistics.rating_end || 0,
                            change: currentStatistics.rating_end - currentStatistics.rating_start,
                            label: this.statisticsSubtitle[0].toUpperCase(),
                            iconName: 'star-gradient',
                        },
                    ];
                } else {
                    res = [
                        {
                            title: 'Позиция в категориях',
                            value: this.metrics.position_category || 0,
                            label: this.statisticsSubtitle[0].toUpperCase(),
                            iconName: 'position-gradient',
                        },
                        {
                            title: 'Просмотры',
                            value: this.metrics.hits_view || 0,
                            label: this.statisticsSubtitle[0].toUpperCase(),
                            iconName: 'views-gradient',
                        },
                        {
                            title: 'Заказы',
                            value: this.metrics.ordered_units || 0,
                            label: this.statisticsSubtitle[1].toUpperCase(),
                            iconName: 'bag-gradient',
                        },
                        {
                            title: 'Выручка',
                            value: this.metrics.revenue || 0,
                            label: this.statisticsSubtitle[1].toUpperCase(),
                            iconName: 'money-gradient',
                        },
                        {
                            title: 'Рейтинг',
                            value: this.rating || 0,
                            label: this.statisticsSubtitle[0].toUpperCase(),
                            iconName: 'star-gradient',
                        },
                        {
                            title: 'Конверсия в карточку',
                            value: this.metrics.conv_tocart_pdp || 0,
                            label: this.statisticsSubtitle[0].toUpperCase(),
                            iconName: 'folder-gradient',
                        },
                        {
                            title: 'Конверсия в корзину',
                            value: this.metrics.conv_tocart || 0,
                            label: this.statisticsSubtitle[0].toUpperCase(),
                            iconName: 'cart-gradient',
                        },
                    ];
                }

                return res;
            },
        },
        mounted() {
            this.toggleFullWidthEl();
            window.onresize = () => {
                this.toggleFullWidthEl();
            };
        },
        methods: {
            toggleFullWidthEl() {
                const productStat = this.$refs.ProductStat;
                const productStatWidth = productStat.offsetWidth;
                if (productStatWidth < 550) {
                    productStat.classList.add('product-stat--full-width-element');
                } else {
                    productStat.classList.remove('product-stat--full-width-element');
                }
            },
        },
    };
</script>

<style lang="scss" scoped>
    .product-stat {
        box-sizing: border-box;
        display: flex;
        align-content: center;
        flex-wrap: wrap;
        gap: 16px;
        width: 100%;
        margin: 8px;
        padding: 16px;
        border-radius: 16px;
        background-color: #fff;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
    }

    .product-stat.product-stat--full-width-element .product-stat__element {
        max-width: 100%;
    }
</style>
