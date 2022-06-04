<template>
    <div class="progress-bar">
        <span v-if="optimization" class="progress-bar__value">{{ optimization }} %</span>
        <icon-loading v-else :pending="true"></icon-loading>
        <VProgressLinear
            :value="optimization | optimizePercent"
            height="20px"
            style="border-radius: 8px"
            :color="optimizationColor"
            class="progress--green"
        ></VProgressLinear>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';
    import productsMixin from '~mixins/products.mixin';

    export default {
        name: 'Optimization',
        mixins: [productsMixin],
        data() {
            return {
                filters: productsMixin.filters,
            };
        },
        computed: {
            ...mapGetters({
                displayOptionBig: 'products/GET_DISPLAY_OPTION',
            }),
            optimization() {
                return this.params.value;
            },
            optimizationColor() {
                const opt = Math.ceil(this.params.value);
                const terms = [opt <= 84 || '#1ee08f', opt <= 34 || '#ffc24d', '#ff6d89'];

                return terms.find(item => typeof item !== 'boolean');
            },
        },
    };
</script>

<style lang="scss" scoped>
    .progress-bar {
        display: flex;
        align-items: center;
        gap: 12px;

        &__value {
            font-weight: 600;
            font-size: 16px;
        }
    }
</style>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .Progress {
        & :global(.v-progress-linear__content) {
            justify-content: left;
        }

        & :global(.progress--green) {
            & :global(.v-progress-linear__determinate) {
                background: linear-gradient(94.35deg, #1ee08f 0%, #63fdaa 100%);
            }
        }

        & :global(.progress--yellow) {
            & :global(.v-progress-linear__determinate) {
                background: linear-gradient(94.35deg, #ffc24d 0%, #ffc164 100%);
            }
        }

        & :global(.progress--pink) {
            & :global(.v-progress-linear__determinate) {
                background: linear-gradient(94.35deg, #ff6d89 0%, #f56094 100%);
            }
        }
    }

    .ProgressBar {
        position: relative;

        & :global(.v-progress-linear__background) {
            border-color: transparent !important;
            background-color: transparent !important;
        }

        & :global(.v-progress-linear__buffer) {
            opacity: 0;
        }

        & :global(.v-progress-linear__determinate) {
            border-radius: 8px;
        }
    }

    .ProgressBarLegendInner {
        margin: 0 0.5rem;
        font-size: size(20);
        font-weight: bold;
        color: $white;
    }

    .ProgressBarLegendOuter {
        position: absolute;
        top: 50%;
        right: 100%;
        display: flex;
        width: 2.875rem;
        margin: 0 0.5rem 0 0;
        font-size: 1rem;
        transform: translateY(-50%);
        font-weight: 600;

        &:global(.progress--green) {
            color: #1ee08f;
        }

        &:global(.progress--yellow) {
            color: #ffc24d;
        }

        &:global(.progress--pink) {
            color: #ff6d89;
        }
    }

    .progress-bar {
        display: flex;
        gap: 8px;
    }
</style>
