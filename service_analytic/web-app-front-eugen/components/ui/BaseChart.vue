<script>
    import 'chartjs-adapter-date-fns';

    import { Chart, registerables } from 'chart.js';
    import { reactiveProp } from '~mixins/chart';
    Chart.register(...registerables);
    export default {
        name: 'BaseChart',
        mixins: [reactiveProp],
        props: {
            chartId: {
                default: 'chartId',
                type: String,
            },
            chartWidth: {
                default: '100%',
                type: [Number, String],
            },
            chartHeight: {
                default: '400',
                type: [Number, String],
            },
            chartType: {
                default: 'line',
                type: String,
            },
            chartPlugins: {
                default: () => [],
                type: Array,
            },
        },
        data() {
            return {
                _chart: null,
                _plugins: this.plugins,
            };
        },
        mounted() {
            return this.renderChart();
        },
        beforeDestroy() {
            this.closeTooltipsForcebly();
        },
        methods: {
            renderChart() {
                const data = this.chartData;
                const options = this.chartOptions;
                if (this._chart) {
                    this._chart.destroy();
                }
                if (!this.$refs.canvas) {
                    throw new Error(
                        'Please remove the <template></template> tags from your chart component.'
                    );
                }
                this.$data._chart = new Chart(this.$refs.canvas.getContext('2d'), {
                    type: this.chartType,
                    data,
                    options,
                    plugins: this.chartPlugins,
                });
            },
            closeTooltipsForcebly() {
                const activeElements = this.$data._chart?.tooltip?._active;
                if (activeElements == undefined || activeElements.length == 0) {
                    return false;
                }
                const requestedElem = this.$data._chart.getDatasetMeta(0).data[0];
                for (let i = 0; i < activeElements.length; i++) {
                    if (requestedElem._index == activeElements[i]._index) {
                        activeElements.splice(i, 1);
                        break;
                    }
                }
                this.$data._chart.tooltip.update(true);
                this.$data._chart.draw();
            },
        },
        render(createElement) {
            return createElement(
                'div',
                {
                    style: this.styles,
                },
                [
                    createElement('canvas', {
                        attrs: {
                            id: this.chartId,
                            width: this.chartWidth,
                            height: this.chartHeight,
                        },
                        ref: 'canvas',
                    }),
                ]
            );
        },
    };
</script>
