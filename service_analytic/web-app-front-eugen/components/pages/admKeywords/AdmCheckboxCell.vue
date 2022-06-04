<template>
    <BaseTableHeadingCell
        v-ripple="!disabled"
        :class="[
            $style.AdmCheckboxCell,
            disabled && $style.disabled,
            responsive && $style.responsive,
        ]"
        is-border-bottom
        is-border-right
        is-flex
    >
        <BaseCheckbox :class="$style.checkbox" :value="value" :indeterminate="indeterminate" />
    </BaseTableHeadingCell>
</template>

<script>
    export default {
        name: 'AdmCheckboxCell',
        props: {
            indeterminate: Boolean,
            value: Boolean,
            disabled: Boolean,
            responsive: {
                type: Boolean,
                default: true,
            },
        },
    };
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .AdmCheckboxCell {
        min-width: 56px;
        min-height: 56px;
        padding-top: unset !important;
        padding-bottom: unset !important;

        &.disabled {
            pointer-events: none;
            user-select: none;
            opacity: 0.7;
        }

        @include respond-to(md) {
            min-width: 40px;
        }

        &.responsive {
            min-width: 5.6rem;
            min-height: 5.6rem;
        }
    }
</style>
