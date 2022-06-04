import { errorHandler } from '~utils/response.utils';
import Vue from 'vue';

const CATEGORIES_API = {
    ozon: {
        api: '/api/vp/v2/ozon-categories',
        params: 'name',
    },
    wildberries: {
        api: '/api/vp/v2/wildberries/categories',
        params: 'search',
    },
};

export default {
    namespaced: true,
    state: () => ({
        isResult: true,
        search: null,
        pending: false,
        selectedCategories: [],
        marketplace: {
            items: [],
        },
    }),
    getters: {
        GET_CATEGORIES(state) {
            return state.marketplace.items;
        },
        GET_PENDING(state) {
            return state.pending;
        },
        IS_RESULT(state) {
            return state.isResult;
        },
        GET_SELECTED_CATEGORIES(state) {
            return state.selectedCategories;
        },
        GET_SEARCH(state) {
            return state.search;
        },
    },
    mutations: {
        SET_CATEGORIES(state, data) {
            state.marketplace.items = data;
        },
        SET_SEARCH(state, data) {
            state.search = data || null;
        },
        SET_PENDING(state, data) {
            state.pending = data;
        },
        SET_IS_RESULT(state, data) {
            state.isResult = data;
        },
        REMOVE_CATEGORIES(state) {
            state.marketplace.items = [];
        },
        SET_STATE_KEY(state, data) {
            const target = data.target || state;
            Vue.set(target, data.key, data.value);
        },
        REMOVE_SEL_CAT(state) {
            state.selectedCategories = [];
        },
    },
    actions: {
        LOAD_CATEGORIES({ commit, state }, marketplace) {
            if (state.search) {
                commit('SET_PENDING', true);
                this.$axios
                    .$get(CATEGORIES_API[marketplace].api, {
                        params: {
                            [CATEGORIES_API[marketplace].params]: state.search,
                        },
                    })
                    .then(({ data }) => {
                        commit('SET_IS_RESULT', Boolean(data.length));
                        commit(
                            'SET_CATEGORIES',
                            data.map(item => {
                                const newItem = item;
                                const recursion = element => {
                                    if (element.parents) {
                                        recursion(element.parents);

                                        // eslint-disable-next-line no-param-reassign
                                        element.parents = [element.parents];
                                    }
                                    return element;
                                };

                                return recursion(newItem);
                            })
                        );
                    })
                    .catch(({ response }) => {
                        errorHandler(response, this.$notify);
                    })
                    .finally(() => {
                        commit('SET_PENDING', false);
                    });
            } else {
                commit('SET_IS_RESULT', true);
                commit('SET_CATEGORIES', []);
            }
        },
        async fetchCategories({ commit, rootGetters }) {
            commit('SET_PENDING', true);
            const urls = [
                '/api/vp/v2/ozon/account-categories',
                '/api/vp/v2/wildberries/account-categories',
            ];
            const topic = urls[rootGetters.isSelMpIndex];
            this.$axios
                .$get(topic)
                .then(({data: { data }}) => {
                    commit('SET_IS_RESULT', Boolean(data.length));
                    commit('SET_CATEGORIES', data);
                })
                .catch(({ response }) => {
                    errorHandler(response, this.$notify);
                })
                .finally(() => {
                    commit('SET_PENDING', false);
                });
        },
    },
};
