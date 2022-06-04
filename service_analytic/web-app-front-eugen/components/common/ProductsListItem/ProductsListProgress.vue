<template>
    <div
        class="progress"
        :class="[
            $style.Progress,
            displayOptionBig ? 'progress--large' : 'progress--small',
            filters.optimizeClass(optimization),
        ]"
    >
        <VMenu offset-y open-on-hover top nudge-bottom="-10" max-width="270">
            <template #activator="{ on, attrs }">
                <div class="" v-bind="attrs" v-on="on">
                    <div v-if="displayOptionBig" class="progress__title-box">
                        <span class="small-txt small-txt--medium small-txt--gray-800">
                            Степень оптимизации карточки
                        </span>
                        <i class="tooltip-btn tooltip-btn--margin-left"></i>
                    </div>

                    <div class="progress__box" :class="$style.ProgressBarBox">
                        <span
                            v-if="!displayOptionBig"
                            :class="[
                                $style.ProgressBarLegendOuter,
                                filters.optimizeClass(optimization),
                            ]"
                        >
                            <icon-loading v-if="optimization === null" :pending="true"></icon-loading>
                            <template v-else>{{ optimization | optimizePercent }}%</template>
                        </span>
                        <VProgressLinear
                            :value="optimization | optimizePercent"
                            :class="[$style.ProgressBar, filters.optimizeClass(optimization)]"
                            height="100%"
                        >
                            <template v-if="displayOptionBig" #default="{ value }">
                                <span :class="$style.ProgressBarLegendInner">{{ value }}%</span>
                            </template>
                        </VProgressLinear>
                    </div>
                </div>
            </template>
            <VCard>
                <VCard-title>Степень оптимизации</VCard-title>
                <VCard-text>
                    <template v-if="optimization">
                        Чем выше степень оптимизации вашего товара, тем чаще он будет встречаться в
                        поиске. Продажи целиком зависят от оптимизации.
                    </template>
                    <template v-else>Степень оптимизации пересчитывается</template>
                </VCard-text>
            </VCard>
        </VMenu>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';
    import productsMixin from '~mixins/products.mixin';
    export default {
        name: 'ProductsListProgress',
        mixins: [productsMixin],
        props: {
            optimization: {
                type: [Number, String],
                required: true,
            },
        },
        data() {
            return {
                filters: productsMixin.filters,
            };
        },
        computed: {
            ...mapGetters({
                displayOptionBig: 'products/GET_DISPLAY_OPTION',
            }),
        },
    };
</script>

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

    .ProgressBarBox {
        position: relative;
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
</style>
