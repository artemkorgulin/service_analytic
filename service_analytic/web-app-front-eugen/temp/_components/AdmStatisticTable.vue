<template>
    <div :class="$style.pageWrapper">
        <VFadeTransition mode="out-in" appear>
            <div v-if="blockingLoading" :class="$style.loadingWrapper">
                <div key="loading" :class="$style.loadingInner">
                    <VProgressCircular indeterminate size="100" color="accent" />
                </div>
            </div>
        </VFadeTransition>
        <div :class="$style.AdmStatisticTable">
            <PerfectScrollbar
                id="scroll_area"
                ref="scrollArea"
                :class="$style.scrollTableWrapper"
                :style="styles"
            >
                <div :class="$style.scrollTableInner">
                    <div :class="$style.tableHeadWrapper">
                        <div :class="$style.tableHeadRow">
                            <div
                                v-for="(value, key) in headings"
                                :key="`table-heading-${key}`"
                                v-ripple="enableSort"
                                :class="[$style.tableHeadItem, enableSort && $style.active]"
                                @click="handleSortChange(key)"
                            >
                                <div :class="$style.tableHeadItemInner">
                                    {{ value }}
                                    <SvgIcon
                                        v-if="enableSort"
                                        :class="$style.tableHeadItemSortIcon"
                                        name="filled/sort"
                                    />
                                </div>
                            </div>
                            <div
                                v-if="isEnableContextMenu"
                                :class="[$style.tableHeadItem, $style.empty]"
                            />
                        </div>
                    </div>
                    <div :class="$style.tableBody">
                        <AdmStatisticTableRow
                            v-for="(item, index) in items"
                            :key="`table-item-${item.id || index}`"
                            :class="$style.row"
                            :item="item"
                            :index="index"
                            :headings="headings"
                            :is-enable-click="isEnableClick"
                            :is-enable-context-menu="isEnableContextMenu"
                            @clicked="handleItemClick(item)"
                            @contextMenuOpen="options => handleContextOpen(options, item)"
                        >
                            <div
                                v-for="(value, key) in headings"
                                :key="`table-item-${index}-${key}`"
                                :class="$style.tableBodyItem"
                            >
                                {{
                                    key === 'date'
                                        ? $options.filters.formatDateTime(item[key], '$d.$m.$y')
                                        : item[key]
                                }}
                            </div>
                        </AdmStatisticTableRow>
                    </div>
                </div>
                <div v-if="isMounted && isEnableInfinity && !isEnd" :class="$style.infinityWrapper">
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
        name: 'AdmStatisticTable',
        components: {
            InfiniteLoading,
        },
        props: {
            headings: {
                type: Object,
                default: () => ({}),
            },
            items: {
                type: Array,
                default: () => [],
            },
            loading: {
                type: Boolean,
                default: false,
            },
            blockingLoading: {
                type: Boolean,
                default: false,
            },
            isEnableClick: {
                type: Boolean,
                default: false,
            },
            isEnableContextMenu: {
                type: Boolean,
                default: false,
            },
            enableSort: {
                type: Boolean,
                default: false,
            },
            isEnd: {
                type: Boolean,
                default: false,
            },
            isEnableInfinity: {
                type: Boolean,
                default: false,
            },
            contextMenu: {
                type: Object,
                default: () => ({}),
            },
        },
        data() {
            return {
                isMounted: false,
                innerWidth: 0,
                innerHeight: 0,
                contextMenuItemCached: {},
            };
        },
        computed: {
            isMobile() {
                return this.$nuxt.$vuetify.breakpoint.mdAndDown;
            },
            width() {
                return !this.isMounted || !this.innerWidth ? '100%' : this.innerWidth + 'px';
            },
            styles() {
                return {
                    height: !this.isMounted || !this.innerHeight ? '100%' : this.innerHeight + 'px',
                    width: this.width,
                };
            },
        },
        mounted() {
            this.isMounted = true;
        },
        methods: {
            handleContextOpen(options, item) {
                this.$emit('onContext', options, item);
            },
            handleItemClick(id) {
                if (!this.isEnableClick || !id) {
                    return;
                }
                this.$emit('clicked', id);
            },
            handleSortChange(val) {
                if (!this.enableSort) {
                    return;
                }
                this.$emit('sort', val);
            },
            handleReachEnd($state) {
                this.$emit('end', $state);
            },
            setScrollAreaHeight() {
                // fix Perfect Scroll bug
                if (!this.$refs.scrollArea) {
                    return;
                }
                this.innerWidth = this.$refs.scrollArea.$parent.$el.offsetWidth;
                this.innerHeight =
                    this.$refs.scrollArea.$el.offsetHeight ||
                    this.$refs.scrollArea.$parent.$el.offsetHeight;
                // console.log('🚀 ~ file: AdmStatisticTable.vue ~ line 185 ~ setScrollAreaHeight ~ this.$refs.scrollArea', this.$refs.scrollArea.$el.offsetHeight);
            },
        },
    };
</script>

<style lang="scss" module>
    .pageWrapper {
        width: 100%;
        height: 100%;
    }

    .row {
        &:hover {
            .tableBodyItem {
                background-color: $base-100;
            }
        }

        &:last-child {
            .tableBodyItem {
                &:before {
                    display: none;
                }
            }
        }

        .tableBodyItem {
            display: table-cell;
            padding-top: 1.5rem;
            padding-right: 1.6rem;
            padding-bottom: 1.5rem;
            padding-left: 1.6rem;
            vertical-align: middle;
            white-space: nowrap;
            font-size: 1.6rem;
            line-height: 150%;
            font-weight: 400;

            @include respond-to(md) {
                padding-top: 8px;
                padding-right: 8px;
                padding-bottom: 8px;
                padding-left: 8px;
                font-size: 12px;
            }

            &:first-child {
                position: sticky;
                left: 0;
                width: 32.8rem;
                max-width: 32.8rem;
                // border-right: 1px solid $base-400;
                // border-bottom: 1px solid $base-400;
                background-color: $white;
                font-size: 1.6rem;
                line-height: 1.5;
                color: $base-900;

                @extend %ellipsis;

                // @include borderLine(true,true);
                // @include borderLine(false,false,false);

                @include respond-to(lg) {
                    position: relative;
                }

                @include respond-to(md) {
                    width: 200px;
                    max-width: 200px;
                    font-size: 12px;
                }
            }
        }
    }

    .loadingWrapper {
        position: absolute;
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

    .AdmStatisticTable {
        position: relative;
        overflow: hidden;
        height: 100%;
        // padding: 16px;
        border-radius: 8px;
        border: 1px solid $base-400;
        // background: $white;
        // box-shadow: 0 4px 32px rgba(0, 0, 0, .06);

        .scrollTableWrapper {
            position: relative;
            z-index: 1;
            overflow: hidden;
            width: 100%;
            height: 100%;
            // margin: auto;
            border-radius: 8px;
            // box-shadow: 0 0 0 1px $base-400;
        }

        :global(.ps__rail-x),
        :global(.ps__rail-y) {
            z-index: 10;
        }

        .scrollTableInner {
            overflow: auto;
            display: table;
            width: 100%;
            border-style: hidden;
            border-spacing: 0;
            border-collapse: collapse;

            .tableHeadWrapper {
                display: table-header-group;
            }

            .tableHeadRow {
                display: table-row;
            }

            .tableHeadItem,
            .tableBodyItem {
                padding-top: 1.5rem;
                padding-right: 1.6rem;
                padding-bottom: 1.5rem;
                padding-left: 1.6rem;
                vertical-align: middle;

                @include respond-to(md) {
                    padding-top: 8px;
                    padding-right: 8px;
                    padding-bottom: 8px;
                    padding-left: 8px;
                }

                &:first-child {
                    position: sticky;
                    left: 0;
                    width: 32.8rem;
                    max-width: 32.8rem;
                    background-color: $white;

                    @include respond-to(md) {
                        width: 200px;
                        max-width: 200px;
                    }

                    &:after {
                        content: '';
                        position: absolute;
                        top: 0;
                        right: 0;
                        z-index: 3;
                        display: block;
                        width: 1px;
                        height: 100%;
                        background-color: $base-400;
                    }

                    &:before {
                        content: '';
                        position: absolute;
                        bottom: 0;
                        left: 0;
                        z-index: 3;
                        display: block;
                        width: 100%;
                        height: 1px;
                        background-color: $base-400;
                    }
                }
            }

            .tableHeadItem {
                position: sticky;
                top: 0;
                z-index: 6;
                display: table-cell;
                // min-width: 150px;
                min-width: 100px;
                border-spacing: 0;
                background-color: $white;
                vertical-align: middle;
                white-space: nowrap;
                font-size: 1.4rem;
                line-height: 1.5;
                color: $base-800;
                transition: $primary-transition;
                transition-property: background-color;
                user-select: none;

                &.empty {
                    min-width: unset;
                }

                @include respond-to(md) {
                    width: unset;
                    min-width: unset;
                    max-width: unset;
                    font-size: 12px;
                }

                &:after {
                    content: '';
                    position: absolute;
                    top: 0;
                    right: 0;
                    z-index: 3;
                    display: block;
                    width: 1px;
                    height: 100%;
                    background-color: $base-400;
                }

                &:before {
                    content: '';
                    position: absolute;
                    bottom: 0;
                    left: 0;
                    z-index: 3;
                    display: block;
                    width: 100%;
                    height: 1px;
                    background-color: $base-400;
                }

                &:last-child {
                    &:after {
                        display: none;
                    }
                }

                &.active {
                    cursor: pointer;

                    &:hover {
                        background-color: $base-400;
                    }
                }

                &:first-child {
                    position: sticky;
                    left: 0;
                    z-index: 11;

                    @include respond-to(lg) {
                        top: 0;
                        left: unset;
                    }
                }
            }

            .tableHeadItemInner {
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .tableHeadItemSortIcon {
                margin-right: -4px;
                margin-left: 8px;
                color: $base-600;
            }

            .tableBody {
                display: table-row-group;
            }
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
