import Vue from 'vue';

const initialState = {
    total: [],
    dataLoaded: false,
    dataEmpty: false,
    activeProductId: null,
    troublesSummary: [],
    activeProduct: {
        id: null,
        troubles: [],
        troubles_summary: {},
    },
    productRequestPending: false,
};

const actions = {
    async fetchTableData({ commit, rootGetters }) {
        const url = '/api/an/v1/wb/calls-for-action/get-data';
        commit('setStoreKey', { key: 'dataLoaded', payload: false });
        commit('setStoreKey', { key: 'dataEmpty', payload: false });
        try {
            const { data } =
                await this.$axios.$get(url);

            commit('setStoreKey', { key: 'dataLoaded', payload: true });
            if (data.length) {
                commit('setStoreKey', { key: 'total', payload: data });
                commit('setStoreKey', { key: 'activeProductId', payload: data[0].id });
            } else {
                commit('setStoreKey', { key: 'dataEmpty', payload: true });
            }
        } catch {
            const { userActiveAccounts } = rootGetters;
            if (userActiveAccounts.length) {
                this.$notify.create({
                    message: 'Произошла ошибка при попытке загрузки данных! Попробуйте перезагрузить страницу.',
                    type: 'negative',
                });
            }
        }
    },
    async fetchProductData({ commit, state}, id) {
        const url = '/api/an/v1/wb/calls-for-action/get-diagram-data/' + id;
        commit('setStoreKey', { key: 'productRequestPending', payload: true });
        try {
            const { data } =
                await this.$axios.$get(url);
            commit('setStoreKey', { key: 'activeProduct', payload: data });
            commit('setStoreKey', { key: 'activeProductId', payload: id });
            commit('setStoreKey', { key: 'productRequestPending', payload: false });
        } catch {
            commit('setStoreKey', { key: 'productRequestPending', payload: false });
            this.$notify.create({
                message: 'Произошла ошибка при попытке загрузки данных! Попробуйте перезагрузить страницу.',
                type: 'negative',
            });
        }
    },
};

const getters = {
    getTroublesSummary: state => state.activeProduct.troubles_summary,
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
    state: initialState,
    actions,
    getters,
    mutations,
};
