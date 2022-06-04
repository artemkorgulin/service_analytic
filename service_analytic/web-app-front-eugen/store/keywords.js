const initialState = {
    keywords: [],
    stopwords: [],
    keywordsCategories: {
        data: {},
        isFetched: false,
    },
    selectedKeywords: [],
    selectedStopwords: [],
};

export const mutations = {
    SET_KEYWORDS(state, payload) {
        state.keywords = payload;
    },
    SET_STOPWORDS(state, payload) {
        state.stopwords = payload;
    },
    SET_KEYWORDS_CATEGORIES(state, payload) {
        state.keywordsCategories.data = payload;
        state.keywordsCategories.isFetched = true;
    },

    SET_SELECTED_KEYWORDS(state, payload) {
        state.selectedKeywords = payload;
    },
    ADD_KEYWORD_TO_SELECTED(state, payload) {
        state.selectedKeywords.push(payload);
    },
    REMOVE_KEYWORD_FROM_SELECTED(state, payload) {
        state.selectedKeywords.splice(state.selectedKeywords.indexOf(payload), 1);
    },

    SET_SELECTED_STOPWORDS(state, payload) {
        state.selectedStopwords = payload;
    },
    ADD_STOPWORD_TO_SELECTED(state, payload) {
        state.selectedStopwords.push(payload);
    },
    REMOVE_STOPWORD_FROM_SELECTED(state, payload) {
        state.selectedStopwords.splice(state.selectedStopwords.indexOf(payload), 1);
    },
};
export const actions = {
    async toggleKeyword({ state, commit }, payload) {
        if (state.selectedKeywords.includes(payload)) {
            return commit('REMOVE_KEYWORD_FROM_SELECTED', payload);
        } else {
            return commit('ADD_KEYWORD_TO_SELECTED', payload);
        }
    },
    async toggleAllKeywords({ dispatch, getters }) {
        if (getters.isAllKeywordsSelected || getters.isAnyKeywordSelected) {
            return dispatch('unselectAllKeywords');
        } else {
            return dispatch('selectAllKeywords');
        }
    },
    selectAllKeywords({ commit, getters, state }) {
        return commit('SET_SELECTED_KEYWORDS', [...state.keywords]);
    },
    unselectAllKeywords({ commit }) {
        return commit('SET_SELECTED_KEYWORDS', []);
    },

    async toggleStopword({ state, commit }, payload) {
        if (state.selectedStopwords.includes(payload)) {
            return commit('REMOVE_STOPWORD_FROM_SELECTED', payload);
        } else {
            return commit('ADD_STOPWORD_TO_SELECTED', payload);
        }
    },
    async toggleAllStopwords({ dispatch, getters }) {
        if (getters.isAllStopwordsSelected || getters.isAnyStopwordSelected) {
            return dispatch('unselectAllStopwords');
        } else {
            return dispatch('selectAllStopwords');
        }
    },
    selectAllStopwords({ commit, getters, state }) {
        return commit('SET_SELECTED_STOPWORDS', [...state.stopwords]);
    },
    unselectAllStopwords({ commit }) {
        return commit('SET_SELECTED_STOPWORDS', []);
    },

    async fetchKeywordsCategories({ state, commit }) {
        if (state.keywordsCategories.isFetched) {
            return state.keywordsCategories.data;
        }
        try {
            const { data, success } = await this.$axios.$get('/api/adm/v1/categories');
            if (!success || !data?.length) {
                return new Error('[fetchKeywordsCategories] fetch failed');
            }
            await commit('SET_KEYWORDS_CATEGORIES', data);
            return data;
        } catch (error) {
            await this?.$sentry?.captureException(error);
            console.log('🚀 ~ file: adm.js ~ line 51 ~ fetchKeywordsCategories ~ error', error);
            throw new Error(error);
        }
    },
    async fetchKeywords({ commit, getters }) {
        try {
            const { data, success } = await this.$axios.$get(
                `/api/adm/v2/campaign/${getters.campaignId}/keywords`,
                {
                    params: getters.keywordsParams,
                }
            );
            if (!success || !data) {
                throw new Error('Ошибка при получении данных');
            }
            return commit('SET_KEYWORDS', data);
        } catch (error) {
            console.log('🚀 ~ file: keywords.js ~ line 142 ~ fetchKeywords ~ error', error);
            await this?.$sentry?.captureException(error);
            throw new Error(error);
        }
    },
    async fetchStopwords({ commit, getters }) {
        try {
            const { data, success } = await this.$axios.$get(
                `/api/adm/v2/campaign/${getters.campaignId}/stop-words`,
                {
                    params: getters.keywordsParams,
                }
            );
            if (!success || !data) {
                throw new Error('Ошибка при получении стоп слов');
            }
            return commit('SET_STOPWORDS', data);
        } catch (error) {
            console.log('🚀 ~ file: keywords.js ~ line 148 ~ fetchStopwords ~ error', error);
            await this?.$sentry?.captureException(error);
            throw new Error(error);
        }
    },
    setPicker({ getters }) {
        const pickerItems = getters.getPickerItems;
        if (!pickerItems.length) {
            return null;
        }
        const id = pickerItems[0].id ? String(pickerItems[0].id) : null;
        if (!id) {
            return null;
        }
        return id;
    },
    flushKeywordData({ commit }) {
        return Promise.all([
            commit('SET_STOPWORDS', []),
            commit('SET_KEYWORDS', []),
            commit('SET_SELECTED_KEYWORDS', []),
        ]);
    },
};

export const gettersModule = {
    getMinusWordsNames(state) {
        return state.stopwords.map(item => item.name);
    },
    pickedId(state, getters, rootState) {
        return rootState?.route?.query?.picked_id;
    },
    campaignId(state, getters, rootState) {
        return rootState?.route?.params?.id;
    },

    getPickerItems(state, getters, rootState, rootGetters) {
        const keywords = rootGetters['campaign/getCampaignGoods'] || [];
        const groups = rootGetters['campaign/getCampaignGroupsFiltered'] || [];
        return [...groups, ...keywords];
    },
    pickerElementsById(state, getters) {
        return getters.getPickerItems.reduce((acc, val) => {
            acc[String(val.id)] = val;
            return acc;
        }, {});
    },
    pickedElementObject(state, getters) {
        const id = getters.pickedId;
        if (!id) {
            return {};
        }
        return getters.pickerElementsById[id] || {};
    },
    keywordsParams(state, getters) {
        if (!getters.pickedElementObject) {
            return {};
        }
        return {
            [getters.pickedElementObject.isGroup ? 'group_id' : 'campaign_good_id']:
                getters.pickedElementObject.id,
        };
    },

    isAnyKeywordSelected(state) {
        return state.selectedKeywords?.length > 0;
    },
    isAllKeywordsSelected(state, getters) {
        if (!getters.isSelectAllKeywordsEnabled) {
            return false;
        }
        return state.selectedKeywords.length === state.keywords.length;
    },
    isSelectAllKeywordsEnabled(state, getters) {
        if (getters.isAnyKeywordSelected) {
            return true;
        }
        return Boolean(state.keywords?.length);
    },
    selectedReducedById(state) {
        return state.selectedKeywords.reduce((acc, val) => {
            acc[val.id] = val;
            return acc;
        }, {});
    },
    selectedIds(state, getters) {
        return Object.keys(getters.selectedReducedById);
    },

    isAnyStopwordSelected(state) {
        return state.selectedStopwords?.length > 0;
    },
    isAllStopwordsSelected(state, getters) {
        if (!getters.isSelectAllStopwordsEnabled) {
            return false;
        }
        return state.selectedStopwords.length === state.stopwords.length;
    },
    isSelectAllStopwordsEnabled(state, getters) {
        if (getters.isAnyStopwordSelected) {
            return true;
        }
        return Boolean(state.stopwords?.length);
    },
    selectedStopwordsReducedById(state) {
        return state.selectedStopwords.reduce((acc, val) => {
            acc[val.id] = val;
            return acc;
        }, {});
    },
    selectedStopwordsIds(state, getters) {
        return Object.keys(getters.selectedStopwordsReducedById);
    },
};
export default {
    state: () => initialState,
    actions,
    getters: gettersModule,
    mutations,
};
