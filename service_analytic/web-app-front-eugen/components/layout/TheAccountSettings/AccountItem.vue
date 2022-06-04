<template>
    <div
        v-ripple="!disabled"
        :class="[$style.AccountItem, disabled && $style.disabled]"
        v-on="$listeners"
    >
        <slot name="prepend" />
        <div :class="$style.title">
            <slot name="text" />
        </div>
        <slot name="append" />
    </div>
</template>

<script>
    export default {
        name: 'AccountItem',
        props: {
            disabled: {
                type: Boolean,
                default: false,
            },
        },
    };
</script>

<style lang="scss" module>
    .AccountItem {
        display: flex;
        align-items: center;
        height: 56px;
        padding: 12px 16px;
        gap: 12px;
        font-size: 16px;
        transition: $primary-transition;
        transition-property: background-color;
        cursor: pointer;

        &:hover {
            background-color: #e9edf2;
        }

        &.disabled {
            pointer-events: none;
        }
    }

    .title {
        @extend %ellipsis;
    }
</style>
