const initialState = {
    campaign: {
        data: {},
    },
    goods: [],
    groups: [],
    selectedGoods: [],
};

function transformItems(payload) {
    if (!payload) {
        return [];
    }
    const copy = [...payload];
    const transformed = copy.map(item => {
        if (!item?.date) {
            return item;
        }
        item.date = item?.date?.split('.').reverse().join('-');
        item.date = new Date(item.date);
        return item;
    });
    return transformed.sort((a, b) => a.date - b.date);
}

const actions = {
    async fetchStatistic({ commit }, payload) {
        try {
            const response = await this.$axios.$get('/api/adm/v1/get-analytics-list', payload);
            const {
                data: {
                    campaigns: { data, counters, campaign, ...pageData },
                },
            } = response;

            const items = transformItems(data);
            return {
                items,
                counters,
                campaign,
                pageData,
            };
        } catch (error) {
            console.log('🚀 ~ file: campaign.js ~ line 22 ~ fetchStatistic ~ error', error);
        }
    },
    async fetchCampaignData({ commit }, id) {
        try {
            const { data, success, errors } = await this.$axios.$get(`/api/adm/v2/campaigns/${id}`);
            if (!success || !data) {
                throw errors || 'Произошла ошибка при получении данных РК';
            }
            await commit('SET_COMPAING_DATA', data);
            return data;
        } catch (error) {
            await this?.$sentry?.captureException(error);

            throw new Error(error);
        }
    },
    async fetchCampaignDataAndGoods({ commit }, id) {
        try {
            const { data, success } = await this.$axios.$get('/api/adm/v1/campaign/goods', {
                params: {
                    campaign_id: id,
                },
            });
            const {
                data: { groupList: groups },
            } = await this.$axios.$get(`/api/adm/v2/campaign/${id}/groups`);
            if (!success || !data) {
                return new Error('[fetchCampaignDataAndGoods] fetch success false');
            }
            const { campaign_good_list: goods, campaign } = data; // groups, group_goods: groupsGoods
            if (!campaign) {
                return new Error('[fetchCampaignDataAndGoods] no campaign data');
            }
            await commit('SET_COMPAING_GOODS', { goods, groups });
            return { goods, groups };
        } catch (error) {
            await this?.$sentry?.captureException(error);
            throw new Error(error);
        }
    },
    async toggleGood({ state, commit }, payload) {
        if (state.selectedGoods.includes(payload)) {
            commit('REMOVE_GOOD_FROM_SELECTED', payload);
        } else {
            commit('ADD_GOOD_TO_SELECTED', payload);
        }
    },
    async toggleAllGoods({ dispatch, getters }) {
        if (getters.isAllGoodsSelected || getters.isAnyGoodSelected) {
            dispatch('unselectAllGoods');
        } else {
            dispatch('selectAllGoods');
        }
    },
    selectAllGoods({ commit, getters }) {
        commit('SET_SELECTED_GOODS', getters.getCompaignGoodsWithGroups);
    },
    unselectAllGoods({ commit }) {
        commit('SET_SELECTED_GOODS', []);
    },
    unsetData({ commit }) {
        return Promise.all([
            commit('SET_SELECTED_GOODS', { goods: [], groups: [] }),
            commit('SET_COMPAING_DATA', {}),
            commit('SET_SELECTED_GOODS', []),
        ]);
    },
};

const mutations = {
    SET_COMPAING_GOODS(state, { goods, groups }) {
        state.goods = goods;
        state.groups = groups;
    },
    SET_COMPAING_DATA(state, payload) {
        state.campaign.data = payload;
    },
    SET_SELECTED_GOODS(state, payload) {
        state.selectedGoods = payload;
    },
    ADD_GOOD_TO_SELECTED(state, payload) {
        state.selectedGoods.push(payload);
    },
    REMOVE_GOOD_FROM_SELECTED(state, payload) {
        state.selectedGoods.splice(state.selectedGoods.indexOf(payload), 1);
    },
};

const gettersModule = {
    getCampaignGoods: state => state.goods,
    getCampaignGroups: state => state.groups,
    getCampaignGroupsFiltered: state =>
        state.groups.map(item => {
            item.isGroup = true;
            return item;
        }),
    getCampaignData: state => state.campaign.data,
    getIsCampaignGoodsEmpty: state => !state.goods.length && !state.groups.length,
    getCompaignGoodsWithGroups: state => {
        const result = [];
        if (state?.groups?.length) {
            const groups = state.groups.reduce((acc, val) => {
                acc.push({ ...val, isGroup: true });
                return acc;
            }, []);
            result.push(...groups);
        }
        if (state?.goods?.length) {
            result.push(...state.goods);
        }
        return result;
    },
    isSelectAllGoodsEnabled(state, getters) {
        if (getters.isAnyGoodSelected) {
            return true;
        }
        return Boolean(getters.getCompaignGoodsWithGroups?.length);
    },
    isAnyGoodSelected(state) {
        return state.selectedGoods?.length > 0;
    },
    isAllGoodsSelected(state, getters) {
        if (!getters.isSelectAllGoodsEnabled) {
            return false;
        }
        return getters.getCompaignGoodsWithGroups.every(item => state.selectedGoods.includes(item));
    },
};
export default {
    state: () => initialState,
    actions,
    getters: gettersModule,
    mutations,
};
