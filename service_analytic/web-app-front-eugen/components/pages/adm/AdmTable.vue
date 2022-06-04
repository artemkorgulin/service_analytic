<template>
    <div id="scroll_area_wrapper" ref="scrollAreaWrapper" :class="$style.wrapper">
        <VFadeTransition mode="out-in" appear>
            <div v-if="blockingLoading" :class="$style.loadingWrapper">
                <div key="loading" :class="$style.loadingInner">
                    <VProgressCircular indeterminate size="100" color="accent" />
                </div>
            </div>
        </VFadeTransition>
        <div :class="$style.AdmTable">
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
                                v-for="item in headings"
                                :key="`table-heading-${item.key}`"
                                v-ripple="enableSort"
                                :class="[$style.tableHeadItem, enableSort && $style.active]"
                                @click="handleSortChange(item.key)"
                            >
                                <div :class="$style.tableHeadItemInner">
                                    <BaseTooltip
                                        v-if="item.tooltip"
                                        is-responsive
                                        :text="item.tooltip"
                                    />
                                    {{ item.text }}
                                    <SvgIcon
                                        v-if="enableSort"
                                        :class="$style.tableHeadItemSortIcon"
                                        name="filled/sort"
                                    />
                                </div>
                            </div>
                            <div
                                :class="[$style.tableHeadItem, $style.empty, $style.withContext]"
                            />
                        </div>
                    </div>
                    <div :class="$style.tableBody">
                        <AdmTableRow
                            :item="{ ...counters, name: 'Итого и среднее' }"
                            :headings="rowHeadings"
                            is-disabled
                            :is-active="isAllSelected"
                            @selected="handleSelect(null)"
                        />
                        <AdmTableRow
                            v-for="(item, index) in items"
                            :key="`table-item-${item.id || index}`"
                            :item="item"
                            :headings="rowHeadings"
                            :is-active="selectedItems.includes(item.id)"
                            @selected="handleSelect(item)"
                            @clicked="handleItemClick(item)"
                            @contextMenuOpen="options => handleContextOpen(options, item)"
                        />
                    </div>
                </div>
                <div v-if="isMounted && !isEnd" :class="$style.infinityWrapper">
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
    import { isUnset } from '~utils/helpers';
    export default {
        name: 'AdmTable',
        components: {
            InfiniteLoading,
        },
        props: {
            headings: {
                type: Array,
                default: () => [],
            },
            items: {
                type: Array,
                default: () => [],
            },
            counters: {
                type: Object,
                default: () => ({}),
            },
            loading: {
                type: Boolean,
                default: false,
            },
            blockingLoading: {
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
            contextMenu: {
                type: Object,
                default: () => ({}),
            },
            value: {
                type: [Array],
                default: () => [],
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
            selectedItems: {
                get() {
                    return this.value;
                },
                set(val) {
                    return this.$emit('input', val);
                },
            },
            isAllSelected() {
                return !this.selectedItems.length;
            },
            isMobile() {
                return this.$nuxt.$vuetify.breakpoint.mdAndDown;
            },
            width() {
                return !this.isMounted || !this.innerWidth ? '100%' : this.innerWidth + 'px';
            },
            styles() {
                try {
                    const mainArea = document.getElementById('main-adm-area');

                    return {
                        height: `${mainArea.offsetHeight - 32}px`,
                        width: this.width,
                    };
                } catch {
                    return {
                        height:
                            !this.isMounted || !this.innerHeight ? '100%' : this.innerHeight + 'px',
                        width: this.width,
                    };
                }
            },
            rowHeadings() {
                return this.headings.slice(2);
            },
        },
        mounted() {
            this.isMounted = true;
        },
        methods: {
            handleSelect(payload) {
                if (isUnset(payload)) {
                    this.selectedItems = [];
                    return;
                }
                const index = this.selectedItems.indexOf(payload.id);
                if (index > -1) {
                    this.selectedItems.splice(index, 1);
                    return;
                }
                this.selectedItems.push(payload.id);
            },
            handleContextOpen(options, item) {
                this.$emit('onContext', options, item);
            },
            handleItemClick(id) {
                if (!id) {
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
                this.innerWidth = this.$refs.scrollAreaWrapper.offsetWidth;
                this.innerHeight = this.$refs.scrollAreaWrapper.offsetHeight;
            },
        },
    };
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .wrapper {
        width: 100%;
        height: 100%;
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

    .AdmTable {
        position: relative;
        overflow: hidden;
        height: 100%;
        border-radius: 8px;
        border: 1px solid $base-400;

        .scrollTableWrapper {
            position: relative;
            z-index: 1;
            overflow: hidden;
            width: 100%;
            height: 100%;
            border-radius: 8px;
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

                    @include borderLine();
                }
            }

            .tableHeadItem {
                position: sticky;
                top: 0;
                z-index: 6;
                display: table-cell;
                min-width: 100px;
                height: 5.6rem;
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

                    &.withContext {
                        right: 0;
                    }
                }

                @include respond-to(md) {
                    width: unset;
                    min-width: unset;
                    max-width: unset;
                    font-size: 12px;
                }

                @include borderLine();
                @include borderLine(false, false, false);

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

                    @include borderLine(false, true, false);

                    @include respond-to(lg) {
                        top: 0;
                        left: unset;
                    }

                    &:before {
                        right: 0;
                        left: unset;
                    }
                }

                &:nth-child(2) {
                    &:before {
                        display: none !important;
                    }
                }
            }

            .tableHeadItemInner {
                display: flex;
                align-items: center;
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
