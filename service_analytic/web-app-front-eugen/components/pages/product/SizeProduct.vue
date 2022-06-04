<template>
    <div :class="$style.SizeProduct">
        <span :class="$style.SizesProductHeader">Размеры</span>
        <SizeProductItem
            v-for="(variation, index) in variations"
            :key="index"
            :ref="'sizeProductItem-' + index"
            :values="variation"
            :index="index"
            :barcode-field-disabled="variation.barcode !== undefined"
        />
    </div>
</template>

<script>
    export default {
        name: 'SizeProduct',
        props: {
            variations: {
                require: true,
                type: Array,
                default: () => [],
            },
        },
    };
</script>

<style lang="scss" module>
    .SizeProduct {
        .SizesProductHeader {
            display: inline-block;
            margin-bottom: 16px;
            font-size: 1rem;
            font-weight: 500;
            line-height: 1;
        }
    }
</style>
