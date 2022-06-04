<template>
    <div class="product-optimization">
        <div class="product-optimization__title">Оптимизация</div>
        <div class="product-optimization__content">
            <div class="progress-sd d-flex align-center">
                <icon-loading
                    v-if="!hideOptimizationSpinner"
                    v-tooltip="'Степень оптимизации пересчитывается'"
                    :pending="true"
                    :size="36"
                    style="min-width: 36px"
                    class="mr-3"
                    v-on="on"
                ></icon-loading>

                <div
                    class="progress-custom d-flex"
                    :style="{
                        color: optimizeColor(optimization),
                    }"
                >
                    <VProgressLinear
                        :value="optimization"
                        :color="optimizeColor(optimization)"
                        height="40"
                    >
                        <template #default="{ value }">
                            <div
                                class="product-optimization__progress-tab"
                                :style="`left: calc(${value}% - 20px); color: ${optimizeColor(
                                    optimization
                                )}`"
                            >
                                {{ Math.ceil(value) }}%
                            </div>
                        </template>
                    </VProgressLinear>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            optimization: {
                type: Number,
                default: 0,
            },
        },
        computed: {
            hideOptimizationSpinner() {
                return !isNaN(parseFloat(this.optimization)) && !isNaN(this.optimization - 0);
            },
        },
        methods: {
            optimizeColor(val) {
                if (!val) return '#cfcfcf';
                else if (val > 84) {
                    return '#1ee08f';
                }
                if (val > 34) {
                    return '#ffc164';
                }
                return '#f56094';
            },
        },
    };
</script>

<style lang="scss" scoped>
    .product-optimization {
        display: flex;
        flex-grow: 1;
        flex-direction: column;
        width: 100%;
        margin-top: 16px;
        border-radius: 8px;

        &__title {
            font-size: 12px;
            font-weight: 400;
            line-height: 16px;
            color: #7e8793;
        }

        &__content {
            margin-top: 8px;
        }

        &__progress-tab {
            position: absolute;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 24px;
            border-radius: 200px;
            background-color: #fff;
            font-size: 12px;
            font-weight: 700;
            line-height: 16px;
        }
    }

    .progress-custom {
        width: 100%;
        max-width: 100%;
    }

    .progress-custom:before {
        display: none;
    }
</style>
