<template>
    <th>
        <slot />
    </th>
</template>

<script>
    export default {
        name: 'TableHeadCell',
    };
</script>

<style scoped lang="scss">
    th {
        padding: 12px 16px;
        text-align: left;
        white-space: nowrap;
        font-weight: 500;
        font-size: 12px;
        cursor: default;

        &:not(:first-child) {
            background-color: $border-color;
        }

        &:first-child {
            position: sticky;
            top: 0;
            left: 0;
            width: 290px;
            background: $white;
            color: $base-800;
            cursor: default;

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
                justify-content: flex-start;

                span {
                    margin-left: 12px;
                }
            }
        }

        &:last-child {
            border-right: 1px solid $border-color;
        }
    }
</style>
