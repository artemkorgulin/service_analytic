<template>
    <div :class="[$style.StatisticsDynamic, directionDown ? $style.down : $style.up]">
        <div :class="$style.numbersItemDynamicValue">{{ value | splitThousands }}</div>
        <div :class="$style.numbersItemDynamicArrow">
            <SvgIcon :name="directionDown ? 'outlined/downtrending' : 'outlined/uptrending'" />
        </div>
    </div>
</template>

<script>
    export default {
        name: 'VTranding',
        props: {
            value: {
                type: String,
                default: '',
            },
            directionDown: {
                type: Boolean,
                default: false,
            },
        },
    };
</script>

<style lang="scss" module>
    .StatisticsDynamic {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 2.4rem;
        padding-right: 0.8rem;
        padding-left: 0.8rem;
        border-radius: 0.4rem;
        flex-direction: row;

        &.down {
            background-color: rgba(255, 11, 153, 0.08);
            color: $error;
        }

        &.up {
            background-color: rgba(32, 194, 116, 0.08);
            color: $success;
        }
    }

    .numbersItemDynamicValue {
        font-weight: bold;
        font-size: 1.2rem;
        line-height: 1.6rem;
    }

    .numbersItemDynamicArrow {
        display: inline-flex;
        width: 1.6rem;
        margin-left: 0.6rem;
    }
</style>
