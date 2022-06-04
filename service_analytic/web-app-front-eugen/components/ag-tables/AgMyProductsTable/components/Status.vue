<template>
    <div class="status_container">
        <StockIcon
            v-if="isSelectedMp.id === 2"
            :status="isProductStockAvailable ? 'success' : 'fail'"
            :icon="productStockAvailabilityParams.icon"
            :message="productStockAvailabilityParams.text"
        />
        <CheckIcon
            v-if="isSelectedMp.id !== 2 && statusId !== 4"
            :time="webCategoryParsedAt"
            :status="statusId"
        />
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';
    import StockIcon from '~/components/ag-tables/AgMyProductsTable/components/StockIcon';
    import CheckIcon from '~/components/ag-tables/AgMyProductsTable/components/CheckIcon';

    export default {
        name: 'Status',
        components: {
            StockIcon,
            CheckIcon,
        },
        computed: {
            ...mapGetters(['isSelectedMp']),
            quantity() {
                return this.params.data.quantity;
            },
            nomenclatures() {
                return this.params.data.nomenclatures;
            },
            webCategoryParsedAt() {
                return this.params.data.web_category_parsed_at;
            },
            statusId() {
                return Number(this.params.data.status_id);
            },
            isProductStockAvailable() {
                if (this.isSelectedMp.id === 2) {
                    if (this.quantity) {
                        return true;
                    }

                    if (!this.nomenclatures) {
                        return false;
                    }

                    return this.nomenclatures.reduce((acc, current) => {
                        if (current.quantity > 0) {
                            acc = true;
                        }
                        return acc;
                    }, false);
                } else {
                    return this.quantity > 0 || false;
                }
            },
            productStockAvailabilityParams() {
                if (this.isProductStockAvailable) {
                    return {
                        text: 'Продаётся',
                        icon: 'outlined/stockAvailable',
                    };
                }
                return {
                    text: 'Нет на складе',
                    icon: 'outlined/stockNotAvailable',
                };
            },
        },
    };
</script>

<style scoped lang="scss">
    .status_container {
        display: grid;
        align-items: center;
        justify-items: start;
        height: 40px;
        gap: 7px;
        grid-template-columns: repeat(3, 1fr);
    }
</style>
