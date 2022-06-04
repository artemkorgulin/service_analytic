<template>
    <div class="product-table__wrap prod">
        <div class="ob-0122-header-product" :class="$style.TableWrapper" style="width: 100%">
            <ProductItem filter />
        </div>
        <div :class="$style.TableWrapper">
            <template v-for="(item, index) in items">
                <ProductItem :key="item.id" :product="item" :last="index + 1 === items.length" />
            </template>
        </div>
    </div>
</template>

<script>
    import productsMixin from '~mixins/products.mixin';
    import ProductItem from '~/components/pages/products/List/ProductItem';

    export default {
        name: 'ProductsList',
        components: {
            ProductItem,
        },
        mixins: [productsMixin],
        props: {
            items: {
                type: Array,
                required: true,
                default: () => [],
            },
        },
    };
</script>

<style lang="scss" module>
    .TableWrapper {
        display: grid;
    }
</style>
