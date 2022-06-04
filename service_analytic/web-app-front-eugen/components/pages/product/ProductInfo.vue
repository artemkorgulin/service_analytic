<template>
    <div class="product-info product-content__panel product-content__panel--base-data">
        <div class="product-info-inner">
            <ProductAvailability
                :sku="sku"
                :url="url"
                :quantity="quantity"
                :product-stock-availability-params="productStockAvailabilityParams"
            />
            <!-- <ProductPriceMonitoring
            /> -->
        </div>
        <ProductOptimization :optimization="optimization"></ProductOptimization>
    </div>
</template>

<script>
    export default {
        props: {
            sku: {
                type: [Object, String],
                default: () => ({}),
            },
            url: {
                type: String,
                default: '',
            },
            quantity: {
                type: [Object, String, Number],
                default: () => ({}),
            },
            optimization: {
                type: Number,
                default: 0,
            },
            productStockAvailabilityParams: {
                type: Object,
                default: () => ({}),
            },
        },
        data() {
            return {
                isMonitoring: false,
            };
        },
    };
</script>

<style lang="scss" scoped>
    .product-info {
        display: flex;
        align-content: space-between;
        flex-wrap: wrap;
        padding-bottom: 20px;
    }

    .product-info-inner {
        display: flex;
        gap: 16px;
        width: 100%;
    }
</style>
