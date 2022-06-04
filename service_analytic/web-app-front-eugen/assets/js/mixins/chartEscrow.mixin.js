import { roundNumber } from '~utils/numbers.utils';

export default {
    data: () => ({
        chartProductsType: 'doughnut',
    }),
    computed: {
        chartInnerPercentagePlugin() {
            return [
                {
                    beforeDraw: chart => {
                        const width = chart.width;
                        const height = chart.height;
                        const ctx = chart.ctx;
                        const text = `${roundNumber(this.percentage, 1)}%`;
                        ctx.restore();
                        ctx.font = '40px Manrope';
                        ctx.textBaseline = 'middle';
                        const textX = Math.round((width - ctx.measureText(text).width) / 2);
                        const textY = height / 2;
                        ctx.fillText(text, textX, textY);
                        ctx.save();
                    },
                },
            ];
        },
        chartOptions() {
            return {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        enabled: false,
                    },
                },
                cutout: '85%',
                elements: {
                    arc: {
                        borderRadius: this.percentage === 100 || this.percentage === 0 ? 0 : [30, 0],
                    }
                }
            };
        },
    },
};
