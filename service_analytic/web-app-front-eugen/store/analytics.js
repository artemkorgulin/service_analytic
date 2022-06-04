import {
    productsHeaderNames,
    categoriesHeaderNames,
    sellersHeaderNames,
    pricesHeaderNames,
} from '~/components/pages/analytics/brand/helpers/headerNames';

const types = ['products', 'categories', 'sellers'];
const colors = ['#FC6E90', '#5DDC98', '#607FEA', '#F4E450', '#3DC1CA', '#F4A950', '#A46FE8'];

export default {
    namespaced: true,
    state: () => ({
        brand: '',
        userParams: {},
        products: {
            data: [],
            total: null,
        },
        categories: {
            data: [],
            total: null,
        },
        sellers: {
            data: [],
            total: null,
        },
        brandList: [],
        prices: [],
        priceRange: [0, 200000],
        selectedDates: [],
        startPriceRange: {
            min: null,
            max: null,
            segment: 25,
        },
        dataPrices: {
            min: null,
            max: null,
            segment: 25,
        },
        isGrouped: false,
        isBrandsAnalyticsLoading: false,
        isBrandPriceLoading: false,
        errors: {
            products: null,
            categories: null,
            sellers: null,
            prices: null,
        },
        firstLoadingPrice: false,
        isLoading: {
            products: false,
            categories: false,
            sellers: false,
            prices: false,
        },
        lastQueryParam: {
            products: null,
            categories: null,
            sellers: null,
            prices: null,
        },
        defaultParams: {},
    }),
    getters: {
        productsRows: state => {
            const fieldsFormatInNumber = [
                'graph_category_count',
                'graph_price',
                'graph_sales',
                'graph_stock',
            ];
            if (state.products.data.length > 0) {
                return Object.values(state.products.data).map(row => ({
                    ...row,
                    ...fieldsFormatInNumber.reduce((acc, el) => {
                        acc[el] = row[el].map(value => Number(value));
                        return acc;
                    }, {}),
                }));
            }
            return [];
        },
        pricesColumns: () => pricesHeaderNames,

        productsColumns: state => {
            if (state.products.data.length > 0) {
                return Object.keys(state.products.data[0])
                    .map(col => ({
                        field: col,
                        filterParams: {
                            buttons: ['reset', 'apply'],
                        },
                        ...productsHeaderNames.find(({ field }) => field === col),
                    }))
                    .filter(item => Object.prototype.hasOwnProperty.call(item, 'headerName'))
                    .sort((a, b) => a['order'] - b['order']);
            }
            return [];
        },

        productsTotal: state => state.products.total,

        categoriesRows: state => Object.values(state.categories.data),

        categoriesColumns: state => {
            if (state.categories.data.length > 0) {
                return Object.keys(state.categories.data[0])
                    .map(col => ({
                        field: col,
                        filterParams: {
                            buttons: ['reset', 'apply'],
                        },
                        ...categoriesHeaderNames.find(({ field }) => field === col),
                    }))
                    .filter(item => Object.prototype.hasOwnProperty.call(item, 'headerName'))
                    .sort((a, b) => a['order'] - b['order']);
            }
            return [];
        },

        categoriesTotal: state => state.categories.total,

        sellersRows: state => {
            if (state.sellers.data.length > 0) {
                return Object.values(state.sellers.data).map(row => ({
                    ...row,
                    graph_sales: row.graph_sales.map(item => Number(item)),
                }));
            }
            return [];
        },

        sellersColumns: state => {
            if (state.sellers.data.length > 0) {
                return Object.keys(state.sellers.data[0])
                    .map(col => ({
                        field: col,
                        filterParams: {
                            buttons: ['reset', 'apply'],
                        },
                        ...sellersHeaderNames.find(({ field }) => field === col),
                    }))
                    .filter(item => Object.prototype.hasOwnProperty.call(item, 'headerName'))
                    .sort((a, b) => a['order'] - b['order']);
            }
            return [];
        },

        sellersTotal: state => state.sellers.total,
        chartDataCategories: state =>
            state.categories.data
                .map(({ category, sales, products, revenue }) => ({
                    category,
                    sales,
                    products,
                    revenue: Math.floor(revenue),
                }))
                .sort((a, b) => b['revenue'] - a['revenue'])
                .slice(0, 7)
                .map((item, index) => ({ ...item, backgroundColor: colors[index] })),

        chartDataSellers: state =>
            state.sellers.data
                .map(({ supplier_name, sales, revenue }, index) => ({
                    supplier_name,
                    sales,
                    revenue: Math.floor(revenue),
                }))
                .sort((a, b) => b['revenue'] - a['revenue'])
                .slice(0, 7)
                .map((item, index) => ({ ...item, backgroundColor: colors[index] })),
    },
    actions: {
        async loadBrands({ state, commit }, { type, params }) {
            params = {
                ...Object.fromEntries(params),
                brand: state.brand,
                start_date: state.selectedDates[0],
                end_date: state.selectedDates[1],
            };

            if (!types.includes(type)) {
                return;
            }

            if (state.lastQueryParam[type] !== JSON.stringify(params)) {
                commit('setLastQueryParam', [type, JSON.stringify(params)]);
            } else {
                return;
            }

            try {
                let total = null;
                let data = [];

                commit('setLoading', { field: type, value: true });

                const response = await this.$axios.get(`/api/an/v2/wb/statistic/brands/${type}?`, {
                    params,
                });

                if (Object.prototype.hasOwnProperty.call(await response.data.data, 'total')) {
                    data = await response.data.data.data;
                    total = Number(await response.data.data.total);
                } else {
                    data = await response.data.data;
                    total = data.length;
                }
                commit('setField', { field: type, value: { data, total } });
            } catch (e) {
                commit('setErrorsField', { type: type, value: e });
                this.$notify.create({
                    message: 'Ошибка получения данных по бренду',
                    type: 'negative',
                });
            } finally {
                commit('setField', { field: 'isBrandsAnalyticsLoading', value: false });
                commit('setLoading', { field: type, value: false });
            }
        },
        async loadUserParams({ state, commit }, type) {
            if (types.includes(type)) {
                try {
                    commit('setField', { field: 'isBrandsAnalyticsLoading', value: true });
                    const response = await this.$axios.get(
                        `/api/an/v1/user-params?url=api/v2/wb/statistic/brands/${type}`
                    );
                    const { data } = await response.data;

                    commit('setField', { field: 'brand', value: data.brand });
                    commit('setField', { field: 'userParams', value: data });
                } catch (e) {
                    this.$notify.create({
                        message: 'Ошибка загрузки параметров пользователя',
                        type: 'negative',
                    });
                } finally {
                    commit('setField', { field: 'isBrandsAnalyticsLoading', value: false });
                }
            }
        },
        async loadBrandList({ commit }, search) {
            if (search.length > 1) {
                try {
                    commit('setField', { field: 'isBrandsAnalyticsLoading', value: true });
                    const response = await this.$axios.get('/api/an/v1/wb/statistic/brands/find', {
                        params: {
                            filter: search,
                        },
                    });
                    const { data } = await response.data;
                    if (data && data.length === 0) {
                        this.$notify.create({
                            message: 'Нет такого бренда, выберите другой',
                            type: 'negative',
                        });
                    }
                    commit('setField', {
                        field: 'brandList',
                        value: Object.values(data).map(({ brand }) => ({ value: brand })),
                    });
                } catch (e) {
                    this.$notify.create({
                        message: 'Ошибка получения списка брендов',
                        type: 'negative',
                    });
                } finally {
                    commit('setField', { field: 'isBrandsAnalyticsLoading', value: false });
                }
            }
        },
        async loadBrandPrices({ commit, state }) {
            // TODO: Необходимо переписать компонент аналитики, чтобы избавиться от этого костыля
            if (!state.selectedDates.length) return;

            const params = {
                brand: state.brand,
                start_date: state.selectedDates[0],
                end_date: state.selectedDates[1],
                ...state.dataPrices,
            };
            if (state.lastQueryParam.prices !== JSON.stringify(params)) {
                commit('setLastQueryParam', ['prices', JSON.stringify(params)]);
            } else {
                return;
            }
            commit('setLoading', { field: 'prices', value: true });
            try {
                commit('setField', { field: 'isBrandPriceLoading', value: true });
                const topic = 'api/an/v2/wb/statistic/brands/price-analysis';
                const response = await this.$axios.get(topic, {
                    params,
                });
                const { data } = await response.data;
                commit('setField', { field: 'prices', value: data });
                if (!state.firstLoadingPrice) {
                    const minMax = {
                        min: Number(data[0].min_range),
                        max: Number(data[data.length - 1].max_range),
                    };
                    commit('setStartPriceRange', minMax);
                    commit('setDataPrices', minMax);
                }
                commit('setField', { field: 'firstLoadingPrice', value: true });
                commit('setLoading', { field: 'prices', value: false });
            } catch (e) {
                this.$notify.create({
                    message: 'Ошибка получения ценового анализа',
                    type: 'negative',
                });
            } finally {
                commit('setLoading', { field: 'prices', value: false });
            }
        },
    },
    mutations: {
        setField(state, { field, value }) {
            state[field] = value;
        },
        setErrorsField(state, { type, value }) {
            state.errors[type] = value;
        },
        setLoading(state, { field, value }) {
            state.isLoading[field] = value;
        },
        setLastQueryParam(state, value) {
            state.lastQueryParam[value[0]] = value[1];
        },
        setDataPrices(state, value) {
            Object.keys(value).forEach(key => {
                if (key in state.dataPrices) state.dataPrices[key] = value[key];
            });
        },
        setStartPriceRange(state, value) {
            Object.keys(value).forEach(key => {
                if (key in state.startPriceRange) state.startPriceRange[key] = value[key];
            });
        },
        setDefaultParams(state) {
            const fields = ['dataPrices', 'firstLoadingPrice', 'startPriceRange'];
            for (const key of fields) {
                state.defaultParams[key] = JSON.parse(JSON.stringify(state[key]));
            }
        },
        resetParams(state) {
            Object.keys(state.defaultParams).forEach(key => {
                state[key] = state.defaultParams[key];
            });
        },
    },
};
