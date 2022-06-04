/* eslint-disable no-prototype-builtins */

function dataHandler(newData, oldData) {
    if (oldData) {
        const chart = this.$data._chart;

        // Get new and old DataSet Labels
        const newDatasetLabels = newData.datasets.map(dataset => dataset.label);

        const oldDatasetLabels = oldData.datasets.map(dataset => dataset.label);

        // Stringify 'em for easier compare
        const oldLabels = JSON.stringify(oldDatasetLabels);
        const newLabels = JSON.stringify(newDatasetLabels);

        // Check if Labels are equal and if dataset length is equal
        if (newLabels === oldLabels && oldData.datasets.length === newData.datasets.length) {
            newData.datasets.forEach((dataset, i) => {
                // Get new and old dataset keys
                const oldDatasetKeys = Object.keys(oldData.datasets[i]);
                const newDatasetKeys = Object.keys(dataset);

                // Get keys that aren't present in the new data
                const deletionKeys = oldDatasetKeys.filter(
                    key => key !== '_meta' && !newDatasetKeys.includes(key)
                );

                // Remove outdated key-value pairs
                deletionKeys.forEach(deletionKey => {
                    delete chart.data.datasets[i][deletionKey];
                });

                // Update attributes individually to avoid re-rendering the entire chart
                for (const attribute in dataset) {
                    if (dataset.hasOwnProperty(attribute)) {
                        chart.data.datasets[i][attribute] = dataset[attribute];
                    }
                }
            });

            if (newData.hasOwnProperty('labels')) {
                chart.data.labels = newData.labels;
                this.$emit('labels:update');
            }
            if (newData.hasOwnProperty('xLabels')) {
                chart.data.xLabels = newData.xLabels;
                this.$emit('xlabels:update');
            }
            if (newData.hasOwnProperty('yLabels')) {
                chart.data.yLabels = newData.yLabels;
                this.$emit('ylabels:update');
            }
            chart.update();
            this.$emit('chart:update');
        } else {
            if (chart) {
                chart.destroy();
                this.$emit('chart:destroy');
            }
            this.renderChart(this.chartData, this.options);
            this.$emit('chart:render');
        }
    } else {
        if (this.$data._chart) {
            this.$data._chart.destroy();
            this.$emit('chart:destroy');
        }
        this.renderChart(this.chartData, this.options);
        this.$emit('chart:render');
    }
}
function dataHandlerOptions(newData, oldData) {
    // const chart = this.$data._chart;
    // chart.update();
    // console.log(
    //     '🚀 ~ file: chart.js ~ line 70 ~ dataHandlerOptions ~ newData, oldData',
    //     newData,
    //     oldData
    // );
    // if (oldData) {
    //     const chart = this.$data._chart;
    //     // Get new and old DataSet Labels
    //     const newDatasetLabels = newData.scales;// .map(dataset => dataset.label);
    //     console.log('🚀 ~ file: chart.js ~ line 76 ~ dataHandlerOptions ~ newDatasetLabels', newDatasetLabels);
    //     const oldDatasetLabels = oldData.scales;// .map(dataset => dataset.label);
    //     console.log('🚀 ~ file: chart.js ~ line 78 ~ dataHandlerOptions ~ oldDatasetLabels', oldDatasetLabels);
    //     // Stringify 'em for easier compare
    //     const oldLabels = JSON.stringify(oldDatasetLabels);
    //     console.log('🚀 ~ file: chart.js ~ line 83 ~ dataHandlerOptions ~ oldLabels', oldLabels);
    //     const newLabels = JSON.stringify(newDatasetLabels);
    //     console.log('🚀 ~ file: chart.js ~ line 82 ~ dataHandlerOptions ~ newLabels', newLabels);
    //     // Check if Labels are equal and if dataset length is equal
    //     if (newLabels === oldLabels && oldData.datasets.length === newData.datasets.length) {
    //         console.log('🚀 ~ file: chart.js ~ line 89 ~ dataHandlerOptions ~ newLabels === oldLabels', newLabels === oldLabels);
    //         chart.update();
    //         // newData.datasets.forEach((dataset, i) => {
    //         //     // Get new and old dataset keys
    //         //     const oldDatasetKeys = Object.keys(oldData.datasets[i]);
    //         //     const newDatasetKeys = Object.keys(dataset);
    //         //     // Get keys that aren't present in the new data
    //         //     const deletionKeys = oldDatasetKeys.filter(key => key !== '_meta' && !newDatasetKeys.includes(key));
    //         //     // Remove outdated key-value pairs
    //         //     deletionKeys.forEach(deletionKey => {
    //         //         delete chart.data.datasets[i][deletionKey];
    //         //     });
    //         //     // Update attributes individually to avoid re-rendering the entire chart
    //         //     for (const attribute in dataset) {
    //         //         if (dataset.hasOwnProperty(attribute)) {
    //         //             chart.data.datasets[i][attribute] = dataset[attribute];
    //         //         }
    //         //     }
    //         // });
    //         // if (newData.hasOwnProperty('labels')) {
    //         //     chart.data.labels = newData.labels;
    //         //     this.$emit('labels:update');
    //         // }
    //         // if (newData.hasOwnProperty('xLabels')) {
    //         //     chart.data.xLabels = newData.xLabels;
    //         //     this.$emit('xlabels:update');
    //         // }
    //         // if (newData.hasOwnProperty('yLabels')) {
    //         //     chart.data.yLabels = newData.yLabels;
    //         //     this.$emit('ylabels:update');
    //         // }
    //         // chart.update();
    //         // this.$emit('chart:update');
    //     } else {
    //         if (chart) {
    //             chart.destroy();
    //             this.$emit('chart:destroy');
    //         }
    //         this.renderChart(this.chartData, this.options);
    //         this.$emit('chart:render');
    //     }
    // } else {
    //     if (this.$data._chart) {
    //         this.$data._chart.destroy();
    //         this.$emit('chart:destroy');
    //     }
    //     this.renderChart(this.chartData, this.options);
    //     this.$emit('chart:render');
    // }
}
export const reactiveData = {
    data() {
        return {
            chartData: null,
            chartOptions: null,
        };
    },

    watch: {
        chartData: dataHandler,
        chartOptions: dataHandlerOptions,
    },
};

export const reactiveProp = {
    props: {
        chartData: {
            type: Object,
            required: true,
            default: () => ({}),
        },
        chartOptions: {
            type: Object,
            required: true,
            default: () => ({}),
        },
    },
    watch: {
        chartData: dataHandler,
        chartOptions: dataHandlerOptions,
    },
};

export default {
    reactiveData,
    reactiveProp,
};
