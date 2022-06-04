<template>
    <div class="cell-number-trend" :class="{ 'alert': alert }">{{ value }} <sup v-if="trend != 0" :class="{ 'negative': negativeTrend }">{{ trend }}</sup></div>
</template>

<script>
    export default {
        name: 'AgCustomCellNumberAndTrend',
        data() {
            return {
                value: 0,
                trend: 0,
                percent: false,
                alert: false,
            };
        },
        computed: {
            negativeTrend() {
                return this.trend < 0;
            }
        },
        created() {
            this.value = this.params.value || 0;
            this.trend = this.params.trend || 0;
            this.alert = this.params.alert || false;
        }
    };
</script>

<style lang="scss" scoped>
    .cell-number-trend {
        width: 100%;
        height: 100%;
        padding: 0 16px;
        color: $base-900;

        &.alert {
            border: 2px solid $error;
        }

        & > sup {
            color: $color-green-secondary;

            &.negative {
                color: $error;
            }
        }
    }
</style>
