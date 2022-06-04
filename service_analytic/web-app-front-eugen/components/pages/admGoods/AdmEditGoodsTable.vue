<template>
    <BaseTable is-flex>
        <BaseTableHeadingRow is-flex :class="$style.tableHeadRow">
            <BaseTableHeadingCell
                v-show="selectable"
                v-ripple="isSelectAllEnabled"
                width="5.6rem"
                is-flex
                :class="[
                    $style.tableHeading,
                    $style.tableCellCheckboxWrapper,
                    !isSelectAllEnabled && $style.disabled,
                ]"
                is-sticky
                @click.native="handleSelectAll"
            >
                <BaseCheckbox
                    :class="$style.checkbox"
                    :value="isAllSelected"
                    :indeterminate="isAnySelected && !isAllSelected"
                />
            </BaseTableHeadingCell>
            <BaseTableHeadingCell
                v-for="header in headers"
                :key="`table-heading-${header.value}`"
                :class="$style.tableHeading"
                :width="header.width"
                is-sticky
                is-flex
            >
                {{ header.text }}
            </BaseTableHeadingCell>
            <BaseTableHeadingCell
                v-if="isEnableContextMenu"
                :class="[$style.tableHeading, $style.tableHeadingContextMenu]"
                is-sticky
                is-flex
            />
        </BaseTableHeadingRow>
        <div v-if="!items.length" :class="$style.empty">
            <div v-if="$slots.empty">
                <slot name="empty"></slot>
            </div>
            <div v-else :class="$style.emptyInner">
                Нажмите на кнопку
                <span :class="$style.bold">Добавить товары</span>
            </div>
        </div>
        <template v-else>
            <template v-for="item in items">
                <AdmEditGoodsTableGroup
                    v-if="item.isGroup"
                    :key="`AdmEditGoodsTableGroup-${item.id}`"
                    :class="$style.tableItem"
                    :item="item"
                    :children="item.goods"
                    :selected="selected"
                    :highlight="highlightedElementsComputed.includes(String(item.id))"
                    :highlighted-elements="highlightedElementsComputed"
                    :headers="headers"
                    is-enable-context-menu
                    @select="handleSelect"
                    @contextMenuOpen="handleGroupContextOpen"
                />
                <AdmEditGoodsTableItem
                    v-else
                    :key="`AdmEditGoodsTableItem-${item.id}`"
                    :class="$style.tableItem"
                    :item="item"
                    :headers="headers"
                    :selected="selected.findIndex(it => String(it.id) === String(item.id)) > -1"
                    :highlight="highlightedElementsComputed.includes(String(item.id))"
                    is-enable-context-menu
                    @select="handleSelect"
                    @contextMenuOpen="options => handleContextOpen(options, item)"
                />
            </template>
        </template>
    </BaseTable>
</template>

<script>
    import { mapState, mapActions, mapGetters } from 'vuex';

    export default {
        name: 'AdmEditGoodsTable',
        props: {
            headers: {
                type: Array,
                default: () => [],
            },
            selectable: {
                type: Boolean,
                default: false,
            },
            items: {
                type: Array,
                default: () => [],
            },
            isEnableContextMenu: {
                type: Boolean,
                default: false,
            },
            highlightElements: {
                type: Array,
                default: () => [],
            },
        },
        computed: {
            ...mapState('campaign', {
                selected: state => state.selectedGoods,
            }),
            ...mapGetters('campaign', {
                isAllSelected: 'isAllGoodsSelected',
                isAnySelected: 'isAnyGoodSelected',
                isSelectAllEnabled: 'isSelectAllGoodsEnabled',
            }),
            isContextMenuOpen() {
                return this?.$contextMenu?.data?.show;
            },
            highlightedElementsComputed() {
                if (!this.isContextMenuOpen || !this.highlightElements?.length) {
                    return [];
                }
                return this.highlightElements.map(item => String(item.id));
            },
        },
        watch: {
            selected(val) {
                this.$emit('selected', val);
            },
        },
        methods: {
            ...mapActions('campaign', {
                handleSelect: 'toggleGood',
                handleSelectAll: 'toggleAllGoods',
            }),
            handleGroupContextOpen(payload) {
                const { item, ...options } = payload;
                this.$emit('onContext', options, item);
            },
            handleContextOpen(options, item) {
                this.$emit('onContext', options, item);
            },
        },
    };
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .AdmEditGoodsTable {
        overflow: hidden;
        display: table;
        width: 100%;
        height: 100%;
        border-radius: 8px;
        table-layout: fixed;

        @include respond-to(md) {
            display: flex;
            flex-direction: column;
        }
    }

    .tableItem {
        &:last-child {
            :global(.active-cell) {
                &:after {
                    display: none;
                }
            }
        }
    }

    .tableHeadRow {
        @include respond-to(md) {
            display: none !important;
        }

        min-height: 5.6rem;
    }

    .tableRow {
        display: table-row;
    }

    .tableBody {
        display: table-row-group;

        @include respond-to(md) {
            display: flex;
            flex-direction: column;
        }
    }

    .tableHeading {
        @extend %text-body-3;

        position: relative;
        text-align: left;
        font-size: 1.4rem;
        color: $base-800;

        &:not(.tableCellCheckboxWrapper) {
            @include borderLine();
            @include borderLine(false, true, false);
        }

        &:last-child {
            &:before {
                display: none;
            }
        }
    }

    .tableGroupCheckbox {
        opacity: 0.7;
    }

    .tableHeadingContextMenu {
        width: 5.6rem;
    }

    .tableBodyData {
        @extend %text-body-1;

        font-size: 1.4rem;
    }

    .tableCellCheckboxWrapper {
        position: relative;
        width: 5.6rem;
        min-width: 5.6rem;
        padding: 0;
        vertical-align: middle;
        line-height: 100%;

        @include borderLine();
        @include borderLine(false, true, false);

        @include respond-to(md) {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 56px;

            &:after,
            &:before {
                display: none !important;
            }
        }

        &.disabled {
            user-select: none;
            pointer-events: none;
            opacity: 0.7;
            transition: $primary-transition;
        }
    }

    .groupCell {
        @extend %text-body-2;

        font-size: 1.6rem;
        color: $base-900;
        cursor: pointer;
    }

    .empty {
        @extend %text-body-2;

        height: 100%;
        text-align: center;
        color: $base-800;
        user-select: none;
        font-weight: normal;

        .emptyInner {
            @include centerer;
        }
    }

    .bold {
        font-weight: bold;
    }
</style>
