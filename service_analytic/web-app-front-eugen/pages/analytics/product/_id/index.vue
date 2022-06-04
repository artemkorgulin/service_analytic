<template>
    <div class="tovar-analitik">
        <div class="tovar-analitik__row">
            <div class="tovar-analitik__colum tovar-analitik__head">
                <div class="tovar-analitik__prev" @click="$router.go(-1)">
                    <svg
                        width="10"
                        height="10"
                        viewBox="0 0 10 10"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            fill-rule="evenodd"
                            clip-rule="evenodd"
                            d="M4.82427 9.02347C4.71175 9.13595 4.55917 9.19914 4.40007 9.19914C4.24097 9.19914 4.08838 9.13595 3.97587 9.02347L0.375867 5.42347C0.263384 5.31095 0.200195 5.15837 0.200195 4.99927C0.200195 4.84017 0.263384 4.68759 0.375867 4.57507L3.97587 0.975068C4.08903 0.865773 4.24059 0.805296 4.39791 0.806663C4.55523 0.80803 4.70571 0.871132 4.81696 0.982377C4.9282 1.09362 4.9913 1.24411 4.99267 1.40143C4.99404 1.55875 4.93356 1.71031 4.82427 1.82347L2.24847 4.39927H9.20007C9.3592 4.39927 9.51181 4.46248 9.62433 4.575C9.73685 4.68753 9.80007 4.84014 9.80007 4.99927C9.80007 5.1584 9.73685 5.31101 9.62433 5.42353C9.51181 5.53605 9.3592 5.59927 9.20007 5.59927H2.24847L4.82427 8.17507C4.93675 8.28759 4.99994 8.44017 4.99994 8.59927C4.99994 8.75837 4.93675 8.91095 4.82427 9.02347Z"
                            fill="#7E8793"
                        />
                    </svg>
                    Назад
                </div>
                <h1 class="tovar-analitik__title">Статистика о товаре</h1>
            </div>
        </div>

        <div class="tovar-analitik__row">
            <div class="tovar-analitik__colum">
                <div class="tovar-analitik__box tovar-analitik__tovar">
                    <div class="tovar-analitik__tovar-info">
                        <div class="tovar-analitik__tovar-info-img">
                            <img src="/images/mascTovar.png" alt="" />
                        </div>
                        <div class="tovar-analitik__tovar-info-box">
                            <div class="tovar-analitik__tovar-info-title">
                                Маски - 100 шт., одноразовые медицинские, трехслойные, защитные для
                                лица
                            </div>
                            <div class="tovar-analitik__tovar-info-text">
                                Здоровье / Маски защитные / Маски одноразовые
                            </div>
                        </div>
                    </div>
                    <div class="tovar-analitik__tovar-period"></div>
                </div>
            </div>
        </div>

        <div class="tovar-analitik__row">
            <div class="tovar-analitik__colum tovar-analitik__card-stat-colum">
                <div class="tovar-analitik__box tovar-analitik__card-stat">
                    <img src="/images/summPic.png" alt="" />
                    <div class="tovar-analitik__card-stst-info">
                        <div class="tovar-analitik__card-stat-title">Суммарно за 60 дней</div>
                        <div class="tovar-analitik__card-stat-text">
                            946 шт
                            <span>></span>
                            193 306 ₽
                        </div>
                    </div>
                </div>
            </div>
            <div class="tovar-analitik__colum tovar-analitik__card-stat-colum">
                <div class="tovar-analitik__box tovar-analitik__card-stat">
                    <img src="/images/potencialPic.png" alt="" />
                    <div class="tovar-analitik__card-stst-info">
                        <div class="tovar-analitik__card-stat-title">
                            Потенциал / Упущенная выручка
                        </div>
                        <div class="tovar-analitik__card-stat-text">
                            252 139 ₽
                            <span>></span>
                            58 833 ₽ (23,33%)
                        </div>
                    </div>
                </div>
            </div>
            <div class="tovar-analitik__colum tovar-analitik__card-stat-colum">
                <div class="tovar-analitik__box tovar-analitik__card-stat">
                    <img src="/images/mediumPic.png" alt="" />
                    <div class="tovar-analitik__card-stst-info">
                        <div class="tovar-analitik__card-stat-title">Среднее (в день)</div>
                        <div class="tovar-analitik__card-stat-text">
                            32 шт
                            <span>></span>
                            6 443,53 ₽
                        </div>
                    </div>
                </div>
            </div>
            <div class="tovar-analitik__colum tovar-analitik__card-stat-colum">
                <div class="tovar-analitik__box tovar-analitik__card-stat">
                    <img src="/images/avialablePic.png" alt="" />
                    <div class="tovar-analitik__card-stst-info">
                        <div class="tovar-analitik__card-stat-title">
                            Среднее при наличии (в наличии: 23 дня)
                        </div>
                        <div class="tovar-analitik__card-stat-text">
                            39 шт
                            <span>></span>
                            8 079,13 ₽
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tovar-analitik__row">
            <div class="tovar-analitik__colum tovar-analitik__data-tovar-colum">
                <div class="tovar-analitik__box tovar-analitik__data-tovar">
                    <div class="tovar-analitik__data-item">
                        <div class="tovar-analitik__data-item-title">SKU</div>
                        <div class="tovar-analitik__data-item-value">14775308</div>
                    </div>
                    <div class="tovar-analitik__data-item">
                        <div class="tovar-analitik__data-item-title">Фото</div>
                        <div class="tovar-analitik__data-item-value"></div>
                    </div>
                    <div class="tovar-analitik__data-item">
                        <div class="tovar-analitik__data-item-title">Бренд</div>
                        <div class="tovar-analitik__data-item-value">
                            <span class="tovar-analitik__data-item-link">НЕ БОЛЕЙ</span>
                        </div>
                    </div>
                    <div class="tovar-analitik__data-item">
                        <div class="tovar-analitik__data-item-title">Цена</div>
                        <div class="tovar-analitik__data-item-value">187,00 р.</div>
                    </div>
                    <div class="tovar-analitik__data-item">
                        <div class="tovar-analitik__data-item-title">Цена до скидки</div>
                        <div class="tovar-analitik__data-item-value">891,00 р., скидка 79%</div>
                    </div>
                    <div class="tovar-analitik__data-item">
                        <div class="tovar-analitik__data-item-title">Поставщик</div>
                        <div class="tovar-analitik__data-item-value">
                            <span class="tovar-analitik__data-item-link">
                                ИП Кривушина Елена Сергеевна
                            </span>
                        </div>
                    </div>
                    <div class="tovar-analitik__data-item">
                        <div class="tovar-analitik__data-item-title">Обновлено</div>
                        <div class="tovar-analitik__data-item-value">01.10.2021</div>
                    </div>
                    <div class="tovar-analitik__data-item">
                        <div class="tovar-analitik__data-item-title">Впервые обнаружен</div>
                        <div class="tovar-analitik__data-item-value">16.10.2020</div>
                    </div>
                    <div class="tovar-analitik__data-item">
                        <div class="tovar-analitik__data-item-title">Рейтинг</div>
                        <div class="tovar-analitik__data-item-value">4,0</div>
                    </div>
                    <div class="tovar-analitik__data-item">
                        <div class="tovar-analitik__data-item-title">Комментарии</div>
                        <div class="tovar-analitik__data-item-value">807</div>
                    </div>
                    <div class="tovar-analitik__data-item">
                        <div class="tovar-analitik__data-item-title">Примечание</div>
                        <div class="tovar-analitik__data-item-value">
                            Имеется спорная точка зрения, гласящая примерно следующее: ключевые
                            особенности структуры проекта призывают...
                            <span class="tovar-analitik__data-item-link">Читать всё</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tovar-analitik__colum tovar-analitik__data-chart-colum">
                <div class="tovar-analitik__box-no-p tovar-analitik__data-chart">
                    <div class="tovar-analitik__box-in-p">
                        <div class="tovar-analitik__data-chart-title">Продажи и остатки</div>
                    </div>

                    <v-tabs v-model="tabChart" class="castom-tabs">
                        <v-tab>Сводка</v-tab>
                        <v-tab>Склады</v-tab>
                    </v-tabs>

                    <div class="tovar-analitik__box-in-p">
                        <div v-if="tabChart == 0" class="tovar-analitik__data-chart-box">
                            <!-- <ApexChart
                                width="320"
                                type="donut"
                                :options="chart1"
                                :series="series1"
                            ></ApexChart>
                            <ApexChart
                                width="320"
                                type="donut"
                                :options="chart2"
                                :series="series2"
                            ></ApexChart> -->

                            <div class="tovar-analitik__data-chart-legend">
                                <div
                                    v-for="(item, index) in colors"
                                    :key="index"
                                    class="tovar-analitik__data-chart-legend-item"
                                >
                                    <span :style="`background:${item.color}`"></span>
                                    <span>{{ item.name }}</span>
                                </div>
                            </div>
                        </div>
                        <div v-if="tabChart == 1" class="tovar-analitik__data-chart-box">
                            <!-- <ApexChart
                                width="320"
                                type="donut"
                                :options="chart3"
                                :series="series3"
                            ></ApexChart>
                            <ApexChart
                                width="320"
                                type="donut"
                                :options="chart4"
                                :series="series4"
                            ></ApexChart> -->

                            <div class="tovar-analitik__data-chart-legend">
                                <div
                                    v-for="(item, index) in colors"
                                    :key="index"
                                    class="tovar-analitik__data-chart-legend-item"
                                >
                                    <span :style="`background:${item.color}`"></span>
                                    <span>{{ item.name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tovar-analitik__row">
            <div class="tovar-analitik__colum">
                <div class="tovar-analitik__box">
                    <h2 class="tovar-analitik__title-h2">Продажи и остатки за 30 дней</h2>
                    <div class="tovar-analitik__sub-title">
                        На графике отображены условные продажи и остатки товара за те даты, когда
                        значение было получено
                    </div>

                    <!-- <ApexChart
                        height="416"
                        type="line"
                        :options="optionsLine"
                        :series="series5"
                    ></ApexChart> -->

                    <div class="tovar-analitik__more-info">
                        <div class="tovar-analitik__more-info-title">Ср. значение:</div>
                        <div class="tovar-analitik__more-info-value">281</div>

                        <div class="tovar-analitik__more-info-title">Количество:</div>
                        <div class="tovar-analitik__more-info-value">24</div>

                        <div class="tovar-analitik__more-info-title">Мин:</div>
                        <div class="tovar-analitik__more-info-value">0</div>

                        <div class="tovar-analitik__more-info-title">Макс:</div>
                        <div class="tovar-analitik__more-info-value">864</div>

                        <div class="tovar-analitik__more-info-title">Сумма:</div>
                        <div class="tovar-analitik__more-info-value">6 744</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tovar-analitik__row">
            <div class="tovar-analitik__colum">
                <div class="tovar-analitik__box-no-p">
                    <div class="tovar-analitik__box-in-p">
                        <h2 class="tovar-analitik__title-h2">Позиции ключевых слов</h2>
                    </div>

                    <v-tabs v-model="tabKeys" class="castom-tabs">
                        <v-tab>По запросам</v-tab>
                        <v-tab>По категориям</v-tab>
                    </v-tabs>

                    <div v-if="tabKeys == 0" class="tovar-analitik__box-in-p">
                        <div class="tovar-analitik__descript">
                            <span>
                                Запросы ключевых слов по которым представлен товар в поиске и
                                занимаемая им позиция (первые 200 с сортировкой по позиции последней
                                даты, частотности)
                            </span>
                            <NuxtLink to="/analytics/tovar/13425/keys">
                                История ключевых слов
                            </NuxtLink>
                        </div>
                    </div>

                    <div v-if="tabKeys == 1" class="tovar-analitik__box-in-p">
                        <div class="tovar-analitik__descript">
                            <span>
                                Категории в которых представлен товар и занимаемая им позиция
                            </span>
                            <NuxtLink to="/analytics/tovar/13425/positions">
                                История позиций
                            </NuxtLink>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        // components: {
        //     ApexChart: () => import('vue-apexcharts'),
        // },
        data: () => ({
            tabChart: 0,
            tabKeys: 0,

            colors: [
                { color: '#F3E560', name: 'Подольск' },
                { color: '#F6EA80', name: 'FBS 119261' },
                { color: '#F9F3B3', name: 'Остальные' },
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

            optionsLine: {
                colors: ['#FC6E90', '#607FEA', '#5DDC98', '#A46FE8'],
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
                    dashArray: 0,
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

            series1: [80, 46, 41],
            series2: [70, 55, 68],
            series3: [80, 46, 41],
            series4: [70, 55, 68],

            series5: [
                {
                    name: 'Продажи',
                    data: [45, 52, 38, 24, 33, 26, 21, 20, 6, 8, 15, 10],
                },
                {
                    name: 'Остаток',
                    data: [35, 41, 62, 42, 13, 18, 29, 37, 36, 51, 32, 35],
                },
                {
                    name: 'Цена',
                    data: [12, 13, 14, 15, 14, 13, 12, 10, 16, 17, 18, 19],
                },
                {
                    name: 'Выручка',
                    data: [50, 50, 50, 56, 56, 56, 57, 57, 57, 59, 59, 59],
                },
            ],
        }),
        computed: {
            chart1() {
                const options = JSON.parse(JSON.stringify(this.options));
                options.colors = this.colors.map(item => item.color);
                options.plotOptions.pie.donut.labels.total.label = 'Остатки';
                return options;
            },
            chart2() {
                const options = JSON.parse(JSON.stringify(this.options));
                options.colors = this.colors.map(item => item.color);
                options.plotOptions.pie.donut.labels.total.label = 'Продажи';
                return options;
            },
            chart3() {
                const options = JSON.parse(JSON.stringify(this.options));
                options.colors = this.colors.map(item => item.color);
                options.plotOptions.pie.donut.labels.total.label = 'Остатки';
                return options;
            },
            chart4() {
                const options = JSON.parse(JSON.stringify(this.options));
                options.colors = this.colors.map(item => item.color);
                options.plotOptions.pie.donut.labels.total.label = 'Продажи';
                return options;
            },
        },
    };
</script>
<style lang="scss" scoped>
    .tovar-analitik {
        padding: 8px;

        .tovar-analitik__title-h2 {
            font-size: 20px;
            font-style: normal;
            font-weight: 500;
            line-height: 27px;
            color: #2f3640;
        }

        .tovar-analitik__sub-title {
            font-size: 12px;
            font-style: normal;
            font-weight: normal;
            line-height: 16px;
            color: #7e8793;
        }

        // Сетка
        .tovar-analitik__row {
            display: flex;
            align-items: stretch;

            .tovar-analitik__colum {
                flex-grow: 1;
                flex-shrink: 0.5;
                padding: 8px;
            }
        }

        .tovar-analitik__box {
            padding: 16px;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 4px 32px rgba(0, 0, 0, 0.06);
        }

        .tovar-analitik__box-no-p {
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 4px 32px rgba(0, 0, 0, 0.06);
        }

        .tovar-analitik__box-in-p {
            padding: 16px;
        }

        //
        .tovar-analitik__head {
            display: flex;

            .tovar-analitik__prev {
                padding: 6px 16px 6px 12px;
                border-radius: 200px;
                border: 1px solid #c8cfd9;
                font-size: 14px;
                font-style: normal;
                font-weight: 500;
                line-height: 19px;
                color: #7e8793;
                cursor: pointer;

                & svg {
                    margin-right: 8px;
                }
            }

            .tovar-analitik__title {
                padding: 0 12px;
                font-size: 24px;
                font-style: normal;
                font-weight: 500;
                line-height: 33px;
                color: #2f3640;
            }
        }

        // Информация о товаре
        .tovar-analitik__tovar {
            display: flex;

            .tovar-analitik__tovar-info {
                display: flex;
                flex-grow: 1;

                .tovar-analitik__tovar-info-img {
                    width: 40px;
                    height: 40px;

                    & img {
                        height: 100%;
                    }
                }

                .tovar-analitik__tovar-info-box {
                    margin-left: 16px;

                    .tovar-analitik__tovar-info-title {
                        font-size: 16px;
                        font-style: normal;
                        font-weight: normal;
                        line-height: 24px;
                        color: #2f3640;
                    }

                    .tovar-analitik__tovar-info-text {
                        font-size: 12px;
                        font-style: normal;
                        font-weight: normal;
                        line-height: 16px;
                        color: #7e8793;
                    }
                }
            }

            .tovar-analitik__tovar-period {
                display: flex;
            }
        }

        // Карточки статистики
        .tovar-analitik__card-stat-colum {
            width: 25%;

            .tovar-analitik__card-stat {
                display: flex;

                .tovar-analitik__card-stst-info {
                    margin-left: 16px;

                    .tovar-analitik__card-stat-title {
                        font-size: 12px;
                        font-style: normal;
                        font-weight: normal;
                        line-height: 16px;
                        color: #7e8793;
                    }

                    .tovar-analitik__card-stat-text {
                        font-size: 16px;
                        font-style: normal;
                        font-weight: normal;
                        line-height: 24px;
                        color: #2f3640;
                    }
                }
            }
        }

        .tovar-analitik__data-tovar-colum {
            width: 30%;

            .tovar-analitik__data-tovar {
                .tovar-analitik__data-item {
                    display: flex;
                    padding: 8px 0;

                    .tovar-analitik__data-item-title {
                        width: 35%;
                        font-size: 14px;
                        font-style: normal;
                        font-weight: normal;
                        line-height: 19px;
                        color: #7e8793;
                    }

                    .tovar-analitik__data-item-value {
                        width: 65%;
                        font-size: 16px;
                        font-style: normal;
                        font-weight: normal;
                        line-height: 24px;
                        color: #2f3640;
                    }
                }
            }
        }

        .tovar-analitik__data-chart-colum {
            width: 70%;

            .tovar-analitik__data-chart {
                height: 100%;

                .tovar-analitik__data-chart-title {
                    font-size: 20px;
                    font-style: normal;
                    font-weight: 500;
                    line-height: 27px;
                    color: #2f3640;
                }

                .tovar-analitik__data-chart-box {
                    display: flex;
                    justify-content: space-around;
                    padding: 50px 0;

                    .tovar-analitik__data-chart-legend {
                        display: flex;
                        justify-content: center;
                        flex-flow: column nowrap;
                        padding: 0 40px;

                        .tovar-analitik__data-chart-legend-item {
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
            }
        }

        .tovar-analitik__more-info {
            display: flex;
            justify-content: flex-end;
            padding-top: 16px;

            .tovar-analitik__more-info-title {
                font-size: 12px;
                font-style: normal;
                font-weight: normal;
                line-height: 16px;
                color: #7e8793;
            }

            .tovar-analitik__more-info-value {
                padding: 0 10px 0 4px;
                font-size: 12px;
                font-style: normal;
                font-weight: bold;
                line-height: 16px;
                color: #2f3640;
            }
        }

        .tovar-analitik__descript {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            font-style: normal;
            font-weight: normal;
            line-height: 16px;
            color: #7e8793;

            & a {
                font-size: 14px;
                font-style: normal;
                font-weight: bold;
                line-height: 19px;
                color: #710bff;
            }
        }
    }
</style>

<style lang="scss">
    .tovar-analitik {
        .apexcharts-legend-series {
            padding: 0 10px;
        }
    }
</style>
