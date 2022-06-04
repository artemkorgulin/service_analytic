<template>
    <div class="tovar-position">
        <div class="tovar-position__row">
            <div class="tovar-position__colum tovar-position__head">
                <div class="tovar-position__prev" @click="$router.go(-1)">
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
                <h1 class="tovar-position__title">История заказов</h1>
            </div>
        </div>

        <div class="tovar-position__row">
            <div class="tovar-position__colum">
                <div class="tovar-position__box tovar-position__tovar">
                    <div class="tovar-position__tovar-info">
                        <div class="tovar-position__tovar-info-img">
                            <img src="/images/mascTovar.png" alt="" />
                        </div>
                        <div class="tovar-position__tovar-info-box">
                            <div class="tovar-position__tovar-info-title">
                                Маски - 100 шт., одноразовые медицинские, трехслойные, защитные для
                                лица
                            </div>
                            <div class="tovar-position__tovar-info-text">
                                Здоровье / Маски защитные / Маски одноразовые
                            </div>
                        </div>
                    </div>
                    <div class="tovar-position__tovar-period"></div>
                </div>
            </div>
        </div>

        <div class="tovar-position__row">
            <div class="tovar-position__colum">
                <div class="tovar-position__box tovar-position__item-info-tovar-box">
                    <div class="tovar-position__item-info-tovar">
                        <div class="tovar-position__data-item">
                            <div class="tovar-position__data-item-title">SKU</div>
                            <div class="tovar-position__data-item-value">14775308</div>
                        </div>
                        <div class="tovar-position__data-item">
                            <div class="tovar-position__data-item-title">Фото</div>
                            <div class="tovar-position__data-item-value"></div>
                        </div>
                        <div class="tovar-position__data-item">
                            <div class="tovar-position__data-item-title">Бренд</div>
                            <div class="tovar-position__data-item-value">
                                <span class="tovar-position__data-item-link">НЕ БОЛЕЙ</span>
                            </div>
                        </div>
                        <div class="tovar-position__data-item">
                            <div class="tovar-position__data-item-title">Цена</div>
                            <div class="tovar-position__data-item-value">187,00 р.</div>
                        </div>
                        <div class="tovar-position__data-item">
                            <div class="tovar-position__data-item-title">Цена до скидки</div>
                            <div class="tovar-position__data-item-value">891,00 р., скидка 79%</div>
                        </div>
                        <div class="tovar-position__data-item">
                            <div class="tovar-position__data-item-title">Поставщик</div>
                            <div class="tovar-position__data-item-value">
                                <span class="tovar-position__data-item-link">
                                    ИП Кривушина Елена Сергеевна
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="tovar-position__item-info-tovar">
                        <div class="tovar-position__data-item">
                            <div class="tovar-position__data-item-title">Обновлено</div>
                            <div class="tovar-position__data-item-value">01.10.2021</div>
                        </div>
                        <div class="tovar-position__data-item">
                            <div class="tovar-position__data-item-title">Впервые обнаружен</div>
                            <div class="tovar-position__data-item-value">16.10.2020</div>
                        </div>
                        <div class="tovar-position__data-item">
                            <div class="tovar-position__data-item-title">Рейтинг</div>
                            <div class="tovar-position__data-item-value">4,0</div>
                        </div>
                        <div class="tovar-position__data-item">
                            <div class="tovar-position__data-item-title">Комментарии</div>
                            <div class="tovar-position__data-item-value">807</div>
                        </div>
                    </div>
                    <div class="tovar-position__item-info-tovar">
                        <div class="tovar-position__data-item">
                            <div class="tovar-position__data-item-title">Примечание</div>
                            <div class="tovar-position__data-item-value">
                                Имеется спорная точка зрения, гласящая примерно следующее: ключевые
                                особенности структуры проекта призывают...
                                <span class="tovar-position__data-item-link">Читать всё</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tovar-position__row">
            <div class="tovar-position__colum">
                <div class="tovar-position__box">
                    <div class="tovar-position__data-chart-title">Заказы и остатки</div>
                    <v-tabs v-model="tab" class="castom-tabs">
                        <v-tab>По складам</v-tab>
                    </v-tabs>

                    <!-- <ApexChart
                        height="416"
                        type="line"
                        :options="optionsLine"
                        :series="series5"
                    ></ApexChart> -->
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
            tab: 0,

            optionsLine: {
                colors: ['#FC6E90', '#607FEA', '#5DDC98', '#A46FE8', '#3DC1CA'],
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

                legend: {
                    position: 'right',
                },
            },

            series5: [
                {
                    name: 'Всего заказов',
                    data: [45, 52, 38, 24, 33, 26, 21, 20, 6, 8, 15, 10],
                },
                {
                    name: 'Подольск. Заказы',
                    data: [35, 41, 62, 42, 13, 18, 29, 37, 36, 51, 32, 35],
                },
                {
                    name: 'Подольск. Остатки',
                    data: [12, 13, 14, 15, 14, 13, 12, 10, 16, 17, 18, 19],
                },
                {
                    name: 'Склад. Заказы',
                    data: [50, 50, 50, 56, 56, 56, 57, 57, 57, 59, 59, 59],
                },
                {
                    name: 'Склад. Остатки',
                    data: [60, 80, 60, 90, 60, 90, 80, 70, 90, 80, 50, 40],
                },
            ],
        }),
    };
</script>
<style lang="scss" scoped>
    .tovar-position {
        padding: 8px;

        .tovar-position__title-h2 {
            font-size: 20px;
            font-style: normal;
            font-weight: 500;
            line-height: 27px;
            color: #2f3640;
        }

        .tovar-position__sub-title {
            font-size: 12px;
            font-style: normal;
            font-weight: normal;
            line-height: 16px;
            color: #7e8793;
        }

        // Сетка
        .tovar-position__row {
            display: flex;
            align-items: stretch;

            .tovar-position__colum {
                flex-grow: 1;
                flex-shrink: 0.5;
                padding: 8px;
            }
        }

        .tovar-position__box {
            padding: 16px;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 4px 32px rgba(0, 0, 0, 0.06);
        }

        .tovar-position__box-no-p {
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 4px 32px rgba(0, 0, 0, 0.06);
        }

        .tovar-position__box-in-p {
            padding: 16px;
        }

        //
        .tovar-position__head {
            display: flex;

            .tovar-position__prev {
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

            .tovar-position__title {
                padding: 0 12px;
                font-size: 24px;
                font-style: normal;
                font-weight: 500;
                line-height: 33px;
                color: #2f3640;
            }
        }

        // Информация о товаре
        .tovar-position__tovar {
            display: flex;

            .tovar-position__tovar-info {
                display: flex;
                flex-grow: 1;

                .tovar-position__tovar-info-img {
                    width: 40px;
                    height: 40px;

                    & img {
                        height: 100%;
                    }
                }

                .tovar-position__tovar-info-box {
                    margin-left: 16px;

                    .tovar-position__tovar-info-title {
                        font-size: 16px;
                        font-style: normal;
                        font-weight: normal;
                        line-height: 24px;
                        color: #2f3640;
                    }

                    .tovar-position__tovar-info-text {
                        font-size: 12px;
                        font-style: normal;
                        font-weight: normal;
                        line-height: 16px;
                        color: #7e8793;
                    }
                }
            }

            .tovar-position__tovar-period {
                display: flex;
            }
        }

        .tovar-position__item-info-tovar-box {
            display: flex;

            .tovar-position__item-info-tovar {
                width: calc(100% / 3);

                .tovar-position__data-item {
                    display: flex;
                    padding: 8px 0;

                    .tovar-position__data-item-title {
                        width: 35%;
                        font-size: 14px;
                        font-style: normal;
                        font-weight: normal;
                        line-height: 19px;
                        color: #7e8793;
                    }

                    .tovar-position__data-item-value {
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

        .tovar-position__data-chart-title {
            padding-bottom: 16px;
            font-size: 20px;
            font-style: normal;
            font-weight: 500;
            line-height: 27px;
            color: #2f3640;
        }
    }
</style>
<style lang="scss">
    .tovar-position {
        .apexcharts-legend-series {
            padding: 0 10px;
        }
    }
</style>
