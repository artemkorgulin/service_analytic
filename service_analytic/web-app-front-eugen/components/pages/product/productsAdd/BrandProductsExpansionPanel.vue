<template>
    <VExpansionPanel :class="$style.BrandProductsExpansionPanel">
        <VExpansionPanelHeader>
            <div :class="$style.productLine">
                <!--                <VSimpleCheckbox-->
                <!--                    v-ripple-->
                <!--                    :value="isBrandCheckboxChecked"-->
                <!--                    :indeterminate="isBrandCheckboxIndeterminate"-->
                <!--                    color="primary"-->
                <!--                    @click="handleBrandClick(isBrandCheckboxChecked, brand.brand)"-->
                <!--                />-->
                <span :class="$style.productLineBold">{{ brand.brand }}</span>
            </div>
        </VExpansionPanelHeader>
        <VExpansionPanelContent>
            <!--            <template v-if="pending && !brand.products.length">-->
            <!--                <icon-loading class="product__sortable-icon" :pending="true" />-->
            <!--            </template>-->
            <!--            <template v-else>-->
            <div
                v-for="(product, index) in products"
                :key="product.id"
                :class="[$style.productLine, $style.productLineList]"
            >
                <VSimpleCheckbox
                    v-ripple
                    :value="productCheckboxes[index]"
                    color="primary"
                    @click="handleProductClick(product.id, brand.brand)"
                />
                <VImg :class="$style.productLineImage" contain :src="product.image" />
                <span :class="$style.productLineText">{{ product.title }}</span>
                <a :class="$style.productLineLink" :href="product.url" target="_blank">
                    <SvgIcon name="outlined/link" />
                </a>
            </div>
            <InfiniteLoading
                ref="infinityLoading"
                :style="{ width: '100%' }"
                force-use-infinite-wrapper="#scroll_area"
                @infinite="handleReachEnd"
            >
                <div slot="no-results"></div>
                <div slot="no-more"></div>
            </InfiniteLoading>
            <!--            </template>-->
        </VExpansionPanelContent>
    </VExpansionPanel>
</template>

<script>
    import { mapGetters, mapActions } from 'vuex';
    import InfiniteLoading from 'vue-infinite-loading';
    // import IconLoading from '~/components/common/IconLoading.vue';

    export default {
        name: 'BrandProductsExpansionPanel',
        // components: { IconLoading, InfiniteLoading },
        components: { InfiniteLoading },
        props: {
            brand: {
                type: Object,
                required: true,
            },
            products: {
                type: Array,
                default: () => [],
            },
            brandIndex: {
                type: Number,
                required: true,
            },
        },
        data() {
            return {
                pending: false,
                // brandCheckbox: false,
                productCheckboxes: [],
            };
        },
        computed: {
            ...mapGetters({
                productsSelected: 'products-add/getProductsSelected',
            }),
            isBrandCheckboxChecked() {
                for (let i = 0; i < this.productCheckboxes.length; i++) {
                    if (this.productCheckboxes[i] === true) {
                        return true;
                    }
                }
                return false;
            },
            isBrandCheckboxIndeterminate() {
                const firstVal = this.productCheckboxes[0];
                for (let i = 1; i < this.productCheckboxes.length; i++) {
                    if (this.productCheckboxes[i] !== firstVal) {
                        return true;
                    }
                }
                return false;
            },
            checkboxesRecalculateTrigger() {
                return JSON.stringify([this.productsSelected, this.products.length]);
            },
            isAllProductsShown() {
                return this.brand.current_page === this.brand.last_page;
            },
        },
        watch: {
            checkboxesRecalculateTrigger: {
                immediate: true,
                handler() {
                    this.getProductCheckboxes();
                },
            },
        },
        methods: {
            ...mapActions({
                fetchProducts: 'products-add/fetchProducts',
                setCheckboxBrand: 'products-add/setCheckboxBrand',
                addProductToSelected: 'products-add/addProductToSelected',
            }),
            // handlePanelExpand() {
            //     if (!this.brand.products.length) {
            //         this.pending = true;
            //         console.log('nothing');
            //         this.fetchProducts({ brand: this.brand.brand }).then(() => {
            //             this.pending = false;
            //         });
            //     }
            // },
            handleProductClick(id, brand) {
                return this.addProductToSelected({ id, brand });
            },
            handleBrandClick(val, brand) {
                console.log('handleBrandClick');
            },
            getProductCheckboxes() {
                this.productCheckboxes = this.products.map((product, index) =>
                    Boolean(this.productsSelected.find(el => el.id === product.id))
                );
            },
            async handleReachEnd($state) {
                try {
                    if (this.pending) {
                        return false;
                    }
                    await this.fetchProducts({
                        brand: this.brand.brand,
                        page: (this.brand.current_page || 0) + 1,
                    }).then(() => {
                        this.pending = false;
                        this.getProductCheckboxes();
                    });
                    if (this.isAllProductsShown) {
                        return $state.complete();
                    } else {
                        return $state.loaded();
                    }
                } catch (error) {
                    await $state.complete();
                    this.notification = await this.$notify.create({
                        message: 'Ошибка при загрузке данных',
                        timeout: 5000,
                        type: 'negative',
                    });
                }
            },
        },
    };
</script>

<style lang="scss" module>
    .BrandProductsExpansionPanel {
        & .productLine {
            display: flex;
            align-items: center;
            gap: size(8);
            font-size: size(14);
            line-height: 1.36;

            &.productLineList {
                padding: size(8) 0;
            }

            & .productLineImage {
                flex: none;
                width: size(32);
                height: size(32);
            }

            & .productLineBold {
                flex-grow: 1;
                font-weight: bold;
            }

            & .productLineText {
                flex-grow: 1;
                font-weight: 500;
            }
        }
    }
</style>
