<template>
    <div :class="$style.BaseRadio">
        <VIcon :class="[$style.icon, isActive && $style.active]">{{ icon }}</VIcon>
    </div>
</template>

<script>
    export default {
        name: 'BaseRadio',
        props: {
            value: { type: Boolean, default: false },
            indeterminate: { type: Boolean, default: false },
        },
        computed: {
            icon() {
                if (this.indeterminate) {
                    return '$radioOff';
                }
                return this.value ? '$radioOn' : '$radioOff';
            },
            isActive() {
                return this.indeterminate || this.value;
            },
        },
    };
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .BaseRadio {
        // max-height: 22px;

        &.checked {
            color: $accent;
        }

        .icon,
        .icon svg {
            width: 22px;
            height: 22px;
        }

        .icon {
            display: flex !important;
            align-items: center;
            height: 100%;
            margin: auto;

            &.active {
                color: $accent;
            }
        }
    }
</style>
