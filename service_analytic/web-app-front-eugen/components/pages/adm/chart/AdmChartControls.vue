<template>
    <VItemGroup v-model="selectedChartFields" :class="$style.AdmChartControls" multiple mandatory>
        <VItem
            v-for="item of items"
            :key="'chart-control-' + item.slug"
            v-slot="{ active, toggle }"
        >
            <div v-ripple :class="[$style.item, active && $style.active]" @click="toggle">
                <div :class="$style.color" :style="{ backgroundColor: item.color }" />
                <div>
                    <div :class="$style.key">
                        {{ item.name }}
                    </div>
                    <div :class="$style.value">
                        {{ item.text }}
                    </div>
                </div>
            </div>
        </VItem>
    </VItemGroup>
</template>

<script>
    export default {
        name: 'AdmChartControls',
        props: {
            value: {
                type: Array,
                default: () => [],
            },
            items: {
                type: Array,
                default: () => [],
            },
        },
        computed: {
            selectedChartFields: {
                get() {
                    return this.value;
                },
                set(val) {
                    return this.$emit('input', val);
                },
            },
        },
    };
</script>

<style lang="scss" module>
    .AdmChartControls {
        display: flex;
        gap: 16px;
        justify-content: space-around;
        padding-top: 8px;

        @include md {
            overflow-y: auto;
            justify-content: unset;
            padding-top: 0;
            gap: 0;
        }
    }

    .color {
        width: 8px;
        min-width: 8px;
        height: 100%;
        min-height: 32px;
        border-radius: 2px;

        @include md {
            width: 3px;
            min-width: 3px;
            border-radius: 0;
        }
    }

    .item {
        display: flex;
        flex: 1 1 auto;
        padding: 8px;
        border-radius: 8px;
        gap: 8px;
        user-select: none;

        @extend %text-caption;

        opacity: 0.4;
        transition: $primary-transition;

        &.active {
            opacity: 1;
        }

        @include md {
            padding: 16px;
            border-radius: 0;
        }
    }

    .key {
        color: $base-900;
        font-weight: bold;

        @include md {
            // margin-bottom: 2px;
            font-size: 12px;
            line-height: 16px;
            color: $base-800;
            font-weight: normal;
        }
    }

    .value {
        @include md {
            font-size: 16px;
            line-height: 22px;
            color: $base-900;
            font-weight: 500;
        }
    }
</style>
