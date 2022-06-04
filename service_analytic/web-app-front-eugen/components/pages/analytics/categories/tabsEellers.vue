<template>
    <div class="tabs-eellers">
        <div class="tabs-eellers__title">ТОП-200 продавцов в категории (за 30 дней)</div>

        <div class="tabs-cat__charts">
            <!-- <ApexChart width="320" type="donut" :options="chartSales" :series="series2"></ApexChart>
            <ApexChart width="320" type="donut" :options="chartReven" :series="series3"></ApexChart> -->

            <div class="tabs-cat__charts-legend">
                <div
                    v-for="(item, index) in colors"
                    :key="index"
                    class="tabs-cat__charts-legend-item"
                >
                    <span :style="`background:${item.color}`"></span>
                    <span>{{ item.name }}</span>
                </div>
            </div>
        </div>

        <div class="tabs-eellers__more-info">
            <div class="tabs-eellers__more-info-title">Ср. значение:</div>
            <div class="tabs-eellers__more-info-value">281</div>

            <div class="tabs-eellers__more-info-title">Количество:</div>
            <div class="tabs-eellers__more-info-value">24</div>

            <div class="tabs-eellers__more-info-title">Мин:</div>
            <div class="tabs-eellers__more-info-value">0</div>

            <div class="tabs-eellers__more-info-title">Макс:</div>
            <div class="tabs-eellers__more-info-value">864</div>

            <div class="tabs-eellers__more-info-title">Сумма:</div>
            <div class="tabs-eellers__more-info-value">6 744</div>
        </div>
    </div>
</template>
<script>
    export default {
        // components: {
        //     ApexChart: () => import('vue-apexcharts'),
        // },

        data: () => ({
            colors: [
                { color: '#FC6E90', name: 'ИП Прокофьева Екатерина Сергеевна' },
                { color: '#5DDC98', name: 'ИП Буров Юрий Александрович' },
                { color: '#607FEA', name: 'ИП Баженова Елена Николаевна' },
                { color: '#F4E450', name: 'ИП Нефедова Любовь Игоревна' },
                { color: '#3DC1CA', name: 'ИП Ероклинцев Владимир Владимирович' },
                { color: '#F4A950', name: 'ИП Чухманова Ирина Владимировна' },
                { color: '#A46FE8', name: 'Остальные' },
            ],

            options: {
                plotOptions: {
                    pie: {
                        donut: {
                            size: '50%',
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                    offsetY: 5,
                                },
                                value: {
                                    show: false,
                                },
                                total: {
                                    show: true,
                                    showAlways: true,
                                    label: 'Total1',
                                    fontSize: '16px',
                                    fontWeight: 400,
                                    color: '#2F3640',
                                },
                            },
                        },
                    },
                },
                tooltip: {
                    enabled: false,
                    shared: false,
                },
                dataLabels: {
                    enabled: false,
                },
                legend: {
                    show: false,
                },
            },

            series2: [80, 46, 41, 19, 68, 5],
            series3: [70, 55, 68, 5, 41, 19],
        }),

        computed: {
            chartSales() {
                const options = JSON.parse(JSON.stringify(this.options));
                options.colors = this.colors.map(item => item.color);
                options.plotOptions.pie.donut.labels.total.label = 'Продажи';
                return options;
            },
            chartReven() {
                const options = JSON.parse(JSON.stringify(this.options));
                options.colors = this.colors.map(item => item.color);
                options.plotOptions.pie.donut.labels.total.label = 'Выручка';
                return options;
            },
        },
    };
</script>
<style lang="scss" scoped>
    .tabs-eellers {
        padding: 16px;

        .tabs-eellers__title {
            font-size: 20px;
            font-style: normal;
            font-weight: 500;
            line-height: 27px;
            color: #2f3640;
        }

        .tabs-cat__charts {
            display: flex;
            justify-content: space-around;
            padding: 50px 0;

            .tabs-cat__charts-legend {
                display: flex;
                justify-content: center;
                flex-flow: column nowrap;
                padding: 0 40px;

                .tabs-cat__charts-legend-item {
                    & span:first-child {
                        display: inline-block;
                        width: 40px;
                        height: 8px;
                        margin-right: 8px;
                        border-radius: 2px;
                    }

                    & span:last-child {
                        font-size: 12px;
                        font-style: normal;
                        font-weight: normal;
                        line-height: 16px;
                        color: #2f3640;
                    }
                }
            }
        }

        .tabs-eellers__more-info {
            display: flex;
            justify-content: flex-end;
            padding-top: 16px;

            .tabs-eellers__more-info-title {
                font-size: 12px;
                font-style: normal;
                font-weight: normal;
                line-height: 16px;
                color: #7e8793;
            }

            .tabs-eellers__more-info-value {
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
