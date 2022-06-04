<template>
    <div
        class="dashboard-card"
        :class="[$style.DashboardAverageOptimization, { [$style.disabledCard]: disabled }]"
    >
        <div :class="$style.contents">
            <h3 class="dashboard-card__header">Средняя степень оптимизации</h3>
            <div :class="$style.rating">
                <div class="dashboard-card__big-text">
                    {{ normalizeNum(value) | repDotWithCom }}%
                </div>
                <div v-if="!disabled" :class="$style.progressWrapper">
                    <VProgressLinear :color="progressColor" rounded height="32" :value="value" :class="$style.progressBar"/>
                </div>
            </div>
            <div class="dashboard-card__text" :class="$style.cardText">
                Средняя степень оптимизации карточек
                <br />
                добавленных товаров
            </div>
        </div>
        <div :class="$style.imageWrapper">
            <VImg
                :class="$style.image"
                src="/images/dashboards-magnifier.svg"
                contain
                alt="Средняя степень оптимизации"
            />
        </div>
    </div>
</template>

<script>
    import formatText from '~mixins/formatText.mixin';
    /* eslint-disable vue/require-prop-types */
    export default {
        name: 'DashboardAverageOptimization',
        mixins: [formatText],
        props: {
            value: {
                required: true,
            },
            disabled: {
                type: Boolean,
                default: false,
            },
        },
        computed: {
            progressColor() {
                const data = Math.ceil(Number(this.value));

                if (data > 84) {
                    return '#63fdaa'; // 'accent';
                } else if (data > 34) {
                    return '#ffc164'; // 'yellow';
                } else {
                    return '#f56094';
                }
            },
        },
        methods: {
            normalizeNum(num) {
                if (!num) {
                    return 0;
                }
                try {
                    return Number(num).toFixed(1);
                } catch (error) {
                    return num;
                }
            },
        },
    };
</script>

<style lang="scss" module>
    .DashboardAverageOptimization {
        display: flex;
        justify-content: space-between;

        @include respond-to(md) {
            grid-column: span 2;
            flex-wrap: wrap;
        }

        @include respond-to(xs) {
            grid-column: span 6;
        }

        &.disabledCard {
            filter: grayscale(1);
        }
    }

    .contents {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        margin-right: 1.5rem;

        @include respond-to(md) {
            margin-right: 0;
        }
    }

    .imageWrapper {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .image {
        $size: 120px;

        width: $size;
        max-width: $size;
        height: $size;
    }

    .rating {
        display: flex;
        align-items: center;
    }

    .progressWrapper {
        flex-grow: 1;
        height: 2rem;
        margin-left: 8px;
    }

    .progressBar {
        @include respond-to(md) {
          display: none;
        }
    }
</style>
