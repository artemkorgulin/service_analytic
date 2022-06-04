<template>
    <div class="multilegend">
        <template v-for="(chart, j) in items">
            <div v-for="(line, i) in chart" :key="`${i}${j}`" class="multilegend-item" @click="$emit('itemClick', {
                chartIndex: j,
                innerIndex: i,
                value: line.active,
                })">
                <div class="multilegend-item__circle" :style="{ backgroundColor: line.color }"/>
                <div class="multilegend-item__text" :class="{ 'multilegend-item__text_crossed': !line.active }" v-html="line.label"/>
            </div>
        </template>

    </div>
</template>

<script>
    export default {
        name: 'MultipleChartLegend',
        props: {
            items: {
                type: Array,
                required: true,
            }
        }
    };
</script>

<style lang="scss" scoped>
.multilegend {
    display: flex;
    flex-wrap: wrap;
    gap: 8px 30px;
    padding: 16px 0 0;

    &-item {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 8px;
        cursor: pointer;

        &__circle {
            width: 12px;
            min-width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        &__text {
            font-weight: 500;
            font-size: 12px;
            line-height: 16px;
            color: $base-800;

            &.multilegend-item__text_crossed {
                text-decoration: line-through;
            }
        }
     }
}
</style>
