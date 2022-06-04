<template>
    <div class="product-price-monitoring">
        <div class="product-price-monitoring__title">Мониторинг</div>
        <div class="product-price-monitoring__tag-wrapper">
            <VChip
                label
                color="rgba(32, 194, 116, 0.08)"
                text-color="#20c274"
                small
                style="font-weight: 700"
            >
                <SvgIcon :name="`filled/check`" style="margin-right: 8px" />
                {{ productStockAvailabilityParams.text }}
            </VChip>
        </div>
        <div class="product-price-monitoring__desc">{{ getQuantityProduct }} {{ getDeclWord }}</div>
    </div>
</template>

<script>
    import { getDeclWord } from '~/assets/js/utils/helpers';
    import { mapGetters } from 'vuex';
    export default {
        props: {
            productStockAvailabilityParams: Boolean,
        },
        computed: {
            ...mapGetters('product', ['getQuantityProduct']),
            getDeclWord() {
                return getDeclWord(['товар', 'товара', 'товаров'], this.getQuantityProduct);
            },
        },
        created() {
            console.log(1, this.getQuantityProduct);
        },
    };
</script>

<style lang="scss" scoped>
    .product-price-monitoring {
        display: flex;
        flex-grow: 1;
        flex-direction: column;
        flex-basis: 50%;
        height: 95px;
        padding: 8px 16px;
        border-radius: 8px;
        background-color: #f9f9f9;

        &__title {
            font-size: 12px;
            font-weight: 500;
            line-height: 16px;
            color: #7e8793;
        }

        &__tag-wrapper {
            display: flex;
            margin-top: 8px;
        }

        &__desc {
            margin-top: 8px;
            font-size: 12px;
            font-weight: 500;
            line-height: 16px;
            color: #7e8793;
        }
    }
</style>
