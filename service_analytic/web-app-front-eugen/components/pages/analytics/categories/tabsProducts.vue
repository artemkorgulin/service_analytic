<template>
    <div class="tabs-products">
        <v-menu
            v-model="modalState"
            :position-x="modalCoords.left"
            :position-y="modalCoords.top"
            absolute
            offset-y
        >
            <v-card>
                <img :src="imgSrc" alt="Изображение товара" />
            </v-card>
        </v-menu>
        <div class="tabs-products__control"></div>
        <div class="tabs-products__loader">
            <div v-if="isLoadProducts" class="tabs-products__loader-circular">
                <VProgressCircular indeterminate size="50" color="accent" />
            </div>
            <SeTableAG
                pagination
                :columns="columnDefs"
                :rows="productsList"
                :page-size="100"
                :total="Number(lastRow)"
                @mouseover="cellMouseOver"
                @mouseout="cellMouseOut"
                @dataChanged="dataChanged"
            />
        </div>
    </div>
</template>
<script>
    import SeTableAG from '~/components/ui/SeTableAG.vue';
    import mouseOverOutAgGrid from '~/assets/js/mixins/mouseOverOutAgGrid';
    import { mapActions, mapState, mapGetters, mapMutations } from 'vuex';

    export default {
        /* eslint-disable */
        components: {
            SeTableAG,
        },
        mixins: [mouseOverOutAgGrid],
        data: () => ({
            modalState: false,
            imgSrc: null,
            colSalesKeys: ['graph_sales', 'graph_category_count', 'graph_price', 'graph_stock'],
            colSort: [
                'sku',
                'brand',
                'name',
                'category',
                'color',
                'position',
                'supplier_name',
                'rating',
                'comments_count',
                'last_price',
                'min_price',
                'max_price',
                'base_price',
                'base_sale',
                'promo_sale',
            ],
            colGroupKeys: ['name'],
            colHide: ['subject_id', 'web_id', 'sales_in_stock_avg'],
            colLink: ['sku'],
        }),
        computed: {
            ...mapState('categories-analitik', [
                'products',
                'pageProducts',
                'dataProducts',
                'productsColums',
                'isLoadProducts',
            ]),
            ...mapGetters('categories-analitik', ['productsList']),

            lastRow() {
                return this.dataProducts?.total || 0;
            },

            columnDefs() {
                const typesFilter = ['agTextColumnFilter', 'agNumberColumnFilter'];
                const numberFilter = [
                    'revenue',
                    'position',
                    'avg',
                    'stock',
                    'rating',
                    'last_price',
                    'days_in_stock',
                    'total_sales',
                    'base_sale',
                ];
                return this.productsColums.map(col => {
                    const column = {
                        ...col,
                        hide: this.colHide.includes(col.field),
                        sortable: this.colSort.includes(col.field),
                        filter: typesFilter[Number(numberFilter.includes(col.field))],
                    };
                    if (col.headerName === 'Фото') {
                        return {
                            ...col,
                            order: 1,
                            maxWidth: 80,
                            cellRenderer: params =>
                                `<div style="height: 100%; display: flex; align-items: center;">
                                    <img src="${params.value}" style="width: 32px; height: 32px; object-fit: contain"/>
                                </div>`,
                        };
                    } else if (this.colSalesKeys.includes(col.field)) {
                        return {
                            ...col,
                            cellRenderer: 'agSparklineCellRenderer',
                            cellRendererParams: {
                                sparklineOptions: {
                                    type: 'column',
                                    fill: '#5DDC98',
                                    stroke: '#5DDC98',
                                    highlightStyle: { fill: '#FAC858' },
                                },
                            },
                        };
                    } else if (this.colLink.includes(col.field)) {
                        return {
                            ...column,
                            cellRenderer: function (params) {
                                if (!params.data) {
                                    return '';
                                }
                                return `<a class="tabs-product__table-link" href="${
                                    params.data[col.field].link
                                }" target="_blank">${params.data[col.field].value}</a>`;
                            },
                        };
                    }
                    return column;
                });
            },
        },
        methods: {
            ...mapActions('categories-analitik', ['loadAnalyticsProductsWildberries']),
            ...mapMutations('categories-analitik', [
                'setPageProducts',
                'setFiltresProductsFilter',
                'setFiltresProductsSort',
                'resetParams',
            ]),

            async dataChanged(page, sortModel, filterModel) {
                if (sortModel.length > 0) {
                    this.setFiltresProductsSort({
                        'sort[col]': sortModel[0].colId,
                        'sort[sort]': sortModel[0].sort,
                    });
                } else {
                    this.setFiltresProductsSort({});
                }

                if (page && page !== null && page != this.pageProducts) {
                    this.setPageProducts(page);
                }

                const dataFilter = {};
                if (Object.keys(filterModel).length) {
                    const key = Object.keys(filterModel)[0];
                    const { filterType } = filterModel[key];
                    dataFilter[`filters[${key}][filterType]`] = filterType;
                    if (Object.prototype.hasOwnProperty.call(filterModel[key], 'condition1')) {
                        for (const filter in filterModel[key]['condition1']) {
                            if (filter !== 'filterType') {
                                dataFilter[`filters[${key}][condition1][${filter}]`] =
                                    filterModel[key]['condition1'][filter];
                            }
                        }
                        dataFilter[`filters[${key}][operator]`] = filterModel[key]['operator'];
                        for (const filter in filterModel[key]['condition2']) {
                            if (filter !== 'filterType') {
                                dataFilter[`filters[${key}][condition2][${filter}]`] =
                                    filterModel[key]['condition2'][filter];
                            }
                        }
                    } else {
                        const { filter, type } = filterModel[key];
                        dataFilter[`filters[${key}][filter]`] = filter;
                        dataFilter[`filters[${key}][type]`] = type;
                    }
                    this.setFiltresProductsFilter(dataFilter);
                } else {
                    this.setFiltresProductsFilter({});
                }
                this.loadAnalyticsProductsWildberries();
            },
        },
    };
</script>
<style lang="scss" scoped>
    .tabs-products {
        display: block;
        padding: 16px;

        &__loader {
            position: relative;

            &-circular {
                position: absolute;
                top: 0;
                left: 0;
                z-index: 8;
                display: flex;
                align-items: center;
                justify-content: center;
                width: 100%;
                height: 100%;
                background: rgba(255, 255, 255, 0.4);
            }
        }

        &__more-info {
            display: flex;
            justify-content: flex-end;
            padding-top: 16px;

            &-title {
                font-size: 12px;
                font-style: normal;
                font-weight: normal;
                line-height: 16px;
                color: #7e8793;
            }

            &-value {
                padding: 0 10px 0 4px;
                font-size: 12px;
                font-style: normal;
                font-weight: bold;
                line-height: 16px;
                color: #2f3640;
            }
        }
    }
</style>
