<template>
    <VCard class="chart-card pa-4" flat>
        <div class="d-flex align-center justify-space-between">
            <h3 class="heading mr-3">{{ title }}</h3>
            <div class="status">
                <div class="status__text" :style="statusByLastValue.textStyles">{{ statusByLastValue.text }}</div>
                <div class="status__icon" :style="statusByLastValue.iconStyles">
                    <SvgIcon :name="statusByLastValue.icon" />
                </div>
            </div>
        </div>
        <BaseChart
            :chart-height="160"
            class="chart"
            :chart-data="chartData"
            chart-type="bar"
            :chart-options="chartOptions"
        />
        <div class="d-flex justify-end">
            <VBtn outlined :class="{ 'pulse-button': seekAttention }" @click="handleButtonClick">Оптимизировать</VBtn>
        </div>

    </VCard>
</template>

<script>
    import { ru } from 'date-fns/locale';

    export default {
        name: 'ProductChartByDaysBar',
        props: {
            title: {
                type: String,
                required: true,
            },
            tabToOpen: {
                type: Number,
                default: null,
            },
            data: {
                type: Array,
                default: null,
            },
            labels: {
                type: Array,
                default: null,
            },
            chartColor: {
                type: String,
                default: '#A46FE8',
            }
        },
        data() {
            return {
                colors: {
                    fine: ['#20C274', 'rgba(92, 231, 156, 0.08)'],
                    warning: ['#ECAD32', 'rgba(255, 193, 100, 0.08)'],
                    bad: ['rgba(252, 110, 144, 1)', 'rgba(252, 110, 144, 0.16)'],
                },
            };
        },
        computed: {
            chartData() {
                return {
                    datasets: [{
                        backgroundColor: this.chartColor,
                        borderColor: this.chartColor,
                        borderRadius: 2,
                        data: this.checkThirtyDays(this.data),
                    }],
                    labels: this.checkThirtyDays(this.labels, true),
                };
            },
            chartOptions() {
                return {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        x: {
                            display: true,
                            type: 'time',
                            adapters: {
                                date: {
                                    locale: ru,
                                },
                            },
                            time: {
                                unit: 'day',
                                tooltipFormat: 'd MMMM',
                            },
                            title: {
                                displayFormat: { day: 'd MMMM' },
                                font: {
                                    // family: 'Comic Sans MS',
                                    size: 10,
                                    // weight: 'bold',
                                    lineHeight: 1,
                                },
                                padding: {top: 20, left: 0, right: 0, bottom: 0},
                            },
                            ticks: {
                                display: true,
                                autoSkip: true,
                                font: {
                                    size: 10,
                                    lineHeight: 1,
                                },
                            },
                        },
                        y: {
                            display: false,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString().replace(/,/g, ' ');
                                },
                            },
                        },
                    },
                    plugins: {
                        legend: {
                            display: false,
                        },
                    },
                };
            },
            lastValue() {
                return this.chartData.datasets?.[0]?.data?.[0] || 0;
            },
            seekAttention() {
                return this.lastValue < 40;
            },
            statusByLastValue() {
                if (this.lastValue >= 40) {
                    return {
                        textStyles: {
                            background: this.colors.fine[1],
                            color: this.colors.fine[0],
                        },
                        iconStyles: {
                            background: this.colors.fine[0],
                        },
                        text: 'Всё отлично!',
                        icon: 'filled/thumbsUp',
                    };
                } else if (this.lastValue > 0) {
                    return {
                        textStyles: {
                            background: this.colors.warning[1],
                            color: this.colors.warning[0],
                        },
                        iconStyles: {
                            background: this.colors.warning[0],
                        },
                        text: 'Обратите внимание!',
                        icon: 'filled/palm',
                    };
                } else {
                    return {
                        textStyles: {
                            background: this.colors.bad[1],
                            color: this.colors.bad[0],
                        },
                        iconStyles: {
                            background: this.colors.bad[0],
                        },
                        text: 'Нужно исправить',
                        icon: 'filled/thumbsDown',
                    };
                }
            },
        },
        methods: {
            handleButtonClick() {
                if (this.tabToOpen !== null) {
                    this.$emit('proceed', this.tabToOpen);
                }
            },
            checkThirtyDays(dataset, isDate = false) {
                const arr = dataset || [];
                const diff = 30 - arr.length;
                if (diff > 0) {
                    for (let i = diff; i > 0; i--) {
                        if (isDate) {
                            const newVal = this.composeDate(arr[arr.length - 1]);
                            arr.push(newVal);
                        } else {
                            arr.push(0);
                        }
                    }
                } else if (diff < 0) {
                    arr.splice(arr.length - 1 - Math.abs(diff), Math.abs(diff));
                }
                return arr;
            },
            composeDate(prevValue) {
                    const newDate = prevValue ? new Date(prevValue) : new Date();
                    newDate.setDate(newDate.getDate() - 1);
                    return newDate.toISOString();
            }
        }
    };
</script>

<style scoped lang="scss">
    .chart-card {
        @include flex-grid-y;

        box-shadow: 0 8px 24px rgb(0 0 0 / 4%) !important;
        border-radius: 16px;
    }

    .heading {
        @extend %text-h5;

        font-size: 20px;
    }

    .status {
        overflow: hidden;
        display: flex;
        height: 32px;
        border-radius: 6px;

        &__text {
            @include flex-center;

            padding: 0 12px;
            font-weight: bold;
            font-size: 12px;
            line-height: 16px;
        }

        &__icon {
            @include flex-center;

            width: 32px;
            color: $white;
        }
    }

    .pulse-button {
        box-shadow: 0 0 0 rgba(204,169,44, 0.4);
        border: 1px solid #FF3981;
        background: rgba(252, 110, 144, 0.24);
        color: #FF3981;
        animation: pulse 2s infinite;

        &:hover {
            background: #FF3981;
            color: $white;
            animation: none;
        }
    }

    @keyframes pulse {
        0% {
            -moz-box-shadow: 0 0 0 0 rgba(252, 110, 144, 0.4);
            box-shadow: 0 0 0 0 rgba(252, 110, 144, 0.4);
        }

        70% {
            -moz-box-shadow: 0 0 0 16px rgba(252, 110, 144, 0);
            box-shadow: 0 0 0 16px rgba(252, 110, 144, 0);
        }

        100% {
            -moz-box-shadow: 0 0 0 0 rgba(252, 110, 144, 0);
            box-shadow: 0 0 0 0 rgba(252, 110, 144, 0);
        }
    }
</style>
