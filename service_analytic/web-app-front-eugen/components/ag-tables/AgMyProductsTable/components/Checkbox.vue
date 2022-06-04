<template>
    <div>
        <BaseCheckbox :value="selectedProducts.includes(params.value)" @click="click" />
    </div>
</template>

<script>
    import { mapState, mapMutations } from 'vuex';
    export default {
        computed: {
            ...mapState('products', ['selectedProducts']),
        },
        methods: {
            ...mapMutations('products', ['addRemSelectedProducts']),
            click() {
                this.addRemSelectedProducts(this.params.value);
            },
        },
    };
</script>
