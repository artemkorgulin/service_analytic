<template>
    <BaseTableCell :class="[$style.cell]" :width="width" is-flex>
        <div v-if="!$slots.default" :class="$style.cellInner">
            {{ text }}
        </div>
        <slot />
    </BaseTableCell>
</template>

<script>
    export default {
        props: {
            width: {
                type: String,
                default: '',
            },
            text: {
                type: [String, Number],
                default: '',
            },
        },
    };
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    $sku-width: 120px;
    $action-width: 56px;

    .cellInner {
        @extend %ellipsis;
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

            @include borderLine(false, false);

            &:hover {
                background-color: $base-100;
            }

            @include respond-to(md) {
                --size: 40px;
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
</style>
