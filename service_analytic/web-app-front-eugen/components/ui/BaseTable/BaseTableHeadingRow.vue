<template>
    <div :class="classes">
        <slot />
    </div>
</template>

<script>
    export default {
        name: 'BaseTableHeadingRow',
        props: {
            isFlex: {
                type: Boolean,
                default: false,
            },
        },
        computed: {
            classes() {
                return [
                    this.$style.BaseTableHeadingRow,
                    {
                        [this.$style.isFlex]: this.isFlex,
                        [this.$style.isTable]: !this.isFlex,
                    },
                ];
            },
        },
    };
</script>

<style lang="scss" module>
    .BaseTableHeadingRow {
        display: table-row;

        &.isFlex {
            display: flex;
        }
    }
</style>
