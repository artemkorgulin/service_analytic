<template>
    <VCard class="chart-card pa-4">
        <div v-if="showCover" class="coming-soon">
            <div class="coming-soon__text">СКОРО</div>
        </div>
        <div class="d-flex align-center justify-space-between">
            <h3 class="heading mr-3">Защита авторства</h3>
            <div class="status">
                <div
                    class="status__text"
                    :style="{ background: colorsByPercentage[1], color: colorsByPercentage[0] }"
                >
                    {{ statusByPercentage.text }}
                </div>
                <div class="status__icon" :style="{ background: colorsByPercentage[0] }">
                    <SvgIcon :name="statusByPercentage.icon" />
                </div>
            </div>
        </div>
        <div class="chart-card__main">
            <BaseChart
                id="chart"
                :chart-height="160"
                class="chart-card__chart"
                :chart-data="chartData"
                chart-type="doughnut"
                :chart-options="chartOptions"
                :chart-plugins="chartInnerPercentagePlugin"
            />
            <div class="chart-card-info">
                <div class="chart-card-info__text-big">
                    {{ escrow.remainLimit }} из {{ escrow.totalLimit }}
                </div>
                <div class="chart-card-info__text-small">осталось депонирований в этом месяце</div>
                <div v-if="certificates[0]" class="certificate">
                    <VBtn
                        class="certificate__icon"
                        depressed
                        :href="certificates[0].link"
                        target="_blank"
                    >
                        <SvgIcon name="outlined/certificate" />
                    </VBtn>
                    <div class="certificate__text">Сертификат от {{ certificateDate }}</div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-end">
            <VBtn outlined :class="{ 'pulse-button': isBtnPulsing }" @click="handleButtonClick">
                Депонировать
            </VBtn>
        </div>
    </VCard>
</template>

<script>
    import { formatDateTime } from '~utils/date-time.utils';
    import { mapActions, mapGetters, mapState } from 'vuex';
    import chartEscrowMixin from '~mixins/chartEscrow.mixin';

    export default {
        name: 'ProductChartEscrow',
        mixins: [chartEscrowMixin],
        props: {
            info: {
                type: Object,
                default: null,
            },
            tabToOpen: {
                type: Number,
                default: null,
            },
            nomenclaturesTabSelected: {
                type: Number,
                default: 0,
            },
            data: {
                type: Array,
                default: null,
            },
            currentTab: {
                type: Number,
                default: 0,
            },
        },
        data() {
            return {
                colorBase: 'rgba(252, 110, 144, 1)',
                colorLight: 'rgba(252, 110, 144, 0.16)',
                colors: {
                    fine: ['#20C274', 'rgba(92, 231, 156, 0.08)'],
                    warning: ['#ECAD32', 'rgba(255, 193, 100, 0.08)'],
                    bad: ['rgba(252, 110, 144, 1)', 'rgba(252, 110, 144, 0.16)'],
                },
            };
        },
        computed: {
            ...mapState(['auth']),
            ...mapState('product', ['hashes', 'certificates', 'escrow']),
            ...mapGetters({
                marketplaceSlug: 'getSelectedMarketplaceSlug',
                getImages: 'product/getImages',
                getWbImages: 'product/getWbImages',
            }),
            ...mapGetters(['isSelectedMp']),
            chartData() {
                return {
                    datasets: [
                        {
                            backgroundColor: [
                                this.colorsByPercentage[0],
                                this.colorsByPercentage[1],
                            ],
                            borderColor: [this.colorsByPercentage[0], this.colorsByPercentage[1]],
                            data: [this.percentage, 100 - this.percentage],
                        },
                    ],
                };
            },
            options() {
                return this.info.options.map(item => ({
                    name: item.name,
                    nmId: item.nmId,
                    image: item.images[0],
                }));
            },
            certificateDate() {
                const date = new Date(this.certificates[this.nomenclaturesTabSelected]?.created_at);
                return formatDateTime(date, '$d.$m.$y') || null;
            },
            percentage() {
                /* eslint-disable */
                if (!this.hashes) {
                    return 0;
                }
                try {
                    return this.hashes.length
                        ? (this.info.imagesNum / this.hashes.length) * 100
                        : 0;
                } catch {
                    return 0;
                }
            },
            colorsByPercentage() {
                if (this.percentage >= 80) {
                    return this.colors.fine;
                } else if (this.percentage >= 60) {
                    return this.colors.warning;
                } else {
                    return this.colors.bad;
                }
            },
            statusByPercentage() {
                if (this.percentage >= 80) {
                    return {
                        text: 'Всё отлично!',
                        icon: 'filled/thumbsUp',
                    };
                } else if (this.percentage >= 60) {
                    return {
                        text: 'Обратите внимание!',
                        icon: 'filled/palm',
                    };
                } else {
                    return {
                        text: 'Нужно исправить',
                        icon: 'filled/thumbsDown',
                    };
                }
            },
            isBtnPulsing() {
                return (!this.showCover && this.percentage < 60) || false;
            },
            showCover() {
                return ['prod', 'demo'].includes(process.env.SERVER_TYPE);
            },
        },
        watch: {
            nomenclaturesTabSelected() {
                this.getDataCertificates();
            },
            currentTab(val) {
                if (val === 0) {
                    this.getDataCertificates();
                }
            },
        },
        mounted() {
            this.getDataCertificates();
        },
        methods: {
            ...mapActions('product', ['fetchCertificatesWB']),
            handleButtonClick() {
                if (this.tabToOpen !== null) {
                    this.$emit('proceed', this.tabToOpen);
                }
            },
            getDataCertificates() {
                if (this.isSelectedMp.id === 2) {
                    this.fetchCertificatesWB(this.options[this.nomenclaturesTabSelected].nmId);
                }
            },
        },
    };
</script>

<style scoped lang="scss">
    .chart-card {
        @include flex-grid-y;

        justify-content: space-between;
        box-shadow: 0 8px 24px rgb(0 0 0 / 4%) !important;
        border-radius: 16px;

        &__main {
            @include flex-grid-x;
        }

        &__chart {
            width: 160px;
        }

        &-info {
            @include flex-grid-y;

            justify-content: center;
            gap: 4px;

            &__text-big {
                @extend %text-h4;

                font-weight: 900;
            }

            &__text-small {
                @extend %text-caption;
            }
        }
    }

    .certificate {
        @include flex-grid-x;

        align-items: center;
        gap: 8px;
        margin-top: 12px;

        &__icon {
            @include flex-center;

            width: 40px;
            min-width: 40px !important;
            height: 40px;
            background-color: $color-main-background;
        }

        &__text {
            @extend %text-body-3;
        }
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
        box-shadow: 0 0 0 rgba(204, 169, 44, 0.4);
        border: 1px solid #ff3981;
        background: rgba(252, 110, 144, 0.24);
        color: #ff3981;
        animation: pulse 2s infinite;

        &:hover {
            background: #ff3981;
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

    .coming-soon {
        position: absolute;
        top: 0;
        left: 0;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8);

        &__text {
            padding: 9px 42px;
            border-radius: 8px;
            background: rgba(0, 0, 0, 0.4);
            font-size: 32px;
            color: $white;
            font-weight: 500;
        }
    }
</style>
