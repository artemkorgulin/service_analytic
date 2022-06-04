import { errorHandler } from '~utils/response.utils';

export default {
    namespaced: true,
    state: () => ({
        marketplace: {
            positions: [],
            sales: [],
            search_request: [],
            statistics: {},
        },
        isAnalyticsLoading: false,
        datesStartEnd: [],
    }),
    actions: {
        async fetchDataChartWB({ commit }, { sku, params }) {
            try {
                commit('setField', { field: 'isAnalyticsLoading', value: true });
                const response = await this.$axios.get(`/api/an/v1/wb/detail/products/${sku}`, {
                    params: params,
                });
                const { data } = await response.data;
                commit('setMarketplaceField', { field: 'statistics', value: data[0] });
                commit('setMarketplaceField', { field: 'positions', value: data[0]?.positions });
                commit('setMarketplaceField', { field: 'sales', value: data[0]?.graphs });
                commit('setMarketplaceField', {
                    field: 'search_request',
                    value: data[0]?.search_request,
                });
            } catch (e) {
                errorHandler(e.message, this.$notify);
            } finally {
                commit('setField', { field: 'isAnalyticsLoading', value: false });
            }
        },
        async fetchDataChartPositionsOzon({ commit }, { id, params }) {
            if (isNaN(id)) {
                return;
            }
            try {
                commit('setField', { field: 'isAnalyticsLoading', value: true });
                const response = await this.$axios.get(
                    `/api/vp/v2/ozon/get-positions-history?product_id=${id}`,
                    {
                        params: params,
                    }
                );
                const { data } = await response.data;
                commit('setMarketplaceField', { field: 'positions', value: data[0] });
                commit('setMarketplaceField', { field: 'search_request', value: data[0] });
            } catch (e) {
                errorHandler(e.message, this.$notify);
            } finally {
                commit('setField', { field: 'isAnalyticsLoading', value: false });
            }
        },
        async fetchDataChartSalesOzon({ commit }, { id, params }) {
            if (isNaN(id)) {
                return;
            }
            try {
                const response = await this.$axios.get(
                    `/api/vp/v2/ozon/get-analytics-data?product_id=${id}`,
                    {
                        params: params,
                    }
                );
                const { data } = await response.data;
                commit('setMarketplaceField', { field: 'sales', value: data[0] });
            } catch (e) {
                errorHandler(e.message, this.$notify);
            }
        },
    },
    mutations: {
        setField(state, { field, value }) {
            state[field] = value;
        },
        setMarketplaceField(state, { field, value }) {
            state.marketplace[field] = value;
        },
    },
};
