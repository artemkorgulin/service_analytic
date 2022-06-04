import { htmlLegendPlugin } from '~/components/pages/product/Charts/plugins/legend';

export default {
    data: () => ({
        chartProductsType: 'doughnut',
    }),
    computed: {
        chartPluginsSales() {
            return this.createChartPlugin('Продажи');
        },
        chartPluginsRevenue() {
            return this.createChartPlugin('Выручка');
        },
    },
    methods: {
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
