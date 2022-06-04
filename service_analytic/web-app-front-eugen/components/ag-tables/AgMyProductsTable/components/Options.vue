<template>
    <div class="options">
        <ProductsListOptionsItem
            v-for="(nomenclature, index) in item.data_nomenclatures"
            :key="nomenclature.id"
            :price="item.price"
            :options="nomenclature"
            :item-id="item.id"
            :nom="item.nomenclatures[index]"
        />
    </div>
</template>

<script>
    import ProductsListOptionsItem from '~/components/common/ProductsListItem/ProductsListOptionsItem';
    export default {
        name: 'Options',
        components: {
            ProductsListOptionsItem,
        },
        computed: {
            item() {
                return this.params.data;
            },
        },
    };
</script>

<style scoped lang="scss">
    .options {
        display: grid;
        grid-column: 2 / -1;
        gap: 1rem;
        padding: 1rem;
        grid-template-columns: repeat(auto-fill, minmax(22rem, 1fr));
    }
</style>
