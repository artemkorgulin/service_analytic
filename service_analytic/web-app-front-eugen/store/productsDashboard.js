const dataApiUrl = '/api/vp/v2/dashboard/data';
const getStateType = type => type.split('_')[1];
const types = [
    'category_optimization',
    'category_revenue',
    'category_ordered',
    'brand_optimization',
    'brand_revenue',
    'brand_ordered',
    'product_optimization',
    'product_revenue',
    'product_ordered',
];

export default {
    namespaced: true,
    state: () => ({
        optimization: {},
        revenue: {},
        ordered: {},
        isLoading: {
            optimization: false,
            revenue: false,
            ordered: false,
        },
    }),
    actions: {
        async fetchDashboardData({ commit }, { type }) {
            if (types.includes(type)) {
                try {
                    commit('setLoading', { field: getStateType(type), value: true });
                    const response = await this.$axios.get(dataApiUrl, {
                        params: {
                            'query[dashboard_type]': type,
                        },
                    });
                    const { data } = response.data;
                    commit('setField', { field: getStateType(type), value: data });
                } catch (e) {
                    this.$notify.create({
                        message: 'Ошибка получения данных по сегментам',
                        type: 'negative',
                    });
                } finally {
                    commit('setLoading', { field: getStateType(type), value: false });
                }
            }
        },
    },
    mutations: {
        setField(state, { field, value }) {
            state[field] = value;
        },
        setLoading(state, { field, value }) {
            state.isLoading[field] = value;
        },
    },
};
