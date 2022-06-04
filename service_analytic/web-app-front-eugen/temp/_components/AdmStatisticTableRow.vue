<template>
    <div
        v-ripple="isActive"
        :class="[$style.tableBodyRow, isActive && $style.active, isDotsPressed && $style.pressed]"
        :title="item.name && item.id ? `${item.name} - ID ${item.id}` : ''"
        @click.capture="handleClick"
        @contextmenu.prevent="handleContextMenu"
    >
        <slot />
        <!-- <div v-for="(value, key) in headings"
             :key="`table-item-${index}-${key}`"
             :class="$style.tableBodyItem"
        >
            {{ item[key] }}
        </div>-->
        <div
            v-if="isEnableContextMenu"
            ref="dots"
            v-ripple="!item.disabled"
            :class="[$style.tableBodyItem, $style.dotsWrapper, isDotsPressed && $style.pressed]"
            @click="handleDotsClick"
        >
            <div :class="$style.dotsInner">
                <SvgIcon name="outlined/dots" />
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'AdmStatisticTableRow',
        props: {
            item: {
                type: Object,
                default: () => ({}),
            },
            headings: {
                type: Object,
                default: () => ({}),
            },
            isEnableClick: {
                type: Boolean,
                default: false,
            },
            isEnableContextMenu: {
                type: Boolean,
                default: false,
            },
            index: {
                type: [String, Number],
                default: '',
            },
        },
        computed: {
            isActive() {
                return this.isEnableClick && this.index !== 0;
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
            handleClick(e) {
                if (!this.isEnableContextMenu) {
                    this.$emit('clicked', this.item.id);
                    return;
                }
                const isDotsClick = this.$refs?.dots?.contains(e.target);

                if (isDotsClick) {
                    e.preventDefault();
                    return;
                }
                this.$emit('clicked', this.item.id);
            },
            handleDotsClick(e) {
                if (this.$contextMenu?.data?.show) {
                    return this.$contextMenu.close();
                }
                const rect = this.$refs.dots.getBoundingClientRect();
                this.$emit('contextMenuOpen', {
                    attach: false,
                    x: rect.x - 231 - 8, // TODO remove hardcode width of context menu
                    y: rect.y,
                });
            },
            handleContextMenu(e) {
                this.$emit('contextMenuOpen', {
                    attach: false,
                    x: e.clientX,
                    y: e.clientY,
                });
            },
        },
    };
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .tableBodyRow {
        display: table-row;

        // &:hover {
        //     .tableBodyItem {
        //         background-color: $base-100;
        //     }
        // }

        // &:last-child {
        //     .tableBodyItem {
        //         &:before {
        //             display: none;
        //         }
        //     }
        // }

        &.active {
            cursor: pointer;
        }

        &.pressed {
            background-color: $base-100;
        }
    }

    // .tableBodyItem {
    //     display: table-cell;
    //     padding-top: 1.5rem;
    //     padding-right: 1.6rem;
    //     padding-bottom: 1.5rem;
    //     padding-left: 1.6rem;
    //     vertical-align: middle;
    //     white-space: nowrap;
    //     font-size: 1.6rem;
    //     line-height: 150%;
    //     font-weight: 400;

    //     @include respond-to(md) {
    //         padding-top: 8px;
    //         padding-right: 8px;
    //         padding-bottom: 8px;
    //         padding-left: 8px;
    //         font-size: 12px;
    //     }

    //     &:first-child {
    //         position: sticky;
    //         left: 0;
    //         width: 32.8rem;
    //         max-width: 32.8rem;
    //         // border-right: 1px solid $base-400;
    //         // border-bottom: 1px solid $base-400;
    //         background-color: $white;
    //         font-size: 1.6rem;
    //         line-height: 1.5;
    //         color: $base-900;

    //         @extend %ellipsis;

    //         @include borderLine();
    //         @include borderLine(false,true,false);

    //         @include respond-to(lg) {
    //             position: relative;
    //         }

    //         @include respond-to(md) {
    //             width: 200px;
    //             max-width: 200px;
    //             font-size: 12px;
    //         }
    //     }
    // }

    .dotsWrapper {
        position: sticky;
        right: 0;
        min-width: 5.6rem;
        padding: 0 !important;

        @include respond-to(md) {
            width: 40px;
            min-width: 40px;
            height: 40px;
        }

        .dotsInner {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 5.6rem;
            border-bottom: 1px solid $base-400;
            border-left: 1px solid $base-400;
            background-color: $white;
            color: $base-700;

            @include respond-to(md) {
                width: 40px;
                height: 40px;
            }
        }

        &.pressed {
            .dotsInner {
                background-color: $base-400;
            }
        }
    }
</style>
