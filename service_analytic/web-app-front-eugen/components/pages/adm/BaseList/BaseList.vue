<template>
    <div :class="$style.BaseList">
        <div v-if="isShowHeadings" :class="$style.headingRow">
            <slot name="heading-prepend" />
            <BaseTableHeadingCell
                v-for="heading in headings"
                :key="heading.value"
                :class="$style.headingCell"
                :inner-text="heading.text"
                :width="heading.width"
                :max-width="heading.maxWidth"
                is-border-bottom
                is-border-right
                is-flex
            />
            <slot name="heading-append" />
        </div>
        <component
            :is="wrapperComponentData.is"
            id="goods_list_scroll_area"
            ref="scrollArea"
            :class="$style.wrapper"
            :style="{ height, maxHeight }"
            v-bind="wrapperComponentData.bind"
        >
            <template v-if="$slots.default">
                <slot />
                <div
                    v-if="isMounted && isInfiniteLoading && !isInfiniteLoadingEnd"
                    :class="$style.infinityWrapper"
                >
                    <InfiniteLoading
                        ref="infinityLoading"
                        :class="$style.infinity"
                        v-bind="{
                            ...(wrapperComponentData.isScrollable
                                ? {
                                      'force-use-infinite-wrapper': '#goods_list_scroll_area',
                                  }
                                : null),
                        }"
                        @infinite="handleReachEnd"
                    >
                        <div slot="no-results"></div>
                        <div slot="no-more"></div>
                    </InfiniteLoading>
                </div>
            </template>
            <template v-else>
                <slot name="empty" />
            </template>
        </component>
    </div>
</template>

<script>
    import InfiniteLoading from 'vue-infinite-loading';
    export default {
        name: 'BaseList',
        components: {
            InfiniteLoading,
        },
        props: {
            headings: {
                type: Array,
                default: () => [],
            },
            isInfiniteLoading: {
                type: Boolean,
                default: false,
            },
            isInfiniteLoadingEnd: {
                type: Boolean,
                default: false,
            },
            isShowHeadings: {
                type: Boolean,
                default: false,
            },
            height: {
                type: String,
                default: '100%',
            },
            maxHeight: {
                type: String,
                default: 'unset',
            },
            isScrollable: {
                type: Boolean,
                default: undefined,
            },
        },
        data() {
            return {
                isMounted: false,
            };
        },
        computed: {
            wrapperComponentData() {
                const isScrollable = this.isScrollable;
                return {
                    isScrollable,
                    is: isScrollable ? 'PerfectScrollbar' : 'div',
                    bind: isScrollable
                        ? {
                              options: { suppressScrollX: true, style: { height: this.height } },
                          }
                        : {},
                };
            },
        },
        mounted() {
            this.isMounted = true;
        },
        methods: {
            handleReachEnd($state) {
                this.$emit('loadMore', $state);
            },
        },
    };
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .BaseList {
        position: relative;
        overflow: hidden;
        width: 100%;
        border-radius: 8px;
        border: 1px solid $base-400;
    }

    .headingRow {
        position: relative;
        display: flex;
        min-height: 56px;

        @include respond-to(md) {
            min-height: 40px;
        }

        &:not(&:last-child) {
            @include borderLine();
        }

        .headingCell {
            font-size: 14px;
        }
    }

    :global(.ps__rail-x),
    :global(.ps__rail-y) {
        z-index: 10;
    }
</style>
