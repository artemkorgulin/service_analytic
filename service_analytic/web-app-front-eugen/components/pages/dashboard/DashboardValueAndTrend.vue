<template>
    <div :class="$style.DashboardValueAndTrend">
        <div :class="$style.title">{{ title }}</div>
        <div :class="$style.value">{{ value | splitThousands }}</div>
        <!-- <div :class="[$style.Trend, trendingClass]">
            {{ trendValue }}
            <SvgIcon name="outlined/arrowUp" :class="[$style.TrendIcon, IconClass]" />
        </div> -->
    </div>
</template>

<script>
    /* eslint-disable vue/require-prop-types */
    export default {
        name: 'DashboardValueAndTrend',
        props: {
            title: {
                type: String,
                required: true,
            },
            value: {
                required: true,
            },
            trendValue: {
                default: 0,
            },
            trendingUp: {
                type: Boolean,
                default: false,
            },
            trendingDown: {
                type: Boolean,
                default: false,
            },
        },
        computed: {
            trendingClass() {
                if (this.trendingUp) {
                    return this.$style.TrendingUp;
                } else if (this.trendingDown) {
                    return this.$style.TrendingDown;
                } else {
                    return this.$style.TrendingStagnate;
                }
            },
            IconClass() {
                if (this.trendingUp) {
                    return null;
                } else if (this.trendingDown) {
                    return this.$style.TrendIconDown;
                } else {
                    return this.$style.TrendIconHidden;
                }
            },
        },
    };
</script>

<style lang="scss" module>
    .DashboardValueAndTrend {
        display: flex;
        justify-content: space-between;
        padding: 1rem;
        border-radius: 8px;
        border: 1px solid $base-400;
        flex-direction: column;
    }

    .title {
        font-size: 0.875rem;
        line-height: 1.35;
        font-weight: 500;
        color: $color-gray-dark-800;
    }

    .value {
        font-size: 1.5rem;
        line-height: 1.35;
        font-weight: 500;
        color: $color-main-font;
    }

    .Trend {
        display: flex;
        align-items: center;
        align-self: baseline;
        height: 24px;
        margin-top: 6px;
        //margin-left: 8px;
        padding: 0 10px;
        border-radius: 4px;
        font-size: 16px;
        line-height: 1;
        font-weight: 700;
    }

    .TrendingUp {
        background-color: rgba(32, 194, 116, 0.06);
        color: $color-green-secondary;
    }

    .TrendingDown {
        background-color: rgba(255, 11, 153, 0.06);
        color: $color-pink-dark;
    }

    .TrendingStagnate {
        background-color: $color-gray-light;
        color: $color-gray-dark;
    }

    .TrendIcon {
        margin-left: 8px;

        &:global(.icon.sprite-outlined) {
            width: 14px;
            height: 14px;
        }
    }

    .TrendIconDown {
        transform: rotate(180deg);
    }

    .TrendIconHidden {
        display: none;
    }
</style>
