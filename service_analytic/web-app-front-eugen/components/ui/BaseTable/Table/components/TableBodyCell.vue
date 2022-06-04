<template>
    <td>
        <slot />
    </td>
</template>

<script>
    export default {
        name: 'TableBodyCell',
    };
</script>

<style scoped lang="scss">
    td {
        padding: 14px 16px;
        text-align: left;
        white-space: nowrap;

        &:first-child {
            position: sticky;
            left: 0;
            width: 290px;
            background: $white;

            &:before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                height: 100%;
                border-left: 1px solid $border-color;
            }

            &:after {
                content: '';
                position: absolute;
                top: 0;
                right: 0;
                height: 100%;
                border-right: 1px solid $border-color;
            }

            div {
                display: flex;
                align-items: center;
                justify-content: space-between;

                span {
                    margin-left: 12px;
                }
            }
        }
    }
</style>
