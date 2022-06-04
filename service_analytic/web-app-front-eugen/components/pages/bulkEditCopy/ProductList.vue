<template>
    <div ref="mainElement" class="bc-product-list">
        <div class="bc-product-list__search-field">
            <SvgIcon
                name="outlined/search"
                style="height: 16px"
                color="#C8CFD9"
                class="search-input__btn"
            />
            <input v-model="pageSetting.search" type="text" placeholder="Поиск" />
        </div>
        <table class="bc-product-list__table bc-table">
            <tbody>
                <BECProductListItem
                    v-for="(item, index) in products"
                    :key="`se-products${index}`"
                    :item="item"
                    @click="$emit('setCategory', $event)"
                ></BECProductListItem>
                <tr v-if="pageLoading">
                    <td colspan="100%" class="text-center pt-4 pb-4">
                        <v-progress-circular
                            indeterminate
                            size="32"
                            color="primary"
                        ></v-progress-circular>
                    </td>
                </tr>
                <tr v-if="!pageLoading && !products.length">
                    <td colspan="100%" class="text-center pt-4 pb-4">
                        Не найдено ни одного товара
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    /* eslint-disable */
    import { mapGetters } from 'vuex';
    import _l from 'lodash';
    import lazyLoading from '~/assets/js/mixins/lazyLoadingPag.mixin';

    export default {
        mixins: [lazyLoading],
        data() {
            return {
                selectedCategory: undefined,
                products: [],
            };
        },
        computed: {
            ...mapGetters(['isSelectedMp', 'isSelMpIndex']),
        },
        watch: {
            'pageSetting.search': {
                handler() {
                    this.debounceSearch();
                },
            },
        },
        async mounted() {
            this.debounceSearch = _l.debounce(this.searchByMatch, 500);
            this.defRequest = this.getProduct;
            await this.getProduct();
        },
        methods: {
            async getProduct() {
                const {
                    getProductsByCategoryWb: wb,
                    getProductsByCategoryOzon: ozon,
                    isSelMpIndex: index,
                } = this;
                await [ozon, wb][index]();
            },
            async getProductsByCategoryWb() {
                const getProductData = data => {
                    if (this.isSelectedMp.id == 2) {
                        const { data: products, last_page } = data;
                        return { products, last_page };
                    }

                    const {
                        data: { items, last_page },
                    } = data;
                    return { products: items, last_page };
                };
                this.pageLoading = true;

                const pageSettings = this.pageSetting;

                let topic = 'api/vp/v2/wildberries/products';
                const params = new URLSearchParams();

                for (const key in pageSettings) {
                    const item = pageSettings[key];
                    if (item) {
                        params.append(_l.snakeCase(key), item);
                    }
                }

                topic = `${topic}?${params}`;

                try {
                    const { data } = await this.$axios.get(topic);
                    const { products, last_page: lastPage } = getProductData(data);

                    this.lastPage = lastPage;
                    this.products = [...this.products, ...products];
                } catch (error) {
                    // TODO: Написать обработчик ошибок
                    console.error(error);
                } finally {
                    this.pageLoading = false;
                }
            },
            async getProductsByCategoryOzon() {
                this.pageLoading = true;
                const topic = '/api/vp/v2/ozon/product/list-with-feature';
                const {
                    pageSetting: { per_page, search, page },
                } = this;

                const params = {
                    'query[filter][category_id]': this.category,
                    'query[order][id]': 'asc',
                    'query[with_category]': 1,
                    per_page,
                    page,
                };

                search && (params['query[search]'] = search);

                try {
                    const {
                        data: {
                            data: { data: products },
                            last_page: lastPage,
                        },
                    } = await this.$axios.get(topic, {
                        params,
                    });

                    this.lastPage = lastPage;
                    this.products = [...this.products, ...products];
                } catch (error) {
                    // TODO: Написать обработчик ошибок
                    console.error(error);
                } finally {
                    this.pageLoading = false;
                }
            },
            searchByMatch() {
                this.pageSetting.page = 1;
                this.products = [];
                this.getProduct();
            },
        },
    };
</script>
