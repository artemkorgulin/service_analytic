<template>
    <div class="tabs-trends">
        <!-- <ApexChart height="416" type="line" :options="optionsLine" :series="series"></ApexChart> -->
        <div class="tabs-trends__more-info">
            <div class="tabs-trends__more-info-title">Ср. значение:</div>
            <div class="tabs-trends__more-info-value">281</div>

            <div class="tabs-trends__more-info-title">Количество:</div>
            <div class="tabs-trends__more-info-value">24</div>

            <div class="tabs-trends__more-info-title">Мин:</div>
            <div class="tabs-trends__more-info-value">0</div>

            <div class="tabs-trends__more-info-title">Макс:</div>
            <div class="tabs-trends__more-info-value">864</div>

            <div class="tabs-trends__more-info-title">Сумма:</div>
            <div class="tabs-trends__more-info-value">6 744</div>
        </div>
    </div>
</template>
<script>
    export default {
        // components: {
        //     ApexChart: () => import('vue-apexcharts'),
        // },
        data: () => ({
            optionsLine: {
                colors: [
                    '#FC6E90',
                    '#607FEA',
                    '#F4E450',
                    '#5DDC98',
                    '#A46FE8',
                    '#3DC1CA',
                    '#F1E040',
                    '#4CCB86',
                    '#A46FE8',
                ],
                chart: {
                    toolbar: {
                        show: false,
                    },
                },
                dataLabels: {
                    enabled: false,
                },
                stroke: {
                    width: 2,
                    curve: 'straight',
                    dashArray: [0, 0, 0, 0, 0, 0, 5, 5, 5],
                },
                title: {
                    text: undefined,
                    align: 'left',
                },
                markers: {
                    size: 0,
                    hover: {
                        sizeOffset: 6,
                    },
                },
                xaxis: {
                    categories: [
                        '01.09',
                        '02.09',
                        '03.09',
                        '04.09',
                        '05.09',
                        '06.09',
                        '07.09',
                        '08.09',
                        '09.09',
                        '10.09',
                        '11.09',
                        '12.09',
                    ],
                    labels: {
                        rotate: -45,
                        rotateAlways: true,
                    },
                },
                grid: {
                    show: true,
                    borderColor: '#f1f1f1',
                    xaxis: {
                        lines: {
                            show: true,
                        },
                    },
                    yaxis: {
                        lines: {
                            show: true,
                        },
                    },
                },
            },

            series: [
                {
                    name: 'Продажи',
                    data: [45, 52, 38, 24, 33, 26, 21, 20, 6, 8, 15, 10],
                },
                {
                    name: 'Выручка',
                    data: [35, 41, 62, 42, 13, 18, 29, 37, 36, 51, 32, 35],
                },
                {
                    name: 'Продавцов',
                    data: [12, 13, 14, 15, 14, 13, 12, 10, 16, 17, 18, 19],
                },
                {
                    name: 'Товаров',
                    data: [50, 50, 50, 56, 56, 56, 57, 57, 57, 59, 59, 59],
                },
                {
                    name: 'Брендов',
                    data: [12, 15, 14, 16, 17, 14, 16, 17, 14, 18, 19, 20],
                },
                {
                    name: 'Выручка на товар',
                    data: [87, 57, 74, 99, 75, 38, 62, 47, 82, 56, 45, 47],
                },
                {
                    name: 'Продавцов с продажами',
                    data: [40, 50, 33, 20, 30, 22, 18, 15, 8, 9, 21, 14],
                },
                {
                    name: 'Товаров с продажами',
                    data: [40, 46, 68, 47, 19, 21, 34, 45, 40, 58, 38, 40],
                },
                {
                    name: 'Брендов с продажами',
                    data: [100, 80, 70, 110, 80, 40, 60, 50, 100, 60, 50, 50],
                },
            ],
        }),
    };
</script>
<style lang="scss" scoped>
    .tabs-trends {
        padding: 16px;

        .tabs-trends__more-info {
            display: flex;
            justify-content: flex-end;
            padding-top: 16px;

            .tabs-trends__more-info-title {
                font-size: 12px;
                font-style: normal;
                font-weight: normal;
                line-height: 16px;
                color: #7e8793;
            }

            .tabs-trends__more-info-value {
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
<style lang="scss">
    .tabs-trends {
        .apexcharts-legend-series {
            padding: 0 10px;
        }
    }
</style>
