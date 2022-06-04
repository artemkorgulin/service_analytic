<template>
    <div :class="classes" @click="$emit('click')">
        <div :class="$style.checkboxInner">
            <SvgIcon :class="[$style.icon, isActive && $style.active]" :name="icon" />
        </div>
    </div>
</template>

<script>
    export default {
        name: 'BaseCheckbox',
        props: {
            value: { type: Boolean, default: false },
            indeterminate: { type: Boolean, default: false },
            autoSize: { type: Boolean, default: false },
            responsive: { type: Boolean, default: false },
        },
        computed: {
            classes() {
                return [
                    this.$style.BaseCheckbox,
                    {
                        [this.$style.AutoSize]: this.autoSize,
                        // [this.$style.on]: this.value,
                    },
                ];
            },
            icon() {
                return this.indeterminate
                    ? 'common/checkboxSemi'
                    : this.value
                    ? 'common/checkboxOn'
                    : 'common/checkboxOff';
            },
            isActive() {
                return this.indeterminate || this.value;
            },
        },
    };
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .BaseCheckbox {
        display: inline-block;
        width: 16px;
        height: 16px;
        border-radius: 4px;
        background-color: $white;
        user-select: none;

        &.checked {
            color: $accent;
        }

        .icon {
            width: 16px;
            height: 16px;

            &.active {
                color: $accent;
            }
        }

        &.AutoSize {
            width: auto;
            height: auto;
        }

        &.responsive {
            width: 1.6rem;
            height: 1.6rem;
        }
        // &.on {
        //     //
        // }
    }

    .checkboxInner {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 16px;
        height: 16px;
        cursor: pointer;
    }
</style>
