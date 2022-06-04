<template>
    <div :class="classes">
        <div :class="$style.wrapper" @contextmenu.prevent="handleContextMenu">
            <div
                v-ripple="isExpandable"
                :class="[$style.inner, isExpandable && $style.active]"
                @click="handleExpand"
            >
                <div :class="[$style.chevronWrapper]">
                    <SvgIcon
                        :class="[$style.chevron, isExpanded && $style.expanded]"
                        name="outlined/chevrondown"
                    />
                </div>
                <div :class="[$style.group]">
                    <div :class="$style.groupCellInner">
                        <SvgIcon :class="$style.icon" :name="icon" />
                        {{ item.name }} ({{ childLength }})
                    </div>
                </div>
            </div>
            <BaseTableCell is-flex :class="[$style.dotsWrapper, isDotsPressed && $style.pressed]">
                <div
                    ref="dots"
                    v-ripple="!item.disabled"
                    :class="[$style.dotsInner, !item.disabled && $style.active]"
                    @click="handleDotsClick"
                >
                    <SvgIcon name="outlined/dots" />
                </div>
            </BaseTableCell>
        </div>
        <VExpandTransition>
            <div v-if="isExpanded" :class="$style.groupWrapper">
                <AdmEditGoodsTableItem
                    v-for="child in children"
                    :key="'AdmEditGoodsTableItem' + child.id"
                    :class="$style.child"
                    :item="child"
                    :headers="headers"
                    :selected="selected.findIndex(it => String(it.id) === String(child.id)) > -1"
                    :highlight="highlightedElements.includes(String(child.id))"
                    is-enable-context-menu
                    is-child
                    @select="handleSelect"
                    @contextMenuOpen="options => handleChildContextOpen(options, child)"
                />
            </div>
        </VExpandTransition>
    </div>
</template>

<script>
    export default {
        name: 'AdmEditGoodsTableGroup',
        props: {
            item: {
                type: Object,
                default: () => ({}),
            },
            headers: {
                type: Array,
                default: () => [],
            },
            children: {
                type: Array,
                default: () => [],
            },
            selected: {
                type: Array,
                default: () => [],
            },
            highlightedElements: {
                type: Array,
                default: () => [],
            },
            highlight: {
                type: Boolean,
                default: false,
            },
            isEnableContextMenu: {
                type: Boolean,
                default: false,
            },
        },
        data() {
            return {
                isExpanded: false,
            };
        },
        computed: {
            classes() {
                return [
                    this.$style.AdmEditGoodsTableGroup,
                    {
                        [this.$style.expanded]: this.isExpanded,
                        [this.$style.highlight]: this.highlight,
                    },
                ];
            },
            icon() {
                return this.isExpanded ? 'filled/folderopened' : 'filled/folderclosed';
            },
            childLength() {
                return this.children?.length || 0;
            },
            isExpandable() {
                return Boolean(this.childLength);
            },
            isDotsPressed() {
                const isShow = this.$contextMenu?.data?.show;
                if (!isShow) {
                    return false;
                }
                const val = this.$contextMenu?.data?.item?.id;

                if (!val) {
                    return false;
                }
                return String(val) === String(this.item?.id);
            },
        },
        methods: {
            async handleChildContextOpen(options, itemPayload) {
                const item = { ...itemPayload, isInGroup: true };
                this.$emit('contextMenuOpen', { ...options, item });
            },
            handleExpand() {
                if (!this.isExpandable) {
                    return;
                }
                this.isExpanded = !this.isExpanded;
            },
            handleSelect(val) {
                return this.$emit('select', val);
            },
            handleDotsClick() {
                if (this.$contextMenu?.data?.show) {
                    return this.$contextMenu.close();
                }
                const rect = this.$refs.dots.getBoundingClientRect();
                this.$emit('contextMenuOpen', {
                    attach: false,
                    x: rect.x - 178.5 - 8, // TODO remove hardcode width of context menu
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
    .AdmEditGoodsTableGroup {
        position: relative;
        display: contents;

        &.expanded {
            .wrapper {
                position: relative;

                @include borderLine(true, false, false);

                &:before {
                    top: -1px;
                }
            }
        }

        &.highlight {
            .wrapper,
            .group {
                background-color: rgba($accent, 0.07);
            }
        }
    }

    .groupWrapper {
        position: relative;

        @include borderLine();
    }

    .group {
        display: flex;
        align-items: center;
        flex: 1;
        padding-left: 12px;
    }

    .child {
        background-color: $base-100;
    }

    .wrapper {
        position: relative;
        display: flex;
        height: 5.6rem;
        color: $base-800;
    }

    .inner {
        display: flex;
        flex: 1;
        opacity: 0.7;
        transition: $primary-transition;

        &.active {
            opacity: 1;
            cursor: pointer;

            &:hover {
                background-color: $base-100;
            }
        }
    }

    .chevronWrapper {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 5.6rem;
        padding: 0;
        opacity: 0.7;

        @include borderLine();
        @include borderLine(false, true, false);

        @include respond-to(md) {
            display: none;
        }

        .chevron {
            width: 1.6rem;
            max-width: 100%;
            height: 1.6rem;
            transform-origin: center;
            transition: $primary-transition;

            &.expanded {
                transform: rotate(180deg);
            }
        }
    }

    .icon {
        margin-right: 6px;
        color: $base-800;
    }

    .groupCellInner {
        display: flex;
        align-items: center;
        font-size: 1.6rem;
    }

    .dotsWrapper {
        position: sticky;
        right: 0;
        width: 5.6rem;
        min-width: 5.6rem;
        max-width: 5.6rem;
        height: 5.6rem;
        padding: 0 !important;
        flex-basis: 5.6rem;

        @include respond-to(md) {
            position: absolute;
            top: 0;
            right: 0;
        }

        transition: $primary-transition;
        transition-property: background-color;

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

            @include borderLine(true, true, true);
            @include borderLine(false, false, false);

            @include respond-to(md) {
                background-color: unset;

                &:after,
                &:before {
                    display: none;
                }
            }

            &.active {
                cursor: pointer;
            }
        }

        &.pressed {
            .dotsInner {
                background-color: $base-400;
            }
        }
    }
</style>
