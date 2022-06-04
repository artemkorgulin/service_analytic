<template>
    <div :class="$style.ViewSwitcher">
        <svg
            :class="[$style.item, $style.grid, value === 'grid' && $style.active]"
            viewBox="0 0 32 32"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            @click="handleChangeValue('grid')"
        >
            <rect x="5" y="5" width="10" height="10" rx="2" fill="currentColor" />
            <rect x="17" y="5" width="10" height="10" rx="2" fill="currentColor" />
            <rect x="5" y="17" width="10" height="10" rx="2" fill="currentColor" />
            <rect x="17" y="17" width="10" height="10" rx="2" fill="currentColor" />
        </svg>
        <svg
            :class="[$style.item, $style.list, value === 'list' && $style.active]"
            viewBox="0 0 32 32"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            @click="handleChangeValue('list')"
        >
            <rect x="4" y="8" width="24" height="4" rx="1" fill="currentColor" />
            <rect x="4" y="14" width="24" height="4" rx="1" fill="currentColor" />
            <rect x="4" y="20" width="24" height="4" rx="1" fill="currentColor" />
        </svg>
    </div>
</template>

<script>
    /* eslint-disable vue/require-prop-types,vue/require-default-prop */

    export default {
        name: 'ViewSwitcher',
        model: {
            prop: 'value',
            event: 'change',
        },
        props: {
            value: {},
            options: {
                type: Array,
                default: () => [],
            },
        },
        methods: {
            handleChangeValue(val) {
                this.$emit('change', val);
            },
        },
    };
</script>

<style lang="scss" module>
    .ViewSwitcher {
        display: flex;
        align-items: center;
        padding: 4px;
        border-radius: 8px;
        background-color: $base-100;
    }

    .item {
        width: 32px;
        height: 32px;
        border-radius: 4px;
        border: 1px solid transparent;
        color: $base-500;
        transition: $primary-transition;
        transition-property: border-color, color, box-shadow;

        &.active {
            border-color: $base-600;
            color: $base-800;
            box-shadow: 0 4px 32px rgba(0, 0, 0, 0.06);
        }
    }
</style>
