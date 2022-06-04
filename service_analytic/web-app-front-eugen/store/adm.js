const initialState = {
    filters: {
        data: {},
        isFetched: false,
    },
    campaigns: {
        data: {
            counters: {},
            pageData: {},
            items: [],
        },
        isFetched: false,
        isLoading: false,
    },
};

export const actions = {
    async fetchStatistic(_, payload) {
        try {
            const {
                data: { statistics: items, total_statistic: counters },
            } = await this.$axios.$get('/api/adm/v2/campaigns-statistic', payload);
            return { items, counters };
        } catch (error) {
            console.log('🚀 ~ file: campaign.js ~ line 22 ~ fetchStatistic ~ error', error);
        }
    },
    async fetchFilters({ state, commit }) {
        if (state.filters.isFetched) {
            return {
                ok: true,
                data: state.filters.data,
            };
        }
        try {
            const { success, data } = await this.$axios.$get('/api/adm/v2/campaign-filters');
            if (!success || !data) {
                console.log('🚀 ~ file: adm.js ~ line 34 ~ fetchFilters ~ data', data);
                throw new Error('Не удалось получить данные фильтров');
            }
            await commit('SET_FILTERS', data);
            return data.data;
        } catch (error) {
            await this?.$sentry?.captureException(error);
            throw new Error(error);
        }
    },
    async searchCampaings({ commit }, payload) {
        try {
            await commit('SET_CAMPAIGNS_LOADING', true);
            const response = await this.$axios.$get('/api/adm/v2/campaigns-search', {
                params: {
                    search: payload,
                },
            });
            await commit('SET_CAMPAIGNS_LOADING', false);
            const { errors, success, data } = response;
            if (!success || !data) {
                throw errors || 'Произошла ошибка при поиске по РК';
            }
            // console.log('🚀 ~ file: adm.js ~ line 44 ~ searchCampaings ~ response', response);
            await commit('SET_CAMPAIGNS_SEARCH', data);
            // console.log('🚀 ~ file: brand.vue ~ line 360 ~ searchAdm ~ response', response);
        } catch (error) {
            console.log('🚀 ~ file: brand.vue ~ line 403 ~ searchAdm ~ error', error);
        }
    },
    async fetchCampaignsData({ commit, state }, params) {
        try {
            const response = await this.$axios.$get('/api/adm/v2/campaigns', {
                params,
            });
            const { errors, success, data } = response;

            if (!success || !data) {
                throw errors || 'Произошла ошибка при получении данных РК';
            }
            const { campaigns, total_statistic: statistic } = data;
            await commit('SET_CAMPAIGNS', { campaigns, statistic });
            return data.campaigns;
        } catch (error) {
            await this?.$sentry?.captureException(error);
            throw new Error(error);
        }
    },
    async fetchCampaignsDataMore({ state, commit }, payload) {
        try {
            await commit('SET_CAMPAIGNS_LOADING', true);
            const data = await this.$axios.$get('/api/adm/v2/campaigns', {
                params: {
                    page: state.campaigns.data.pageData.current_page + 1,
                    ...payload,
                },
            });
            // console.log('🚀 ~ file: adm.js ~ line 77 ~ fetchCampaignsDataMore ~ data', data);
            await commit('SET_CAMPAIGNS_LOADING', false);
            if (!data?.success || !data?.data?.campaigns) {
                throw new Error('Не удалось получить данные рекламных компаний');
            }
            await commit('SET_CAMPAIGNS_MORE', data.data.campaigns);
            return {
                ok: true,
                data: data.data.campaigns,
            };
        } catch (error) {
            await this?.$sentry?.captureException(error);
            console.log('🚀 ~ file: adm.js ~ line 78 ~ fetchCampaignsDataMore ~ error', error);

            return {
                ok: false,
                data: error,
            };
        }
    },
};

export const mutations = {
    SET_CAMPAIGNS_LOADING(state, payload) {
        state.campaigns.isLoading = payload;
    },
    SET_FILTERS(state, payload) {
        state.filters.data = payload;
        state.filters.isFetched = true;
    },
    SET_CAMPAIGNS_SEARCH(state, data) {
        state.campaigns.data.pageData = {};
        state.campaigns.data.items = data;
        // state.campaigns.data.counters = statistic;
    },
    SET_CAMPAIGNS(state, { campaigns, statistic }) {
        const { data: items, ...pageData } = campaigns;
        // state.campaigns.isFetched = true;
        state.campaigns.data.pageData = pageData;
        state.campaigns.data.items = items;
        state.campaigns.data.counters = statistic;
    },
    SET_CAMPAIGNS_MORE(state, { data, ...pageData }) {
        state.campaigns.data.pageData = pageData;
        state.campaigns.data.items = [...state.campaigns.data.items, ...data];
    },
};

export const gettersModule = {
    getCampaigns: state => {
        if (!state?.campaigns?.data?.items?.length) {
            return [];
        }
        const strategyStatuses = { 1: 'Активна', 2: 'Неактивна' };
        const strategies = { 1: 'Оптимальное количество показов', 2: 'Оптимизация по CPO' };
        return state.campaigns.data.items.map(item => {
            item.payment_type_name = item.payment_type?.name;
            item.placement_name = item.placement?.description;
            item.status_name = item.campaign_status?.name;
            item.status_id = item.campaign_status.id;
            item.strategy_status_name = item?.strategy?.strategy_status_id
                ? strategyStatuses[item.strategy.strategy_status_id]
                : '-';
            item.strategy_name = item?.strategy?.strategy_type_id
                ? strategies[item.strategy.strategy_type_id]
                : '-';
            item.popularity = item.sum_statistics.popularity;
            item.shows = item.sum_statistics.shows;
            item.purchased_shows = item.sum_statistics.purchased_shows;
            item.avg_1000_shows_price = item.sum_statistics.avg_1000_shows_price;
            item.clicks = item.sum_statistics.clicks;
            item.ctr = item.sum_statistics?.ctr; // .toFixed(2);
            item.avg_click_price = item.sum_statistics.avg_click_price;
            item.orders = item.sum_statistics.popularity;
            item.profit = item.sum_statistics.profit;
            item.cost = item.sum_statistics.cost;
            item.cpo = item.sum_statistics.cpo;
            item.drr = item.sum_statistics.drr;
            return item;
        });
    },
    isCampaignsReachEnd: state =>
        !state.campaigns.data.pageData?.next_page_url ||
        state.campaigns.data.pageData.current_page === state.campaigns.data.pageData?.last_page,
};
export default {
    state: () => initialState,
    actions,
    getters: gettersModule,
    mutations,
};
