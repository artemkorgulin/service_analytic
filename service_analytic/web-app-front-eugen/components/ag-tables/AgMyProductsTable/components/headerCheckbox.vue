<template>
    <BaseCheckbox :indeterminate="Boolean(isAllSelect)" @click="handleClick" />
</template>
<script>
    import { mapState, mapMutations, mapGetters } from 'vuex';
    export default {
        computed: {
            ...mapState('products', ['selectedProducts']),
            ...mapGetters({
                products: 'products/GET_PRODUCTS',
            }),
            isAllSelect() {
                return this.selectedProducts.length;
            },
        },
        methods: {
            ...mapMutations('products', ['addRemSelectedProducts', 'setFieldArray']),
            handleClick() {
                this.setFieldArray([
                    'selectedProducts',
                    this.isAllSelect
                        ? []
                        : [...this.selectedProducts, ...this.products.items.map(item => item.id)],
                ]);
            },
        },
    };
</script>
