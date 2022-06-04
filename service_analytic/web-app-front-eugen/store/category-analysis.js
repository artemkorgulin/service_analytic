/* eslint-disable  no-unused-vars, no-extra-parens */

import Vue from 'vue';
import { sortTableData, sortPinnedBottomData, getSubjectsAndBrands, getValuesRange, getCategoryAnalysisParams } from '/store/helpers/category-analysis';

const initialState = {
    brandsData: null,
    periods: [
        { label: '1 день', value: 'yesterday' },
        { label: '7 дней', value: 'week' },
        { label: '30 дней', value: 'month' },
        { label: '90 дней', value: 'quarter' },
        { label: '365 дней', value: 'year' },
    ],
    selectedPeriod: null,
    subjectsTotal: [],
    subjectsTable: [],
    brandsTotal: [],
    brandsTable: [],
    brandsFrontend: [],
    columnDefs: null,
    tableData: null,
    userSearchParams: null,
    selectedTab: 0,
    tabItems: [
        {
            title: 'Количество SKU',
            sort: 'sku_count',
        },
        {
            title: 'Поставщики',
            sort: 'suppliers_count',
        },
        {
            title: 'Выручка на SKU',
            sort: 'avg_take',
        },
    ],
    valuesRange: {
        max: {
            sku_count: 0,
            suppliers_count: 0,
            avg_take: 0,
        },
        min: {
            sku_count: 0,
            suppliers_count: 0,
            avg_take: 0,
        },
    },
    pinnedBottomData: {},
};

const actions = {
    async fetchCategoryAnalysis({ commit, dispatch, state }, payload) {
        const { params, brands } = payload;
        const url = '/api/an/v1/wb/statistic/category/analysis?' + params;
        try {
            const { data } = await this.$axios.$get(url);
            const { subjectsTotal, brandsTotal } = getSubjectsAndBrands(data, brands);
            const valuesRange = getValuesRange(data);
            const { tableData } = sortTableData(data, subjectsTotal, brandsTotal);
            const pinnedBottomData = sortPinnedBottomData(data, brandsTotal);
            commit('setStoreKey', { key: 'subjectsTotal', payload: subjectsTotal });
            commit('setStoreKey', { key: 'subjectsTable', payload: subjectsTotal });
            commit('setStoreKey', { key: 'brandsTotal', payload: brandsTotal });
            commit('setStoreKey', { key: 'brandsTable', payload: brandsTotal });
            commit('setStoreKey', { key: 'tableData', payload: tableData });
            commit('setStoreKey', { key: 'brandsData', payload: data });
            commit('setStoreKey', { key: 'valuesRange', payload: valuesRange });
            commit('setStoreKey', { key: 'pinnedBottomData', payload: pinnedBottomData });
        } catch {
            this.$notify.create({
                message: 'Произошла ошибка при попытке загрузки данных! Попробуйте перезагрузить страницу.',
                type: 'negative',
            });
        }
    },
    async fetchUserSearchParams({ commit }, payload) {
        return new Promise((resolve, reject) => {
            this.$axios
                .$get('/api/an/v1/user-params?url=api/v1/wb/statistic/category/analysis')
                .then(({ data }) => {
                    const endDefault = new Date();
                    const startDefault = new Date(endDefault);
                    startDefault.setDate(startDefault.getDate() - 7);

                    const brands = payload?.length ? payload : data.brands || [6364, 5772, 6049];
                    const start_date = startDefault;
                    const end_date = endDefault;
                    commit('setStoreKey', {
                        key: 'userSearchParams',
                        payload: { brands, end_date, start_date },
                    });
                    resolve({ brands, end_date, start_date });
                }).catch(() => {
                this.$notify.create({
                    message: 'Не удалось загрузить сохраненные параметры поиска. Попробуйте перезагрузить страницу.',
                    type: 'negative',
                });
            });
        });
    },
    updateBrandsTable({ commit, state, dispatch }, payload) {
        commit('setStoreArrayElement', {
            target: state.brandsTable,
            index: payload.index,
            value: {
                brand_id: payload.brand_id,
            },
        });

        commit('setStoreArrayElement', {
            target: state.brandsFrontend,
            index: payload.index,
            value: payload.brand_id,
        });

        const params = getCategoryAnalysisParams(state);
        dispatch('fetchCategoryAnalysis', params);
    },
    removeBrand({ commit, state, dispatch }, payload) {
        commit('removeStoreArrayElement', {
            target: state.brandsTable,
            index: payload.index,
        });

        commit('removeStoreArrayElement', {
            target: state.brandsFrontend,
            index: payload.index,
        });

        if (state.brandsTable.length) {
            const params = getCategoryAnalysisParams(state);
            dispatch('fetchCategoryAnalysis', params);
        }
    },
};

const getters = {
    getSelectedTabName: state => state.tabItems[state.selectedTab].sort,
};

const mutations = {
    setStoreKey(state, data) {
        state[data.key] = data.payload;
    },
    setStoreArrayElement(state, payload) {
        Vue.set(payload.target, [payload.index], payload.value);
    },
    removeStoreArrayElement(state, payload) {
        payload.target.splice(payload.index, 1);
    },
};

export default {
    namespaced: true,
    state: () => initialState,
    actions,
    getters,
    mutations,
};
