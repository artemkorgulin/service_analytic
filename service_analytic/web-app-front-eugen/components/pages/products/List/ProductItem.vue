<template>
    <div
        :class="[
            $style.ProductItem,
            $style.ProductItemSmall,
            productHasOptions ? [$style.ProductItemHasOptions] : [$style.ProductItemNoOptions],
            { 'header-table-product': filter },
            { 'last-product-list': last },
        ]"
    >
        <div :class="$style.SelectExpandWrapper">
            <SelectOrExpand
                v-if="!filter"
                :id="item.id"
                :expanded="expanded"
                @switch-expanded="expanded = !expanded"
            />
            <SelectOrExpand
                v-else
                filter
                :select-all="isSelectAll"
                :expanded="false"
                @click="selectAll = !selectAll"
            />
        </div>
        <div :class="[$style.ProductInfo]">
            <div :class="[$style.ProductInfoBox, filter && $style.header]">
                <div :class="[$style.ProductInfoColumn, $style.ProductInfoColumnImg]">
                    <ProductsListProductImg
                        v-if="!filter"
                        :link="'product/' + item.id"
                        :img="item.photo_url"
                        :variants-count="item.nomenclatures | calcCountNomenclatures"
                        show-number
                    />
                    <div v-else class="name-filter">
                        <span class="name-filter__title">Фото</span>
                    </div>
                </div>
                <div :class="[$style.ProductInfoColumn, $style.ProductInfoColumnName]">
                    <template v-if="!filter">
                        <NuxtLink class="txt-link" :to="'product/' + item.id">
                            <span
                                class="small-txt small-txt--medium"
                                :class="$style.ProductInfoDescriptionColumn"
                            >
                                {{ getProductName }}
                            </span>
                        </NuxtLink>
                    </template>
                    <div v-else class="name-filter filter pointer">
                        <span class="name-filter__title">Название</span>
                        <SeSortArrow
                            name="Название"
                            :name-sort-field="nameSortField"
                            :asc-sort="ascSort"
                        ></SeSortArrow>
                    </div>
                </div>

                <div :class="[$style.ProductInfoColumn, $style.ProductInfoColumnBrand]">
                    <span v-if="!filter" :class="$style.BrandName">{{ item.brand }}</span>
                    <div v-else class="name-filter">
                        <span class="name-filter__title">Бренд</span>
                    </div>
                </div>
                <div :class="[$style.ProductInfoColumn, $style.ProductInfoColumnPrice]">
                    <ProductsListPrice
                        v-if="!filter"
                        :item="item"
                        :range="productHasOptions ? item.price_range : false"
                    />
                    <div v-else class="name-filter filter pointer">
                        <span class="name-filter__title">Цена</span>
                        <SeSortArrow
                            name="Цена"
                            :name-sort-field="nameSortField"
                            :asc-sort="ascSort"
                        ></SeSortArrow>
                    </div>
                </div>
                <div :class="[$style.ProductInfoColumn, $style.ProductInfoColumnProgress]">
                    <ProductsListProgress
                        v-if="!filter"
                        :class="$style.ProgressBar"
                        :optimization="item.optimization"
                    />
                    <div v-else class="name-filter filter pointer">
                        <span class="name-filter__title">Оптимизация</span>
                        <SeSortArrow
                            name="Оптимизация"
                            :name-sort-field="nameSortField"
                            :asc-sort="ascSort"
                        ></SeSortArrow>
                    </div>
                </div>

                <div :class="[$style.ProductInfoColumn, $style.ProductInfoColumnInfo]">
                    <div :class="$style.ProductInfoRow">
                        <ProductsListRating
                            v-if="!filter"
                            :rating="item.rating"
                            :length="displayOptionBig ? 5 : 1"
                        />
                        <div v-else class="name-filter filter pointer">
                            <span class="name-filter__title">Рейтинг</span>
                            <SeSortArrow
                                name="Рейтинг"
                                :name-sort-field="nameSortField"
                                :asc-sort="ascSort"
                            ></SeSortArrow>
                        </div>
                    </div>
                </div>

                <div
                    v-if="!displayOptionBig && !productHasOptions"
                    :class="[$style.ProductInfoColumn, $style.ProductInfoColumnLink]"
                >
                    <ProductsListUrl
                        v-if="!filter && !productHasOptions"
                        :url="item.product_ozon_url"
                        :sku="item.sku"
                    />
                    <div v-else class="name-filter">
                        <span class="name-filter__title">Артикул</span>
                    </div>
                </div>

                <div :class="[$style.ProductInfoColumn, $style.ProductInfoColumnControls]">
                    <div :class="$style.ProductInfoControlsGroup">
                        <template v-if="!filter">
                            <ProductsListIconWithTooltip
                                :status="isProductStockAvailabile ? 'success' : 'fail'"
                                :icon="productStockAvailabilityParams.icon"
                                :message="productStockAvailabilityParams.text"
                            />

                            <ProductsListIconCheck
                                v-if="isSelectedMp.id !== 2 && item.status_id != 4"
                                :time="item.web_category_parsed_at"
                                :status="item.status_id"
                            />

                            <ProductsListIconNotifications
                                :status="2"
                                :pending="item.status_id === 2"
                            />
                        </template>
                        <div v-else class="name-filter filter pointer">
                            <span class="name-filter__title">Статус</span>
                            <SeSortArrow
                                name="Статус"
                                :name-sort-field="nameSortField"
                                :asc-sort="ascSort"
                            ></SeSortArrow>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <VFadeTransition mode="out-in" appear>
            <div
                v-if="!filter && productHasOptions && expanded"
                :class="[
                    $style.ProductOptions,
                    displayOptionBig ? [$style.ProductOptionsBig] : [$style.ProductOptionsSmall],
                ]"
            >
                <ProductsListOptionsItem
                    v-for="(nomenclature, index) in item.data.nomenclatures"
                    :key="nomenclature.id"
                    :price="item.price"
                    :options="nomenclature"
                    :item-id="item.id"
                    :nom="item.nomenclatures[index]"
                />
            </div>
        </VFadeTransition>
    </div>
</template>

<script>
    import SeSortArrow from '~/components/ui/SeSortArrow';
    import { mapGetters, mapActions, mapState } from 'vuex';
    import productsMixin from '~mixins/products.mixin';
    import { debounce } from '~utils/helper.utils';

    export default {
        name: 'ProductItem',
        components: { SeSortArrow },
        filters: {
            calcCountNomenclatures(val) {
                if (!val) {
                    return 0;
                }
                return val.reduce((acc, current) => {
                    if (!current.variations) {
                        return 0;
                    }
                    acc += current.variations.length;
                    return acc;
                }, 0);
            },
        },
        mixins: [productsMixin],
        props: {
            filter: Boolean,
            last: Boolean,
            product: {
                type: Object,
                default: () => ({}),
            },
        },
        data() {
            return {
                // nameSortField: '',
                ascSort: false,
                headerItems: undefined,
                sortCounter: 1,
                lists: {
                    ozon: [
                        {
                            name: 'Название',
                            value: 'name',
                            class: 'name',
                        },
                        {
                            name: 'Цена',
                            value: 'price',
                            class: 'price',
                        },
                        {
                            name: 'Оптимизация',
                            value: 'optimization',
                            class: 'optimization',
                        },
                        {
                            name: 'Рейтинг',
                            value: 'rating',
                            class: 'rating',
                        },
                        {
                            name: 'Статус',
                            value: 'status_id',
                            class: 'status',
                        },
                    ],
                    wildberries: [
                        {
                            name: 'Название',
                            value: 'name',
                            class: 'name',
                        },
                        {
                            name: 'Рейтинг',
                            value: 'rating',
                            notSort: true,
                        },
                        {
                            name: 'Цена',
                            value: 'price',
                            class: 'price',
                        },
                        {
                            name: 'Оптимизация',
                            value: 'characteristics',
                            class: 'optimization',
                        },
                        {
                            name: 'Статус',
                            value: 'status_id',
                            class: 'status',
                        },
                    ],
                },
                item: {},
                filters: productsMixin.filters,
                expanded: false,
                colors: {
                    primaryPurple: '#710bff',
                    black: '#2f3640',
                },
            };
        },
        computed: {
            ...mapGetters(['isSelectedMp']),
            ...mapGetters({
                displayOptionBig: 'products/GET_DISPLAY_OPTION',
                productHasOptions: 'isProductHasOptions',
                marketplaceSlug: 'getSelectedMarketplaceSlug',
                isSelectAll: 'products/IS_SELECT_ALL',
            }),
            ...mapState('products', ['nameSortField', 'sortTypeCounter']),
            selectAll: {
                get() {
                    return this.isSelectAll;
                },
                set(value) {
                    this.selectAllProducts();
                },
            },
            getProductName() {
                try {
                    const name = this.isSelectedMp.id === 2 ? this.item.title : this.item.name;
                    return name.length > 50 ? `${name.slice(0, 50)}...` : name;
                } catch {
                    return '';
                }
            },
            isProductStockAvailabile() {
                if (this.isSelectedMp.id === 2) {
                    if (this.product.quantity) {
                        return true;
                    }

                    if (!this.product.nomenclatures) {
                        return false;
                    }

                    return this.product.nomenclatures.reduce((acc, current) => {
                        if (current.quantity > 0) {
                            acc = true;
                        }
                        return acc;
                    }, false);
                } else {
                    return this.product.quantity > 0 || false;
                }
            },
            productStockAvailabilityParams() {
                if (this.isProductStockAvailabile) {
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
        watch: {
            product(val) {
                this.item = val;
            },
        },
        created() {
            this.item = this.product;
        },
        mounted() {
            if (this.filter) {
                this.headerItems = Array.from(this.$el.getElementsByClassName('name-filter'));

                this.headerItems.forEach(el => {
                    el.addEventListener('click', this.clickFilter);
                });
            }
        },
        methods: {
            ...mapActions({
                selectAllProducts: 'products/SELECT_ALL_PRODUCTS',
            }),
            clickFilter(e) {
                const nameField = e.currentTarget.querySelector('.name-filter__title').textContent;
                const { key } = this.isSelectedMp;
                const list = this.lists[key];
                const field = list.find(({ name }) => name === nameField);

                if (this.sortCounter > 3) {
                    this.nameSortField = '';
                    this.sortCounter = 1;
                }

                this.$store.commit('products/setField', {
                    field: 'sortTypeCounter',
                    value: this.sortCounter++,
                });

                if (!field) {
                    return '';
                }
                this.ascSort = !this.ascSort;

                this.$store.commit('products/setField', {
                    field: 'nameSortField',
                    value: nameField,
                });

                this.$store.commit('products/setField', {
                    field: 'sortLoading',
                    value: true,
                });

                this.$store.commit('products/SET_SORT', {
                    sortType: this.ascSort ? 'asc' : 'desc',
                    sortBy: field.value,
                });

                this.$store.commit('products/setField', {
                    field: 'sortLoading',
                    value: false,
                });

                debounce({
                    time: 250,
                    collBack: () => {
                        this.$store.dispatch('products/LOAD_PRODUCTS', {
                            type: 'sort',
                            reload: false,
                            marketplace: this.marketplaceSlug,
                        });
                    },
                });
            },
        },
    };
</script>

<style lang="scss" scoped>
    .last-product-list {
        border-bottom: none !important;
    }

    .name-filter {
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: 100%;

        &.filter {
            min-height: 2.5rem;
            padding: 0 8px;
            border-radius: 4px;
            transition: background-color ease-in 200ms;

            &:hover {
                background-color: #f6f8fa;
            }
        }
    }

    .pointer {
        cursor: pointer;
    }

    .header-table-product {
        height: 48px !important;
        background: $base-400;
        font-size: 12px !important;

        & .name-filter {
            background: $base-400;
        }
    }
</style>

<style lang="scss" module>
    .ProductItem {
        display: inline-grid;
        grid-template-columns: 2rem auto;
        padding: 0.5rem 1rem 0.5rem 0.5rem;
        border-bottom: 1px solid $color-gray-light;
        background-color: $white;
        column-gap: 1rem;

        &.ProductItemSmall {
            padding: 0.25rem;
            font-size: 14px;

            & .ProductInfo {
                padding: 0;
            }

            & .ProductInfoBox {
                align-items: center;
            }

            & .ProductInfoColumnProgress {
                // align-items: center;
                // justify-content: center;
                width: 10%;
            }

            & .ProductInfoDescription {
                -webkit-line-clamp: 2;
            }

            & .ProductInfoColumnImg {
                width: 2.5rem;
                min-width: 2.5rem;
                height: 2.5rem;
            }

            & .ProductInfoColumnBrand {
                width: 5%;
                padding: 0 8px;
            }

            & .ProductInfoColumnInfo {
                width: 2.95rem;
                min-width: 2.95rem;
            }

            & .ProductInfoColumnLink {
                width: 5.75rem;
                min-width: 5.75rem;
            }

            & .ProductInfoColumnName {
                width: 30%;
            }

            & .ProductInfoColumnPrice {
                width: 14rem;

                @include respond-to(lg) {
                    width: 9%;
                }
            }

            & .ProductInfoControlsGroup {
                flex-direction: row;
                // justify-content: flex-end;
                width: 7rem;
                min-width: 7rem;
                margin-right: 7px;
                margin-left: 20px;
            }

            &.ProductItemHasOptions {
                grid-template-columns: 5rem auto;
                grid-template-rows: minmax(2.5rem, auto) auto;
            }

            &.ProductItemNoOptions {
                grid-template-columns: 2rem auto;
                grid-template-rows: minmax(2.5rem, auto);
            }
        }
    }

    .SelectExpandWrapper {
        grid-row: 1 / -1;
    }

    .ProductInfo {
        display: flex;
        flex-direction: column;
        background-color: $white;
    }

    .ProductInfoBox {
        display: flex;
        gap: 1rem;
        justify-content: space-between;

        &.header {
            background-color: $base-400;
            //gap: 0;
        }
    }

    .ProductInfoColumn {
        display: flex;
        justify-content: center;
        flex-direction: column;
        min-height: 40px;
    }

    .ProductInfoRow {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;

        &:not(:last-child) {
            margin-bottom: 0.5rem;
        }
    }

    .ProductInfoDescription {
        overflow: hidden;
        display: -webkit-box;
        font-size: 1.25rem;
        -webkit-box-orient: vertical;
        color: $color-purple-primary;
    }

    .ProductInfoDescriptionColumn {
        overflow: hidden;
        display: -webkit-box;
        font-size: 0.875rem;
        -webkit-box-orient: vertical;
        color: $color-purple-primary;
    }

    .ProductInfoControlsGroup {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.5rem;
    }

    .BrandName {
        font-size: 0.875rem;
        line-height: 1.3;
        font-weight: 500;
    }

    .ProductOptions {
        display: grid;
        grid-column: 2 / -1;
        grid-gap: 1rem;
        //min-height: 9rem;
        padding-top: 1rem;
        padding-bottom: 0.5rem;
        grid-template-columns: repeat(auto-fill, minmax(22rem, 1fr));
    }

    .ProgressBar {
        width: 100%;
    }
</style>
