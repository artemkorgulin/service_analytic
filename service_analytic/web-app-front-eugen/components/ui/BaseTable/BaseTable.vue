<template>
    <div>
        <VFadeTransition mode="out-in" appear>
            <div v-if="blockingLoading" :class="$style.loadingWrapper">
                <div key="loading" :class="$style.loadingInner">
                    <VProgressCircular indeterminate size="100" color="accent" />
                </div>
            </div>
        </VFadeTransition>
        <div v-resize="setScrollAreaHeight" :class="classes">
            <PerfectScrollbar
                id="scroll_area"
                ref="scrollArea"
                :class="$style.baseTableWrapper"
                :style="styles"
            >
                <div
                    :class="[
                        $style.baseTableInner,
                        !isHorizontalScrollEnabled && $style.withoutHorizontalScroll,
                    ]"
                >
                    <slot></slot>
                </div>
                <div
                    v-if="isMounted && isInfinityEnable && !isInfinityEnd"
                    :class="$style.infinityWrapper"
                >
                    <InfiniteLoading
                        ref="infinityLoading"
                        :class="$style.infinity"
                        :style="{ width: width }"
                        force-use-infinite-wrapper="#scroll_area"
                        @infinite="handleReachEnd"
                    >
                        <div slot="no-results"></div>
                        <div slot="no-more"></div>
                    </InfiniteLoading>
                </div>
            </PerfectScrollbar>
            <VProgressLinear
                :active="loading"
                :class="$style.loadingLinearBar"
                indeterminate
                color="base-500"
            />
        </div>
    </div>
</template>

<script>
    import InfiniteLoading from 'vue-infinite-loading';

    export default {
        name: 'BaseTable',
        components: {
            InfiniteLoading,
        },
        props: {
            isFlex: {
                type: Boolean,
                default: false,
            },
            loading: {
                type: Boolean,
                default: false,
            },
            blockingLoading: {
                type: Boolean,
                default: false,
            },
            isInfinityEnable: {
                type: Boolean,
                default: false,
            },
            isInfinityEnd: {
                type: Boolean,
                default: false,
            },
            contextMenu: {
                type: Object,
                default: () => ({}),
            },
            isHorizontalScrollEnabled: {
                type: Boolean,
                default: false,
            },
        },
        data() {
            return {
                isMounted: false,
                innerWidth: 0,
                innerHeight: 0,
            };
        },
        computed: {
            classes() {
                return [
                    this.$style.BaseTable,
                    {
                        [this.$style.isFlex]: this.isFlex,
                        [this.$style.isTable]: !this.isFlex,
                    },
                ];
            },
            isMobile() {
                return this.$nuxt.$vuetify.breakpoint.mdAndDown;
            },
            styles() {
                return {
                    height: !this.isMounted || !this.innerHeight ? '100%' : this.innerHeight + 'px',
                    // width: this.width,
                };
            },
        },
        mounted() {
            this.isMounted = true;
            this.setScrollAreaHeight();
        },
        methods: {
            setScrollAreaHeight() {
                // fix Perfect Scroll bug
                if (!this.$refs.scrollArea) {
                    return;
                }
                // this.innerWidth = this.$refs.scrollArea.$parent.$el.offsetWidth - 32;
                this.innerHeight = this.$refs.scrollArea.$parent.$el.offsetHeight; // - 32;
            },
        },
    };
</script>

<style lang="scss" module>
    .BaseTable {
        position: relative;
        overflow: hidden;
        height: 100%;
        // padding: 16px;
        // border-radius: 2.4rem;
        // background: $white;
        // box-shadow: 0 4px 32px rgba(0, 0, 0, .06);

        .baseTableWrapper {
            position: relative;
            z-index: 1;
            overflow: hidden;
            width: 100%;
            height: 100%;
            margin: auto;
            border-radius: 8px;
            box-shadow: 0 0 0 1px $base-400;
        }

        :global(.ps__rail-x),
        :global(.ps__rail-y) {
            z-index: 10;
        }

        .baseTableInner {
            overflow: auto;
            display: table;
            width: 100%;
            border-style: hidden;
            border-spacing: 0;
            border-collapse: collapse;

            &.withoutHorizontalScroll {
                table-layout: fixed;
            }
        }

        &.isFlex {
            .baseTableInner {
                display: flex;
                flex-direction: column;
            }
        }
    }

    .loadingWrapper {
        position: absolute;
        bottom: 16px;
        z-index: 11;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.5);

        .loadingInner {
            @include centerer;
        }
    }

    .infinityWrapper {
        position: sticky;
        left: 0;
        display: block;
        width: 100%;
        height: 4rem;

        @include respond-to(sm) {
            height: 38px;
        }
    }

    .loadingLinearBar {
        position: absolute;
        right: 0;
        bottom: 0;
        left: 0;
        height: 4px;
    }
</style>
