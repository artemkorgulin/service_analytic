import { isEqual, sortBy } from 'lodash';
import { errorHandler } from '~utils/response.utils';
import { productsParse } from '~utils/products-parse.utils';

export default {
    namespaced: true,
    state: () => ({
        perPage: 25,
        pageLoading: true,
        nameSortField: '',
        sortLoading: false,
        sortTypeCounter: 1,
        search: null,
        categoryId: null,
        selectedProducts: [],
        activeDeleteBtn: true,
        newProduct: {
            params: null,
            category: null,
        },
        sort: {
            sortBy: 'id',
            sortType: 'desc',
        },
        list: {
            pending: {
                type: 'common',
                show: false,
            },
            items: [],
            total: 0,
            deletedCount: 0,
            perPage: 10,
            page: 1,
            lastPage: 1,
            timestamp: new Date(),
            itemsSelected: [],
            displayOptionBig: false,
        },
        dashboardFilter: {
            optimization: false,
            revenue: false,
            ordered: false,
        },
    }),
    getters: {
        getTotalProducts: state => state.list.total,
        getDeletedProducts: state => state.list.deletedCount,
        GET_PRODUCTS(state) {
            return state.list;
        },
        GET_PENDING(state) {
            return state.list.pending;
        },
        GET_SORT(state) {
            return state.sort;
        },
        IS_SEARCH_AND_FILTER(state) {
            return Boolean(state.search || state.categoryId);
        },
        GET_NEW_PRODUCT(state) {
            return state.newProduct;
        },
        GET_ITEMS_SELECTED(state) {
            return state.list.itemsSelected;
        },
        IS_SELECT_ALL(state) {
            if (!state?.list?.items?.length) {
                return false;
            }
            const selectIDs = state.list.items.map(item => item.id);
            return isEqual(sortBy(state.list.itemsSelected), sortBy(selectIDs));
        },
        GET_CURRENT_ITEMS(state) {
            return state.list.items;
        },
        GET_DISPLAY_OPTION(state) {
            return state.list.displayOptionBig;
        },
    },
    mutations: {
        setField(state, { value, field }) {
            state[field] = value;
        },
        setFieldArray(state, value) {
            state[value[0]] = value[1];
        },

        addRemSelectedProducts(state, id) {
            if (!id) {
                state.selectedProducts = [];
                state.activeDeleteBtn = true;
                return;
            }

            const index = state.selectedProducts.indexOf(id);
            if (index > -1) {
                state.selectedProducts.splice(index, 1);
            } else {
                state.selectedProducts.push(id);
            }
            state.activeDeleteBtn = !state.selectedProducts.length;
        },
        setDashboardFilter(state, { value, field }) {
            state.dashboardFilter[field] = value;
        },
        setResetDashboardFilters(state) {
            state.dashboardFilter.optimization = false;
            state.dashboardFilter.revenue = false;
            state.dashboardFilter.ordered = false;
        },
        SET_PRODUCTS(state, payload) {
            try {
                const data = productsParse(payload);

                state.list.items = data.items;
                state.list.total = data.total;
                state.list.deletedCount = data.deletedCount || data.deletedProducts || 0;
                // state.list.perPage;
                state.list.lastPage = data.last_page;
                state.list.page = data.current_page;
                state.list.timestamp = new Date();
            } catch (error) {
                console.error(error);
            }
        },
        SET_SORT(state, { sortBy, sortType }) {
            state.sort.sortBy = sortBy;
            state.sort.sortType = sortType;

            state.list.page = 1;
        },
        CHANGE_PAGE(state, data) {
            state.list.page = data;
        },
        CHANGE_PER_PAGE(state, data) {
            state.list.perPage = data;
        },
        SET_SEARCH(state, data) {
            state.search = data;
            state.list.page = 1;
        },
        SET_PENDING(state, data) {
            state.list.pending.type = data.type;
            state.list.pending.show = data.show;
        },
        SET_CATEGORY(state, data) {
            state.categoryId = data.length ? data : null;
            state.list.page = 1;
        },
        SET_NEW_PRODUCT_PARAMS(state, data) {
            state.newProduct.params = data;
        },
        SET_NEW_PRODUCT_CATEGORY(state, data) {
            state.newProduct.category = data;
        },
        ADD_SELECTED_PRODUCT(state, data) {
            state.list.itemsSelected.push(data);
        },
        REMOVE_SELECTED_PRODUCT(state, data) {
            state.list.itemsSelected.splice(data, 1);
        },
        SET_SELECT_NONE(state) {
            state.list.itemsSelected.splice(0);
        },
        SET_SELECT_ALL(state, data) {
            state.list.itemsSelected.splice(0);
            data.forEach(item => state.list.itemsSelected.push(item));
        },
        SET_DISPLAY_OPTION_MUTATE(state, data) {
            state.list.displayOptionBig = data;
        },
    },
    actions: {
        async LOAD_PRODUCTS({ commit, state, rootGetters }, options) {
            /* eslint-disable */
            const { isSelMpIndex } = rootGetters;
            const topic = ['/api/vp/v2/products', '/api/vp/v2/wildberries/products'][isSelMpIndex];

            if (options.reload) {
                commit('SET_SEARCH', null);
                commit('SET_SORT', {
                    sortBy: 'id',
                    sortType: 'desc',
                });
            }

            const { page } = state.list;
            const searchQuery = state.search === -1 ? null : state.search;

            return new Promise((resolve, reject) => {
                this.$axios
                    .$get(topic, {
                        params: {
                            per_page: state.perPage,
                            page,
                            search: searchQuery,
                            categoryId: state.categoryId,
                            ...state.sort,
                        },
                    })
                    .then(response => {
                        commit('SET_SEARCH', searchQuery);
                        commit('SET_PRODUCTS', {
                            data: response,
                            marketplace: options.marketplace,
                        });
                        resolve();
                    })
                    .catch(({ response }) => {
                        if (response && response.status === 403) {
                            throw 'no added accounts';
                        } else {
                            const { data } = errorHandler(response, this.$notify);
                            throw new Error(data.title || 'Произошла ошибка');
                        }
                    })
                    .finally(() => {
                        commit('setField', {
                            field: 'dashboardFilter',
                            value: {
                                optimization: false,
                                revenue: false,
                                ordered: false,
                            },
                        });

                        commit('SET_PENDING', {
                            type: options.type,
                            show: false,
                        });
                    });
            });
        },
        LOAD_NEW_PRODUCT_PARAMS({ commit, state }, payload = null) {
            let endpoint;

            switch (payload) {
                case 'wildberries':
                    endpoint = '/api/vp/v2/wildberries/categories/' + state.newProduct.category;
                    break;
                default:
                    endpoint = '/api/vp/v2/create-product/' + state.newProduct.category;
            }

            this.$axios
                .$get(endpoint)
                .then(res => {
                    commit('SET_NEW_PRODUCT_PARAMS', res);
                })
                .catch(({ response }) => {
                    errorHandler(response, this.$notify);
                });
        },
        SELECT_PRODUCT({ commit }, prodID) {
            commit('ADD_SELECTED_PRODUCT', prodID);
        },
        DESELECT_PRODUCT({ getters, commit }, prodID) {
            const selectedItems = getters.GET_ITEMS_SELECTED;
            const index = selectedItems.indexOf(prodID);

            if (index > -1) {
                commit('REMOVE_SELECTED_PRODUCT', index);
            }
        },
        SELECT_ALL_PRODUCTS({ getters, commit }) {
            const selectAll = getters.IS_SELECT_ALL;

            if (selectAll) {
                commit('SET_SELECT_NONE');
            } else {
                const items = getters.GET_CURRENT_ITEMS;
                const selectIDs = items.map(item => item.id);
                commit('SET_SELECT_ALL', selectIDs);
            }
        },
        SET_DISPLAY_OPTION({ commit }, option = true) {
            commit('SET_DISPLAY_OPTION_MUTATE', option);
        },
        async loadFilteredDashboardData(
            { commit, rootGetters },
            { dashboard, segment, marketplace }
        ) {
            const { isSelMpIndex } = rootGetters;
            try {
                const dashBoardType = dashboard.split('_')[1];
                commit('SET_PENDING', {
                    type: 'common',
                    show: true,
                });
                const topic = [
                    'api/vp/v2/ozon/dashboard/filter-products',
                    'api/vp/v2/wildberries/dashboard/filter-products',
                ][isSelMpIndex];
                const response = await this.$axios.get(topic, {
                    params: {
                        'query[dashboard_type]': dashboard,
                        'query[segment_type]': segment,
                    },
                });

                if (response.status === 200) {
                    commit('setResetDashboardFilters');
                    commit('setDashboardFilter', { value: true, field: dashBoardType });
                }

                commit('SET_PRODUCTS', {
                    data: marketplace === 'ozon' ? response.data : response.data.data,
                    marketplace,
                });
            } catch ({ response }) {
                this.$notify.create({
                    message: 'Ошибка фильтрации данных по сегментам',
                    type: 'negative',
                });
            } finally {
                commit('SET_PENDING', {
                    type: 'common',
                    show: false,
                });
            }
        },
    },
};
