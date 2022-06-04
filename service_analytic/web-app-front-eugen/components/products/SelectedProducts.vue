<template>
    <div class="selected-products">
        <div class="selected-products__filters">
            <v-select
                v-model="selBrand"
                outlined
                dense
                clearable
                hide-details
                offset-y
                label="Фильтр по брендам"
                background-color="#f9f9f9"
                :menu-props="{ nudgeBottom: 42 }"
                class="light-outline"
                :items="selectedBrands"
            ></v-select>
        </div>
        <div
            v-for="item in filteredProducts"
            :key="item.id"
            class="selected-products__item d-flex align-center"
        >
            <div class="sub-item__img ml-1 mr-5">
                <VImg
                    :src="item.image || 'https://i.stack.imgur.com/GNhxO.png'"
                    lazy-src="https://i.stack.imgur.com/GNhxO.png"
                    max-width="30"
                    aspect-ratio="1"
                    style="width: 100px"
                />
            </div>
            <span class="selected-products__title" style="flex: auto">
                {{ item.title }}
            </span>
            <VBtn icon @click="$emit('delProd', item)">
                <SvgIcon name="outlined/close" style="width: 18px" />
            </VBtn>
        </div>
        <div v-if="!selectedProducts.length" class="no-sel-prod">Не выбрано ни одного продукта</div>
    </div>
</template>

<script>
    export default {
        props: {
            search: {
                type: String,
                default: '',
            },
            selectedProducts: {
                type: Array,
                default: () => [],
            },
        },
        data() {
            return {
                selBrand: null,
            };
        },
        computed: {
            selectedBrands() {
                const brandList = [];

                this.selectedProducts.forEach(item => {
                    if (!brandList.includes(item.brand)) {
                        brandList.push(item.brand);
                    }
                });
                return brandList;
            },
            filteredProducts() {
                return this.selectedProducts.filter(item => {
                    const re = new RegExp(this.search.toLowerCase());
                    const bySearchField = re.test(
                        (item.title + item.brand + item.barcode).toLowerCase()
                    );
                    const byBrand = this.selBrand ? item.brand === this.selBrand : true;
                    return bySearchField && byBrand;
                });
            },
        },
    };
</script>

<style lang="scss" scoped>
    .selected-products {
        font-size: 14px;

        &__item {
            padding: 10px 16px;
            font-weight: 500;
        }

        &__filters {
            padding: 8px 16px;
        }
    }

    .no-sel-prod {
        width: 100%;
        padding: 10px 16px;
        text-align: center;
    }
</style>
