<template>
    <div :class="$style.BaseListItem">
        <slot />
        <div v-if="icon" :class="[$style.cell, $style.actionCell]">
            <VBtn
                plain
                tile
                :class="$style.cellInner"
                :disabled="actionDisabled"
                @click="$emit('eventEmitted', item)"
            >
                <SvgIcon :class="$style.action" :name="icon" />
            </VBtn>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'BaseListItem',
        props: {
            item: {
                type: Object,
                default: () => ({}),
            },
            selected: {
                type: Boolean,
                default: false,
            },
            headings: {
                type: Array,
                default: () => [],
            },
            icon: {
                type: String,
                default: null,
            },
            actionDisabled: {
                type: Boolean,
                default: false,
            },
        },
    };
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    $sku-width: 120px;
    $action-width: 56px;

    .BaseListItem {
        position: relative;
        display: flex;
    }

    .cell {
        @extend %text-body-1;

        height: 56px;
        font-size: 16px;

        @include respond-to(md) {
            height: 40px;
            font-size: 14px;
        }

        &.actionCell {
            --size: 56px;

            position: relative;
            width: var(--size) !important;
            min-width: var(--size) !important;
            height: var(--size) !important;
            min-height: var(--size) !important;
            max-height: var(--size) !important;
            padding: 0 !important;
            transition: $primary-transition;

            @include borderLine(true, true);
            @include borderLine(false, false, false);

            &:hover {
                background-color: $base-100;
            }

            @include respond-to(md) {
                --size: 40px;
            }

            &:before {
                display: block !important;
            }
        }

        &.name {
            overflow: hidden;
            flex: 1 1 auto;
        }

        &.sku {
            min-width: $sku-width;

            @extend %ellipsis;
        }
    }

    .cellInner {
        // @extend %ellipsis;
        width: 100% !important;
        min-width: 100% !important;
        max-width: 100% !important;
        height: 100% !important;
    }

    .action {
        display: block;
        margin: auto;
    }
</style>
