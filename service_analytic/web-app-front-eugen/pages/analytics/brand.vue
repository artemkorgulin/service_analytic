<template>
    <section class="brand-page">
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
        <Page :title="title" :back="back" :period="period" :period-change="handleDateChange">
            <div class="brand-page_table-container">
                <div class="brand-page_table-head">
                    <div class="d-flex mb-2">
                        <ElAutocomplete
                            v-model="search"
                            class="inline-input"
                            clearable
                            :fetch-suggestions="querySearch"
                            :debounce="600"
                            placeholder="Введите бренд"
                            :trigger-on-focus="false"
                            @select="handleSelect"
                        ></ElAutocomplete>
                    </div>
                </div>
                <VTabs v-model="tabSelected" class="se-tabs">
                    <VTab
                        v-for="tab in tabs"
                        :key="tab.id"
                        v-ripple="false"
                        :disabled="tab.disabled"
                        @change="changeTab(tab.value)"
                    >
                        {{ tab.title }}
                    </VTab>
                </VTabs>
                <div class="brand-page_table-body page-loader">
                    <div
                        v-if="
                            isBrandsAnalyticsLoading ||
                            isLoading.products ||
                            isLoading.categories ||
                            isLoading.sellers
                        "
                        class="page-loader__circle"
                    >
                        <VProgressCircular
                            indeterminate
                            size="50"
                            color="accent"
                            class="brand-page_table-body__cover-loader"
                        />
                    </div>
                    <div v-if="tab === 0" class="se-analytics-tabs">
                        <div class="d-flex flex-column">
                            <SeTableAG
                                pagination
                                :page-size="pageSize"
                                :columns="productsColumns"
                                :rows="productsRows"
                                :total="productsTotal"
                                no-rows-overlay
                                :no-rows-message="noRowsMessage"
                                @mouseover="cellMouseOver"
                                @mouseout="cellMouseOut"
                                @dataChanged="dataChanged"
                            ></SeTableAG>
                        </div>
                    </div>
                    <div v-if="tab === 1" class="se-analytics-tabs">
                        <BrandCategoriesChart />
                        <SeTableAG
                            :page-size="50"
                            :columns="categoriesColumns"
                            :rows="categoriesRows"
                            no-rows-overlay
                            :no-rows-message="noRowsMessage"
                        />
                    </div>
                    <div v-if="tab === 2" class="se-analytics-tabs">
                        <BrandSellersChart />
                        <SeTableAG
                            :page-size="pageSize"
                            :columns="sellersColumns"
                            :rows="sellersRows"
                            no-rows-overlay
                            :no-rows-message="noRowsMessage"
                        />
                    </div>
                    <TabsPrices
                        v-if="tab === 3"
                        :load-data="loadBrandPrices"
                        :set-data-prices="setDataPrices"
                        :data-prices="dataPrices"
                        :start-price-range="startPriceRange"
                        :prices="prices"
                        :is-load-prices="isLoading.prices"
                        :is-first-loading="firstLoadingPrice"
                    ></TabsPrices>
                </div>
            </div>
        </Page>
    </section>
</template>

<script>
    /* eslint-disable */

    import { mapActions, mapGetters, mapMutations, mapState } from 'vuex';
    import AgServerSideAnalyticsTable from '~/components/ag-tables/AgServerSideAnalyticsTable';
    import AgClientSideAnalyticsTable from '~/components/ag-tables/AgClientSideAnalyticsTable';
    import SeDatePickerPeriod from '~/components/ui/SeDatePickerPeriod';
    import BrandCategoriesChart from '~/components/pages/analytics/brand/charts/BrandCategoriesChart';
    import BrandSellersChart from '~/components/pages/analytics/brand/charts/BrandSellersChart';
    import BrandPricesChart from '~/components/pages/analytics/brand/charts/BrandPricesChart';
    import { format } from 'date-fns';
    import Page from '~/components/ui/SeInnerPage';
    import mouseOverOutAgGrid from '~/assets/js/mixins/mouseOverOutAgGrid';

    export default {
        name: 'BrandAnalytics',
        components: {
            AgServerSideAnalyticsTable,
            AgClientSideAnalyticsTable,
            SeDatePickerPeriod,
            BrandCategoriesChart,
            BrandSellersChart,
            BrandPricesChart,
            Page,
        },
        mixins: [mouseOverOutAgGrid],
        asyncData({ from }) {
            const prevRoute = from.path;
            return {
                prevRoute,
            };
        },
        data: () => ({
            isShowTmpl: false,
            tabLoading: {
                priceAnalysis: false,
            },
            title: {
                isActive: true,
                text: 'Аналитика брендов',
            },
            pageSize: 20,
            switchGroup: false,
            search: '',
            selectedDates: [],
            tabs: [
                { id: 1, title: 'Товары', value: 'products', disabled: false },
                { id: 2, title: 'Категории', value: 'categories', disabled: false },
                { id: 3, title: 'Продавцы', value: 'sellers', disabled: false },
                { id: 4, title: 'Ценовой анализ', value: 'prices', disabled: false },
            ],
            switchLabel: 'Сгруппировать по предметам',
            tabSelected: 0,
            tab: 0,
            checked: false,
            isDataLoaded: {
                products: false,
                categories: false,
                sellers: false,
                prices: false,
            },
            segment: '25',
            range: [],
            brandParams: {
                brand: null,
            },
            noRowsMessage: "Выберите бренд, чтобы посмотреть отчет",
        }),
        head() {
            return {
                title: 'Категорийный анализ',
            };
        },
        computed: {
            ...mapState('analytics', [
                'isBrandsAnalyticsLoading',
                'isBrandPriceLoading',
                'userParams',
                'errors',
                'isLoading',
                'brandList',
                'prices',
                'dataPrices',
                'firstLoadingPrice',
                'startPriceRange',
            ]),
            ...mapGetters('analytics', [
                'productsRows',
                'productsColumns',
                'productsTotal',
                'categoriesRows',
                'categoriesColumns',
                'categoriesTotal',
                'sellersRows',
                'sellersColumns',
                'sellersTotal',
                'pricesRows',
                'pricesColumns',
            ]),
            ...mapGetters(['isSelectedMp']),
            back() {
                return {
                    isActive: true,
                    route: this.prevRoute,
                };
            },
            period() {
                return {
                    isActive: true,
                    selectedDates: this.selectedDates,
                };
            },
        },
        watch: {
            // TODO Убрать эту прокладку пока не решиться вопрос с производительностью AgGrid и ChartJs
            tabSelected(value) {
                setTimeout(() => {
                    this.tab = value;
                }, 300);
            },
            async selectedDates(newVal) {
                this.setField({ field: 'selectedDates', value: newVal });
                if (newVal && this.brandParams.brand) {
                    const [start_date, end_date] = newVal;
                    this.brandParams = { ...this.brandParams, start_date, end_date };
                    const params = this.appendParams(this.brandParams);
                    const type = this.tabs[this.tabSelected].value;
                    if (type !== 'prices') {
                        await this.loadBrands({ type, params });
                    }
                }
            },
            userParams: {
                async handler(newVal) {
                    if (newVal) {
                        await this.defineBrand();
                    }
                },
                deep: true,
                immediate: true,
            },
        },
        async created() {
            this.setDefaultParams();
            if (this.$route.query.brand)
                await this.handleSelect({ value: this.$route.query.brand });
            else await this.loadUserParams('products');

            this.isShowTmpl = true;
        },
        methods: {
            ...mapActions('analytics', [
                'loadUserParams',
                'loadBrandList',
                'loadBrands',
                'loadBrandPrices',
                'loadBrandList',
            ]),
            ...mapActions('categories-analitik', ['searchAndOpenCategory']),
            ...mapMutations('analytics', [
                'setField',
                'setDataPrices',
                'setDefaultParams',
                'resetParams',
            ]),
            handleDateChange(value) {
                this.selectedDates = value;
            },
            async defineBrand() {
                const { brand, start_date, end_date } = this.userParams;
                this.brandParams = { ...this.brandParams, brand, per_page: this.pageSize };
                this.search = brand;
                if (start_date && end_date) {
                    this.selectedDates = [start_date, end_date];
                }
            },
            async changeTab(type) {
                const params = this.appendParams(this.brandParams);

                if (type !== 'prices') {
                    await this.loadBrands({ type, params });
                } else {
                    await this.loadBrandPrices();
                }
            },
            async querySearch(queryString, callback) {
                this.search = queryString;
                await this.loadBrandList(this.search);
                callback(this.brandList);
            },
            async handleSelect(item) {
                try {
                    this.setField({ field: 'brand', value: item.value });
                    this.brandParams = { ...this.brandParams, brand: item.value };
                    this.search = item.value;
                    const params = this.appendParams(this.brandParams);
                    const type = this.tabs[this.tabSelected].value;
                    Object.entries(this.isDataLoaded).forEach(([key, value]) => {
                        if (value && key !== type) {
                            this.isDataLoaded[key] = false;
                        }
                    });

                    this.resetParams();

                    if (type !== 'prices') {
                        await this.loadBrands({ type, params });
                    } else {
                        await this.loadBrandPrices();
                    }
                } catch (e) {
                    console.error(e);
                }
            },
            async dataChanged(page, sortModel, filterModel) {
                if (this.brandParams.brand && this.selectedDates.length > 0) {
                    this.brandParams = { ...this.brandParams, page: page + 1 };
                    const params = this.appendParams(this.brandParams);

                    if (sortModel.length > 0) {
                        params.append('sort[col]', sortModel[0].colId);
                        params.append('sort[sort]', sortModel[0].sort);
                    }

                    if (Object.keys(filterModel).length > 0) {
                        const key = Object.keys(filterModel)[0];
                        const { filterType } = filterModel[key];
                        params.append(`filters[${key}][filterType]`, filterType);

                        if (Object.prototype.hasOwnProperty.call(filterModel[key], 'condition1')) {
                            for (const filter in filterModel[key]['condition1']) {
                                if (filter !== 'filterType') {
                                    params.append(
                                        `filters[${key}][condition1][${filter}]`,
                                        filterModel[key]['condition1'][filter]
                                    );
                                }
                            }

                            const operator = filterModel[key]['operator'];
                            params.append(`filters[${key}][operator]`, operator);

                            for (const filter in filterModel[key]['condition2']) {
                                if (filter !== 'filterType') {
                                    params.append(
                                        `filters[${key}][condition2][${filter}]`,
                                        filterModel[key]['condition2'][filter]
                                    );
                                }
                            }
                        } else {
                            const { filter, type } = filterModel[key];

                            params.append(`filters[${key}][filter]`, filter);
                            params.append(`filters[${key}][type]`, type);
                        }
                    }

                    const type = this.tabs[this.tabSelected].value;
                    await this.loadBrands({ type, params });
                }
            },
            async handleRangeValues() {
                const params = this.appendParams(this.brandParams);
                await this.loadBrandPrices({ params, isRangeLoaded: false });
                if (this.selectedDates.length > 0) {
                }
            },
            formatDate(date) {
                return format(new Date(date), 'yyyy-MM-dd');
            },
            appendParams(obj) {
                const params = new URLSearchParams();
                for (const key in obj) {
                    if (Object.prototype.hasOwnProperty.call(obj, key) && obj[key]) {
                        params.append(key, obj[key]);
                    }
                }
                return params;
            },
        },
    };
</script>

<style scoped lang="scss">
    .tab-content {
        position: relative;

        &__loader {
            position: absolute;
            top: 0;
            left: 0;
            z-index: 4;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.6);
        }
    }

    .tab-loader {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 400px;
    }

    .brand-page {
        position: relative;
        display: flex;
        min-height: 100%;
        margin-bottom: 100px;

        &_container {
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        &_header {
            display: flex;
            justify-content: space-between;
            width: 100%;
            margin-bottom: 24px;
            gap: 16px;

            @media screen and (max-width: 800px) {
                align-items: flex-start;
                flex-direction: column;
            }

            &__inner {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 16px;

                &-title {
                    font-size: 26px;
                    font-weight: 600;
                    line-height: 35px;
                    user-select: none;

                    @extend %text-h1;

                    @media screen and (max-width: 960px) {
                        font-size: 22px;
                    }
                }

                &-prev {
                    padding: 6px 16px 6px 12px;
                    border-radius: 200px;
                    border: 1px solid $base-500;
                    font-size: 14px;
                    font-style: normal;
                    font-weight: 500;
                    line-height: 19px;
                    color: $base-700;
                    cursor: pointer;

                    & svg {
                        margin-right: 8px;
                    }
                }

                &-controls {
                    display: flex;
                    gap: 8px;

                    @include md {
                        width: fit-content;
                    }

                    .column {
                        width: 115px;
                        max-width: 115px !important;
                    }

                    .select {
                        border: 1px solid $inner-border-color !important;
                    }

                    .datepicker {
                        width: 300px;
                    }
                }
            }
        }

        &_table-container {
            display: flex;
            border-radius: 24px;
            background-color: $color-light-background;
            flex-direction: column;

            @include cardShadow;
        }

        &_table-head {
            display: flex;
            padding: 24px 16px 8px;

            .custom-search {
                margin-right: 8px;
            }

            @media screen and (max-width: 620px) {
                flex-wrap: wrap-reverse;

                .custom-search {
                    max-width: 100%;
                    margin: 8px 0 0;
                }
            }

            &__checkbox {
                font-size: 14px;
            }
        }

        &_table-body {
            padding: 16px;

            &__switch {
                width: fit-content;
            }

            &__cover {
                position: absolute;
                z-index: 10;
                width: 100%;
                height: 100%;

                &-loader {
                    position: absolute;
                    top: 50%;
                    left: 50%;
                }
            }
        }
    }

    .page-loader {
        position: relative;

        &__circle {
            position: absolute;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.4);
        }
    }
</style>
