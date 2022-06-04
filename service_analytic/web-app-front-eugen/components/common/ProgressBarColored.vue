<template>
    <div :class="$style.ProgressBarColored">
        <VProgressLinear
            :value="value"
            :class="[$style.ProgressBar, filters.optimizeClass(value)]"
            height="100%"
        />
    </div>
</template>

<script>
    /* eslint-disable vue/require-prop-types */
    import productsMixin from '~mixins/products.mixin';
    export default {
        name: 'ProgressBarColored',
        mixins: [productsMixin],
        props: {
            value: {
                required: true,
            },
        },
        data() {
            return {
                filters: productsMixin.filters,
            };
        },
    };
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .ProgressBarColored {
        flex-grow: 1;
        height: 2rem;
        padding-left: 0.5rem;

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
            border-radius: 4px;
        }
    }
</style>
