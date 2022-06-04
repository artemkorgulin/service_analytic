<template>
    <div class="product">
        <NuxtLink :to="link" class="product_link">
            <img :src="img" alt="" class="product_img" />
        </NuxtLink>
    </div>
</template>

<script>
    export default {
        name: 'Img',
        computed: {
            link() {
                return 'product/' + this.params.data.id;
            },
            img() {
                return this.params.value;
            },
        },
    };
</script>

<style scoped lang="scss">
    .product {
        position: relative;
        display: flex;
        justify-content: center;
        flex-direction: column;
        max-width: 40px;
        min-height: 40px;
        max-height: 100%;
        text-align: center;
        cursor: pointer;

        &_link {
            display: block;
            width: 100%;
            min-width: 40px;
            height: 100%;
            padding: 4px;
            border-radius: 4px;
        }

        &_img {
            display: flex;
            max-width: 40px;
            max-height: 40px;
        }
    }
</style>
