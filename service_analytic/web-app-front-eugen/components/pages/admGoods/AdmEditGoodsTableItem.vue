<template>
    <BaseTableRow :class="classes" is-flex @contextmenu.prevent.native="handleContextMenu">
        <BaseTableCell
            v-ripple
            is-flex
            :class="[$style.tableBodyData, $style.tableCellCheckboxWrapper]"
            class="active-cell"
            @click.native="handleSelect(item)"
        >
            <BaseCheckbox :class="$style.checkbox" :value="selected" />
        </BaseTableCell>
        <BaseTableCell
            v-for="header in headers"
            :key="`${header.value}-${item.id}`"
            is-flex
            is-mobile-variant
            :width="header.width"
            :class="[$style.tableBodyData, $style.tableCellItem]"
        >
            <div :class="$style.key">
                {{ header.text }}
            </div>
            <div :class="$style.val">
                {{
                    header.valueTransfromFunction &&
                    typeof header.valueTransfromFunction === 'function'
                        ? header.valueTransfromFunction(
                              item[isChild ? header.childValue || header.value : header.value]
                          )
                        : item[isChild ? header.childValue || header.value : header.value]
                }}
            </div>
        </BaseTableCell>
        <BaseTableCell
            v-if="isEnableContextMenu"
            is-flex
            :class="[$style.dotsWrapper, isDotsPressed && $style.pressed]"
            class="active-cell"
        >
            <div
                ref="dots"
                v-ripple="!item.disabled"
                :class="$style.dotsInner"
                @click="handleDotsClick"
            >
                <SvgIcon name="outlined/dots" />
            </div>
        </BaseTableCell>
    </BaseTableRow>
</template>

<script>
    export default {
        name: 'AdmEditGoodsTableItem',
        props: {
            item: {
                type: Object,
                default: () => ({}),
            },
            selected: {
                type: Boolean,
                default: false,
            },
            highlight: {
                type: Boolean,
                default: false,
            },
            headers: {
                type: Array,
                default: () => [],
            },
            isEnableContextMenu: {
                type: Boolean,
                default: false,
            },
            isChild: {
                type: Boolean,
                default: false,
            },
        },
        computed: {
            classes() {
                return [
                    this.$style.AdmEditGoodsTableItem,
                    {
                        [this.$style.selected]: this.selected,
                        [this.$style.highlight]: this.highlight,
                    },
                ];
            },
            isDotsPressed() {
                const isShow = this.$nuxt.$contextMenu?.data?.show;
                if (!isShow) {
                    return false;
                }
                const val = this.$nuxt.$contextMenu?.data?.item?.id;

                if (!val) {
                    return false;
                }
                return String(val) === String(this.item?.id);
            },
        },
        methods: {
            handleSelect(val) {
                return this.$emit('select', val);
            },
            handleDotsClick(e) {
                if (this.$contextMenu?.data?.show) {
                    return this.$contextMenu.close();
                }
                const rect = this.$refs.dots.getBoundingClientRect();
                this.$emit('contextMenuOpen', {
                    attach: false,
                    x: rect.x - 211.5 - 8, // TODO remove hardcode width of context menu
                    y: rect.y,
                    item: this.item,
                });
            },
            handleContextMenu(e) {
                return this.$emit('contextMenuOpen', {
                    attach: false,
                    x: e.clientX,
                    y: e.clientY,
                    item: this.item,
                });
            },
        },
    };
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */

    .AdmEditGoodsTableItem {
        min-height: 5.6rem;

        &.selected {
            background-color: $base-100;
        }

        &.highlight {
            background-color: rgba($accent, 0.07);
        }

        &:last-child {
            .tableCell.tableCellCheckboxWrapper {
                &:after {
                    display: none;
                }
            }
        }

        .tableCell {
            @extend %ellipsis;
        }

        @include respond-to(md) {
            position: relative;
            padding-top: 0;
            padding-bottom: 24px;
            flex-direction: column;

            @include borderLine(true);
        }

        @include respond-to(sm) {
            padding-bottom: 16px;
        }
    }

    .tableCellCheckboxWrapper {
        position: relative;
        width: 5.6rem;
        min-width: 5.6rem;
        padding: 0;
        vertical-align: middle;
        line-height: 100%;
        transition: $primary-transition;
        transition-property: background-color;
        cursor: pointer;

        @include borderLine();
        @include borderLine(false, true, false);

        @include respond-to(md) {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 5.6rem;

            &:after,
            &:before {
                display: none !important;
            }
        }

        &:hover {
            background-color: $base-400;
        }
    }

    .tableCellItem {
        @include respond-to(md) {
            display: flex;
            justify-content: space-between;
            width: 100%;
            padding-top: 16px;
            padding-bottom: 16px;
        }

        .key {
            display: none;

            @include respond-to(md) {
                display: block;
            }
        }

        .val {
            @extend %ellipsis;
        }

        @include respond-to(md) {
            padding-top: 8px;
            padding-bottom: 8px;
        }
    }

    .dotsWrapper {
        position: sticky;
        right: 0;
        overflow: unset !important;
        min-width: 5.6rem;
        padding: 0 !important;
        transition: $primary-transition;
        transition-property: background-color;
        cursor: pointer;

        @include respond-to(md) {
            position: absolute;
            top: 0;
            right: 0;
            width: 56px;
            min-width: 56px;
            height: 56px;
        }

        &:hover {
            .dotsInner {
                background-color: $base-400;
            }
        }

        .dotsInner {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            background-color: $white;
            color: $base-700;
            transition: $primary-transition;
            transition-property: background-color;

            @include borderLine(true, true, true);
            @include borderLine(false, false, false);

            @include respond-to(md) {
                background-color: unset;

                &:after,
                &:before {
                    display: none;
                }
            }
        }

        &.pressed {
            .dotsInner {
                background-color: $base-400;
            }
        }
    }
</style>
