<template>
    <div
        v-ripple="!isDisabled"
        :class="[
            $style.tableBodyRow,
            !isDisabled && $style.active,
            isDisabled && $style.disabled,
            isDotsPressed && $style.pressed,
        ]"
        :title="item.name && item.id ? `${item.name} - ID ${item.id}` : ''"
        @click.capture="handleClick"
        @contextmenu.prevent="handleContextMenu"
    >
        <div :class="[$style.tableBodyItem, $style.name]">
            <div :class="$style.nameInner">
                <div :class="$style.nameText">{{ item.name }}</div>
                <VIcon
                    ref="icon"
                    :class="[$style.icon, isActive && $style.active]"
                    @click="handleIconClick"
                >
                    {{ isActive ? '$eye' : '$eyeOff' }}
                </VIcon>
            </div>
        </div>
        <div :class="[$style.tableBodyItem, $style.status]">
            <template v-if="!isDisabled">
                <VStatusIcon :status="item.status_id" :text="item.status_name" expanded />
            </template>
        </div>
        <div
            v-for="heading in headings"
            :key="`table-item-${heading.key}-${item.id}`"
            :class="$style.tableBodyItem"
        >
            {{ item[heading.key] }}
        </div>
        <div
            v-if="!isDisabled"
            ref="dots"
            v-ripple="!item.disabled"
            :class="[$style.tableBodyItem, $style.dotsWrapper, isDotsPressed && $style.pressed]"
            @click="handleDotsClick"
        >
            <div :class="$style.dotsInner">
                <SvgIcon name="outlined/dots" />
            </div>
        </div>
        <div v-else :class="[$style.tableBodyItem, $style.dotsWrapper]">
            <div :class="$style.dotsInner" />
        </div>
    </div>
</template>

<script>
    export default {
        name: 'AdmTableRow',
        props: {
            item: {
                type: Object,
                default: () => ({}),
            },
            headings: {
                type: Array,
                default: () => [],
            },
            isDisabled: {
                type: Boolean,
                default: false,
            },
            isActive: {
                type: Boolean,
                default: false,
            },
        },
        computed: {
            // isDisabled() {
            //     return this.item?.disabled;
            // },
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
                // if (!this.isDisabled) {
                //     this.$emit('clicked', this.item.id);
                //     return;
                // }
                const isDotsClick = this?.$refs?.dots?.contains(e.target);
                const isIconClick = this?.$refs?.icon?.$el.contains(e.target);

                if (isDotsClick || isIconClick) {
                    e.preventDefault();
                    return;
                }
                // this.item.id,this.itempayload
                return this.$emit('clicked');
            },
            handleIconClick() {
                return this.$emit('selected');
            },
            handleDotsClick() {
                if (this.$contextMenu?.data?.show) {
                    return this.$contextMenu.close();
                }
                const rect = this.$refs.dots.getBoundingClientRect();
                return this.$emit('contextMenuOpen', {
                    attach: false,
                    x: rect.x - 231 - 8, // TODO remove hardcode width of context menu
                    y: rect.y,
                });
            },
            handleContextMenu(e) {
                return this.$emit('contextMenuOpen', {
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

        &.active {
            cursor: pointer;
        }

        &.pressed {
            background-color: $base-100;
        }

        &.disabled {
            &:hover {
                .tableBodyItem {
                    background-color: unset;
                }
            }
        }
    }

    .nameInner {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .nameText {
        @extend %ellipsis;
    }

    .icon {
        width: 16px;
        height: 16px;
        opacity: 0.3;

        &.active {
            opacity: 1;
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

        &.name {
            position: sticky;
            left: 0;
            width: 32.8rem;
            max-width: 32.8rem;
            background-color: $white;
            font-size: 1.6rem;
            line-height: 1.5;
            color: $base-900;

            @extend %ellipsis;

            @include borderLine();
            @include borderLine(false, true, false);

            @include respond-to(lg) {
                position: relative;
            }

            @include respond-to(md) {
                width: 200px;
                max-width: 200px;
                font-size: 12px;
            }
        }

        &.status {
            padding-top: 0;
            padding-bottom: 0;
        }
    }

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
