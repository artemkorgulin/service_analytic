import Vue from 'vue';
import { sortNumValuesAgGrid } from '~/assets/js/utils/helpers';
import { errorHandler } from '~utils/response.utils';
import { currencyFormatter } from '~/components/pages/analytics/brand/helpers/currencyFormatter';
import { numberFormatter } from '~/components/pages/analytics/brand/helpers/numberFormatter';
import LinkToCategoriesRenderer from '~/components/pages/analytics/brand/additional/LinkToCategoriesRenderer';

const colors = ['#FC6E90', '#607FEA', '#5DDC98', '#F4E450', '#3DC1CA', '#A46FE8', '#F4A950'];

function searchIndexCategories(categories, value) {
    return !categories || !value
        ? null
        : categories.indexOf(categories.find(item => item.web_id === value.web_id));
}

const stringFields = ['category'];
const fieldsToNumber = ['avg_rating'];

export default {
    namespaced: true,
    state: () => ({
        categoryPath: '',
        selectCategories: {
            levelOne: null,
            levelTwo: null,
            levelThree: null,
        },
        selectCategoriesFlag: true,

        categories: null,

        products: null,
        isLoadProducts: false,
        filtresProducts: {
            filter: {},
            sort: {},
        },
        dataProducts: null,
        pageProducts: 0,
        productsColums: [
            { field: 'image_middle', headerName: 'Фото', headerTooltip: 'Фото' },
            { field: 'sku', headerName: 'Артикул', headerTooltip: 'Артикул' },
            {
                field: 'brand',
                headerName: 'Бренд',
                headerTooltip: 'Бренд',
                cellRendererFramework: Vue.extend(LinkToCategoriesRenderer),
                from: 'cat',
                to: 'brand',
            },
            { field: 'name', headerName: 'Название', headerTooltip: 'Название' },
            {
                field: 'category',
                headerName: 'Категория',
                headerTooltip: 'Категория',
                cellRendererFramework: Vue.extend(LinkToCategoriesRenderer),
                from: 'cat',
            },
            {
                field: 'position',
                headerName: 'Позиция в рубрике',
                headerTooltip: 'Позиция в рубрике',
                valueFormatter: params => numberFormatter(params.value === 0 ? '-' : params.value),
            },
            { field: 'supplier', headerName: 'Продавец', headerTooltip: 'Продавец' },
            {
                field: 'revenue',
                headerName: 'Выручка',
                headerTooltip: 'Выручка',
                valueFormatter: params => currencyFormatter(params.data.revenue, '₽'),
            },
            { field: 'rating', headerName: 'Рейтинг', headerTooltip: 'Рейтинг' },
            {
                field: 'comments',
                headerName: 'Комментариев',
                headerTooltip: 'Комментариев',
                valueFormatter: params => numberFormatter(params.data.comments),
            },
            {
                field: 'last_price',
                headerName: 'Цена',
                headerTooltip: 'Цена',
                valueFormatter: params => currencyFormatter(params.data.last_price, '₽'),
            },
            {
                field: 'min_price',
                headerName: 'Мин. цена',
                headerTooltip: 'Мин. цена',
                valueFormatter: params => currencyFormatter(params.data.min_price, '₽'),
            },
            {
                field: 'max_price',
                headerName: 'Макс. цена',
                headerTooltip: 'Макс. цена',
                valueFormatter: params => currencyFormatter(params.data.max_price, '₽'),
            },
            {
                field: 'price_with_sale',
                headerName: 'Цена со скидкой',
                headerTooltip: 'Цена со скидкой',
                valueFormatter: params => currencyFormatter(params.data.price_with_sale, '₽'),
            },
            {
                field: 'stock',
                headerName: 'Наличие',
                headerTooltip: 'Наличие',
                valueFormatter: params => numberFormatter(params.data.stock),
            },
            {
                field: 'revenue_potential',
                headerName: 'Потенциал',
                headerTooltip: 'Потенциал',
                valueFormatter: params => numberFormatter(params.data.revenue_potential),
            },
            {
                field: 'lost_profit',
                headerName: 'Упущенная выручка',
                headerTooltip: 'Упущенная выручка',
                valueFormatter: params => currencyFormatter(params.data.lost_profit, '₽'),
            },
            {
                field: 'lost_profit_percent',
                headerName: 'Упущенная выручка %',
                headerTooltip: 'Упущенная выручка %',
            },
            { field: 'days_in_stock', headerName: 'Был в наличии', headerTooltip: 'Был в наличии' },
            {
                field: 'days_with_sales',
                headerName: 'Дней с продажами',
                headerTooltip: 'Дней с продажами',
            },
            {
                field: 'sales_avg',
                headerName: 'Среднее в день',
                headerTooltip: 'Среднее в день',
                valueFormatter: params => numberFormatter(params.data.sales_avg),
            },
            {
                field: 'total_sales',
                headerName: 'Продаж',
                headerTooltip: 'Продаж',
                valueFormatter: params => numberFormatter(params.data.total_sales),
            },
            {
                field: 'avg_price',
                headerName: 'Ср. цена',
                headerTooltip: 'Ср. цена',
                valueFormatter: params => currencyFormatter(params.data.avg_price, '₽'),
            },
            {
                field: 'base_price',
                headerName: 'Базовая цена',
                headerTooltip: 'Базовая цена',
                valueFormatter: params => currencyFormatter(params.data.base_price, '₽'),
            },
            { field: 'base_sale', headerName: 'Скидка', headerTooltip: 'Скидка' },
            { field: 'promo_sale', headerName: 'Промокод', headerTooltip: 'Промокод' },
            { field: 'color', headerName: 'Цвет', headerTooltip: 'Цвет' },
            { field: 'graph_sales', headerName: 'График продаж', headerTooltip: 'График продаж' },
            {
                field: 'graph_category_count',
                headerName: 'Число категорий',
                headerTooltip: 'Число категорий',
            },
            { field: 'graph_price', headerName: 'Изменение цены', headerTooltip: 'Изменение цены' },
            // { field: 'subject_id', headerName: '' },
            // { field: 'web_id', headerName: '' },
            // { field: 'sales_in_stock_avg', headerName: '' },
        ],

        subcategories: null,
        isLoadSubcategories: false,
        subcategoriesColums: [
            { field: 'category', headerName: 'Категория', headerTooltip: 'Категория' },
            {
                field: 'revenue',
                headerName: 'Выручка',
                headerTooltip: 'Выручка',
                valueFormatter: params => currencyFormatter(params.value, '₽'),
            },
            { field: 'avg_rating', headerName: 'Рейтинг', headerTooltip: 'Рейтинг' },
            { field: 'avg_comments', headerName: 'Комментарии', headerTooltip: 'Комментарии' },
            {
                field: 'products',
                headerName: 'Товаров',
                headerTooltip: 'Товаров',
                comparator: sortNumValuesAgGrid,
                valueFormatter: params => numberFormatter(params.value),
            },
            {
                field: 'products_with_sales',
                headerName: 'Товаров с продажами',
                headerTooltip: 'Товаров с продажами',
                comparator: sortNumValuesAgGrid,
                valueFormatter: params => numberFormatter(params.value),
            },
            {
                field: 'sales',
                headerName: 'Продажи',
                headerTooltip: 'Продажи',
                comparator: sortNumValuesAgGrid,
                valueFormatter: params => numberFormatter(params.value),
            },
            {
                field: 'avg_price',
                headerName: 'Средняя цена',
                headerTooltip: 'Средняя цена',
                comparator: sortNumValuesAgGrid,
                valueFormatter: params => currencyFormatter(params.value, '₽'),
            },
            {
                field: 'products_with_sales_percent',
                headerName: 'Процент товаров со продажами',
                comparator: sortNumValuesAgGrid,
                headerTooltip: 'Процент товаров со продажами',
            },
            {
                field: 'sales_per_products_avg',
                headerName: 'Среднее кол-во продаж',
                comparator: sortNumValuesAgGrid,
                headerTooltip: 'Среднее кол-во продаж',
            },
            {
                field: 'sales_per_products_with_sales_avg',
                headerName: 'Среднее кол-во товаров со продажами',
                comparator: sortNumValuesAgGrid,
                headerTooltip: 'Среднее кол-во товаров со продажами',
            },
            {
                field: 'suppliers',
                headerName: 'Продавцов',
                headerTooltip: 'Продавцов',
                comparator: sortNumValuesAgGrid,
                valueFormatter: params => numberFormatter(params.value),
            },
            {
                field: 'suppliers_with_sales',
                headerName: 'Продавцов со продажами',
                headerTooltip: 'Продавцов со продажами',
                comparator: sortNumValuesAgGrid,
                valueFormatter: params => numberFormatter(params.value),
            },
            {
                field: 'suppliers_with_sales_percent',
                headerName: 'Процент продавцов со продажами',
                comparator: sortNumValuesAgGrid,
                headerTooltip: 'Процент продавцов со продажами',
            },
            {
                field: 'revenue_per_products_avg',
                headerName: 'Средняя цена продажи',
                headerTooltip: 'Средняя цена продажи',
                comparator: sortNumValuesAgGrid,
                valueFormatter: params =>
                    currencyFormatter(params.data.revenue_per_products_avg, '₽'),
            },
            {
                field: 'revenue_per_products_with_sales_avg',
                headerName: 'Средняя цена товара с продажами',
                headerTooltip: 'Средняя цена товара с продажами',
                comparator: sortNumValuesAgGrid,
                valueFormatter: params =>
                    currencyFormatter(params.data.revenue_per_products_with_sales_avg, '₽'),
            },
            // { field: 'subject', headerName: '' },
        ],

        prices: null,
        isLoadPrices: false,
        pricesColums: [
            {
                field: 'min_range',
                headerName: 'От',
                headerTooltip: 'От',
                valueFormatter: params => currencyFormatter(params.data.min_range, '₽'),
            },
            {
                field: 'max_range',
                headerName: 'До',
                headerTooltip: 'До',
                valueFormatter: params => currencyFormatter(params.data.max_range, '₽'),
            },
            {
                field: 'sales',
                headerName: 'Продажи',
                headerTooltip: 'Продажи',
                comparator: sortNumValuesAgGrid,
                valueFormatter: params => numberFormatter(params.data.sales),
            },
            {
                field: 'revenue',
                headerName: 'Выручка',
                headerTooltip: 'Выручка',
                sort: true,
                comparator: sortNumValuesAgGrid,
                filterParams: { valueFormatter: params => params.value },
                valueFormatter: params => currencyFormatter(params.data.revenue, '₽'),
            },
            {
                field: 'products',
                headerName: 'Товаров',
                headerTooltip: 'Товаров',
                sort: true,
                comparator: sortNumValuesAgGrid,
                filterParams: { valueFormatter: params => Number(params.value) },
                valueFormatter: params => numberFormatter(params.data.products),
            },
            {
                field: 'products_with_sales',
                headerName: 'Товаров с продажами',
                headerTooltip: 'Товаров с продажами',
                sort: true,
                comparator: sortNumValuesAgGrid,
                valueFormatter: params => numberFormatter(params.value),
            },
            {
                field: 'suppliers',
                headerName: 'Продавцов',
                headerTooltip: 'Продавцов',
                sort: true,
                comparator: sortNumValuesAgGrid,
                valueFormatter: params => numberFormatter(params.data.suppliers),
            },
            {
                field: 'suppliers_with_sales',
                headerName: 'Продавцов со продажами',
                headerTooltip: 'Продавцов со продажами',
                sort: true,
                comparator: sortNumValuesAgGrid,
                valueFormatter: params => numberFormatter(params.data.suppliers_with_sales),
            },
            {
                field: 'revenue_per_products_avg',
                headerName: 'Выручка на товар',
                headerTooltip: 'Выручка на товар',
                sort: true,
                comparator: sortNumValuesAgGrid,
                valueFormatter: params =>
                    currencyFormatter(params.data.revenue_per_products_avg, '₽'),
            },
            {
                field: 'revenue_per_products_with_sales_avg',
                headerName: 'Средняя цена товара с продажами',
                headerTooltip: 'Средняя цена товара с продажами',
                sort: true,
                comparator: sortNumValuesAgGrid,
                valueFormatter: params => currencyFormatter(params.value, '₽'),
            },
            {
                field: 'sales_per_products_avg',
                headerName: 'Среднее кол-во продаж',
                headerTooltip: 'Среднее кол-во продаж',
                sort: true,
                comparator: sortNumValuesAgGrid,
                valueFormatter: params => numberFormatter(params.value),
            },
            {
                field: 'sales_per_products_with_sales_avg',
                headerName: 'Среднее кол-во товаров со продажами',
                headerTooltip: 'Среднее кол-во товаров со продажами',
                sort: true,
                comparator: sortNumValuesAgGrid,
                valueFormatter: params => numberFormatter(params.value),
            },
            {
                field: 'avg_price',
                headerName: 'Средняя цена',
                headerTooltip: 'Средняя цена',
                sort: true,
                comparator: sortNumValuesAgGrid,
                valueFormatter: params => currencyFormatter(params.data.avg_price, '₽'),
            },
        ],
        dataPrices: {
            min: null,
            max: null,
            segment: 25,
        },

        subjects: null,
        selectSubjectId: null,
        date: null,
        defaultParams: {},
        firstLoadingPrice: false,
        startPriceRange: {
            min: 0,
            max: 20000,
        },
        lastQueryParam: {
            products: null,
            subCat: null,
            prices: null,
        },
    }),
    getters: {
        // TODO: Сделать единый компонент для категорий брендов, и подкатегорий
        subcategoriesColumns: state =>
            state.subcategoriesColums.map(item =>
                !stringFields.includes(item.field)
                    ? {
                          ...item,
                          sortable: true,
                          filter: 'agNumberColumnFilter',
                      }
                    : {
                          sortable: true,
                          filter: 'agTextColumnFilter',
                          ...item,
                      }
            ),
        path: (state, getters) => {
            const {
                selectCategories: { levelOne, levelTwo, levelThree },
                selectSubjectId,
            } = state;

            const { subjectsList } = getters;
            const selectedSubject = subjectsList.find(({ id }) => id === selectSubjectId);

            return [
                levelOne,
                levelTwo,
                levelThree,
                selectedSubject ? { name: selectedSubject.label } : undefined,
            ]
                .filter(item => item)
                .map(({ name }) => name)
                .join('/');
        },
        levelOne: state => state.categories,

        levelTwo: state => {
            let value = [];
            const indexLevelOne = searchIndexCategories(
                state.categories,
                state.selectCategories.levelOne
            );
            if (indexLevelOne !== null && state.categories[indexLevelOne].children.length > 0) {
                value = state.categories[indexLevelOne].children;
            }
            return value;
        },

        levelThree: state => {
            let value = [];
            const indexLevelOne = searchIndexCategories(
                state.categories,
                state.selectCategories.levelOne
            );
            let indexLevelTwo = null;
            if (indexLevelOne !== null) {
                indexLevelTwo = searchIndexCategories(
                    state.categories[indexLevelOne].children,
                    state.selectCategories.levelTwo
                );
            }

            if (
                indexLevelOne !== null &&
                state.categories[indexLevelOne].children.length > 0 &&
                indexLevelTwo !== null &&
                state.categories[indexLevelOne].children[indexLevelTwo].children.length > 0
            ) {
                value = state.categories[indexLevelOne].children[indexLevelTwo].children;
            }
            return value;
        },

        finishCategiry: state => {
            const keys = Object.keys(state.selectCategories);
            let fin = null;
            for (let i = 0; i < keys.length; i++) {
                if (state.selectCategories[keys[i]] !== null) {
                    fin = state.selectCategories[keys[i]];
                } else {
                    break;
                }
            }
            return fin;
        },

        subjectsList: state => {
            if (!state.subjects) {
                return [];
            }
            const ids = Object.keys(state.subjects);
            const lables = Object.values(state.subjects);
            const subjects = [];
            for (let i = 0; i < ids.length; i++) {
                subjects.push({ id: ids[i], label: lables[i] });
            }
            return subjects;
        },

        subcategoriesList: state => state.subcategories,
        productsList: state => {
            const graphMap = ['graph_sales', 'graph_category_count', 'graph_price', 'graph_stock'];
            return !state.products
                ? []
                : state.products.map(item =>
                      Object.entries(item).reduce(function (current, coll) {
                          let value = null;
                          if (coll[0] === 'sku') {
                              value = { link: item.url, value: item.sku };
                          } else if (graphMap.includes(coll[0])) {
                              value = coll[1].map(item => Number(item));
                          } else {
                              value = coll[1];
                          }
                          current[coll[0]] = value;
                          return current;
                      }, {})
                  );
        },

        pricesList: state => state.prices || [],

        chartDataSubcategories: state => {
            if (state.subcategories && state.subcategories.length > 0) {
                return state.subcategories
                    .map(({ category, sales, revenue, products }) => ({
                        category,
                        sales,
                        products,
                        revenue: Math.floor(Number(revenue)),
                    }))
                    .sort((a, b) => b['revenue'] - a['revenue'])
                    .slice(0, 7)
                    .map((item, index) => ({ ...item, backgroundColor: colors[index] }));
            }
            return [];
        },
    },

    mutations: {
        SET_SELECTCATEGORIES(state, data) {
            state.selectCategories = data;
        },
        setLastQueryParam(state, value) {
            state.lastQueryParam[value[0]] = value[1];
        },

        setDefaultParams(state) {
            const fields = [
                'pageProducts',
                'filtresProducts',
                'dataPrices',
                'firstLoadingPrice',
                'startPriceRange',
            ];
            for (const key of fields) {
                state.defaultParams[key] = state[key];
            }
        },
        resetParams(state) {
            Object.keys(state.defaultParams).forEach(key => {
                console.log(state.defaultParams[key]);
                state[key] = state.defaultParams[key];
            });
        },

        // Установка даты периода
        setDate(state, value) {
            state.date = value;
        },

        // Работа с категориями
        setCategoriesData(state, value) {
            state[value[0]] = value[1];
        },

        // Предметные категории
        setSubjectsData(state, value) {
            state[value[0]] = value[1];
        },

        // Работа с товарами
        setProducts(state, value) {
            state.products = value;
        },
        setIsLoadProducts(state, value) {
            state.isLoadProducts = value;
        },
        setPageProducts(state, value) {
            state.pageProducts = value;
        },
        setDataProducts(state, value) {
            state.dataProducts = value;
        },
        setFiltresProductsFilter(state, value) {
            state.filtresProducts.filter = value;
        },
        setFiltresProductsSort(state, value) {
            state.filtresProducts.sort = value;
        },
        clearDataProduts(state) {
            state.filtresProducts.sort = {};
            state.filtresProducts.filter = {};
            state.pageProducts = 0;
        },

        // Работа с подкатегориями
        setSubcategories(state, value) {
            state.subcategories = value;
        },
        setIsLoadSubcategories(state, value) {
            state.isLoadSubcategories = value;
        },

        // Ценовой анализ
        setPrices(state, value) {
            state.prices = value;
        },
        setIsLoadPrices(state, value) {
            state.isLoadPrices = value;
        },
        setDataPrices(state, value) {
            Object.keys(value).forEach(key => {
                state.dataPrices[key] = value[key];
            });
        },
        setStartPriceRange(state, value) {
            Object.keys(value).forEach(key => {
                if (key in state.startPriceRange) state.startPriceRange[key] = value[key];
            });
        },
    },
    actions: {
        async searchAndOpenCategory({ state, dispatch, commit }, id) {
            await dispatch('loadAnalyticsCategoriesWildberries');
            const filter = (array, level) => {
                for (let i = 0; i < array.length; i++) {
                    const item = array[i];
                    if (level === 1) {
                        commit('setCategoriesData', [
                            'selectCategories',
                            {
                                levelOne: item,
                                levelTwo: null,
                                levelThree: null,
                            },
                        ]);
                    }
                    if (level === 2) {
                        commit('setCategoriesData', [
                            'selectCategories',
                            {
                                levelOne: state.selectCategories.levelOne,
                                levelTwo: item,
                                levelThree: null,
                            },
                        ]);
                    }
                    if (level === 3) {
                        commit('setCategoriesData', [
                            'selectCategories',
                            {
                                levelOne: state.selectCategories.levelOne,
                                levelTwo: state.selectCategories.levelTwo,
                                levelThree: item,
                            },
                        ]);
                    }
                    /* eslint-disable */
                    if (
                        item.web_id === id ||
                        (item.children &&
                            item.children.length > 0 &&
                            filter(item.children, level + 1))
                    ) {
                        return true;
                    }
                }
                return false;
            };
            filter(state.categories, 1);
            commit('setCategoriesData', ['selectCategoriesFlag', false]);
        },

        async loadAnalyticsCategoriesWildberries({ commit }) {
            try {
                const topic = '/api/an/v1/wb/get/categories';
                const {
                    data: {
                        data: { categories },
                    },
                } = await this.$axios.get(topic);
                commit('setCategoriesData', ['categories', categories]);
            } catch (error) {
                console.error(error);
                errorHandler(error, this.$notify);
            }
        },

        async loadAnalyticsProductsWildberries({ state, commit, getters }) {
            try {
                const topic = '/api/an/v2/wb/statistic/categories/products';
                const params = {
                    category: state.categoryPath,
                    per_page: 100,
                    page: state.pageProducts + 1,
                    start_date: state.date[0],
                    end_date: state.date[1],
                    ...state.filtresProducts.sort,
                    ...state.filtresProducts.filter,
                };

                console.log(state.filtresProducts.filter);

                if (state.lastQueryParam.products !== JSON.stringify(params)) {
                    commit('setLastQueryParam', ['products', JSON.stringify(params)]);
                } else {
                    console.log('dsd');
                    return;
                }
                commit('setIsLoadProducts', true);

                let {
                    data: { data },
                } = await this.$axios.get(topic, { params });

                const dataProducts = { ...data };
                const pagFields = ['current_page', 'from', 'last_page', 'per_page', 'to', 'total'];

                for (let key of Object.keys(dataProducts)) {
                    if (!pagFields.includes(key)) {
                        delete dataProducts[key];
                    }
                }

                data = data.data;

                commit('setProducts', data);
                commit('setDataProducts', dataProducts);
                return [data, dataProducts];
            } catch (error) {
                console.error(error);
                errorHandler(error, this.$notify);
            } finally {
                commit('setIsLoadProducts', false);
            }
        },

        loadAnalyticsSubcategoriesWildberries({ state, commit, getters }) {
            try {
                const topic = 'api/an/v2/wb/statistic/categories/subcategories';
                const params = {
                    category: state.categoryPath,
                    start_date: state.date[0],
                    end_date: state.date[1],
                };

                if (state.lastQueryParam.subCat !== JSON.stringify(params)) {
                    commit('setLastQueryParam', ['subCat', JSON.stringify(params)]);
                } else {
                    return;
                }
                commit('setIsLoadSubcategories', true);

                this.$axios.get(topic, { params }).then(({ data }) => {
                    if (typeof data.data === 'string') {
                        this.$notify.create({
                            message: data.data,
                            type: 'negative',
                        });
                    } else {
                        data = data.data;

                        data.forEach(item => {
                            Object.keys(item).forEach(key => {
                                if (fieldsToNumber.includes(key)) {
                                    item[key] = Number(item[key]);
                                } else if (!stringFields.includes(key)) {
                                    item[key] = Math.floor(item[key]);
                                }
                            });
                        });
                        commit('setSubcategories', data);
                    }
                    commit('setIsLoadSubcategories', false);
                });
            } catch (error) {
                console.error(error);
                errorHandler(error, this.$notify);
            }
        },

        async loadAnalyticsCategoriesSubjects({ state, commit, getters }, webId) {
            try {
                const topic = '/api/an/v1/wb/get/category-subjects';
                const params = {
                    category_id: webId || getters.finishCategiry.web_id,
                };
                const {
                    data: {
                        data: { subjects },
                    },
                } = await this.$axios.get(topic, { params });

                commit('setSubjectsData', ['subjects', subjects]);
                return subjects;
            } catch (error) {
                console.error(error);
                errorHandler(error, this.$notify);
            }
        },

        loadAnalyticsCategoriesPrices({ state, commit }) {
            try {
                const topic = '/api/an/v2/wb/statistic/categories/price-analysis';
                const params = {
                    category: state.categoryPath,
                    start_date: state.date[0],
                    end_date: state.date[1],
                    segment: state.dataPrices.segment,
                    min: state.dataPrices.min,
                    max: state.dataPrices.max,
                };

                if (state.lastQueryParam.prices !== JSON.stringify(params)) {
                    commit('setLastQueryParam', ['prices', JSON.stringify(params)]);
                } else {
                    return;
                }
                commit('setIsLoadPrices', true);

                this.$axios.get(topic, { params }).then(({ data: { data } }) => {
                    commit('setPrices', data);
                    if (!state.firstLoadingPrice) {
                        const minMax = {
                            min: Number(data[0].min_range),
                            max: Number(data[data.length - 1].max_range),
                        };
                        commit('setStartPriceRange', minMax);
                        commit('setDataPrices', minMax);
                    }
                    commit('setSubjectsData', ['firstLoadingPrice', true]);
                    commit('setIsLoadPrices', false);
                });
            } catch (error) {
                console.error(error);
                errorHandler(error, this.$notify);
            }
        },
    },
};
