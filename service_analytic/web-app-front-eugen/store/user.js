const initialState = {
    isMenuExpanded: false,
    isAccountSettingsMenuExpanded: false,
    isMarketplaceSettingsMenuExpanded: false,
    companies: [],
    welcomeGuideShown: false,
    currentEditAccountData: {},
    needToShowPhoneModal: false,
};

const mutations = {
    SET_MENU_STATE(state, payload) {
        state.isMenuExpanded = payload;
    },
    SET_ACCOUNT_SETTINGS_MENU_STATE(state, payload) {
        state.isAccountSettingsMenuExpanded = payload;
    },
    SET_MARKETPLACE_SETTINGS_MENU_STATE(state, payload) {
        state.isMarketplaceSettingsMenuExpanded = payload;
    },
    SET_COMPANIES(state, payload) {
        state.companies = payload;
    },
    SET_WELCOME_GUIDE_SHOWN(state, payload) {
        state.welcomeGuideShown = payload;
    },
    SET_CURRENT_EDIT_ACCOUNT_DATA(state, payload) {
        state.currentEditAccountData = payload;
    },
    setField(state, { field, value }) {
        state[field] = value;
    },
};
const actions = {
    setMenuState({ commit }, { val, bur = false }) {
        if (bur) {
            this.$cookies.set('menustate', val, {
                path: '/',
                maxAge: 60 * 60 * 24 * 365,
            });
        }

        return commit('SET_MENU_STATE', val);
    },
    changeAccountSettingsMenuState({ commit }, val) {
        return commit('SET_ACCOUNT_SETTINGS_MENU_STATE', val);
    },
    setAccountSettingsMenuState({ commit }, val) {
        return commit('SET_ACCOUNT_SETTINGS_MENU_STATE', val);
    },
    setMarketplaceSettingsMenuState({ commit }, val) {
        return commit('SET_MARKETPLACE_SETTINGS_MENU_STATE', val);
    },
    async fetchCompanies({ commit }) {
        try {
            const { data } = await this.$axios.$get('/api/v1/company');
            commit('SET_COMPANIES', data);
            return data;
        } catch (error) {
            console.log('🚀 ~ file: company.vue ~ line 20 ~ fetchCompanies ~ error', error);
        }
    },
    setWelcomeGuideShown({ commit }, val) {
        return commit('SET_WELCOME_GUIDE_SHOWN', val);
    },
};
const gettersModule = {
    // activeAccounts: (state, getters, rootState) => {
    //     if (!rootState?.auth?.user?.accounts?.length) {
    //         return [];
    //     }
    //     return rootState.auth.user.accounts.filter(item => item?.is_active);
    // },

    // ozonSellerAccounts: (state, getters) => {
    //     if (!getters?.activeAccounts?.length) {
    //         return false;
    //     }
    //     return getters.activeAccounts.filter(item => item.platform_id === 1);
    // },
    // ozonSellerSelectedAccount: (state, getters) => {
    //     const activeAccounts = getters.ozonPerfomanceAccounts;
    //     if (!activeAccounts) {
    //         return false;
    //     }
    //     const acc = getters.ozonSellerAccounts.find(item => Boolean(item?.pivot?.is_selected));
    //     let result;
    //     if (!acc) {
    //         console.log('SellerSelectedAccount NO SELECTED (PIVOT) ACCOUNT');
    //         result = first(getters.ozonSellerAccounts);
    //     } else {
    //         result = acc;
    //     }
    //     return result;
    // },
    // ozonPerfomanceAccounts: (state, getters) => {
    //     if (!getters?.activeAccounts?.length) {
    //         return false;
    //     }
    //     return getters.activeAccounts.filter(item => item.platform_id === 2);
    // },
    // ozonPerfomanceSelectedAccount: (state, getters) => {
    //     const activeAccounts = getters.ozonPerfomanceAccounts;
    //     if (!activeAccounts) {
    //         return false;
    //     }
    //     const findSelected = activeAccounts.find(item => Boolean(item?.pivot?.is_selected));
    //     if (activeAccounts.length && !findSelected) {
    //         console.log('NO SELECTED OZON PERFOMANCE ACC IN BACKEND');
    //         // TODO REPLACE THIS SHIT
    //         // return activeAccounts[0];
    //     }
    //     return findSelected;
    // },
    // filteredMenuItems: (state, getters, rootState, rootGetters) => {
    //     const selectedMarketplaceSlug = rootGetters.getSelectedMarketplaceSlug;
    //     if (!selectedMarketplaceSlug) {
    //         console.warn(
    //             '🚀 ~ file: user.js ~ line 167 ~ NO selectedMarketplaceSlug',
    //             selectedMarketplaceSlug
    //         );
    //     }
    //     // const isSellerAccount = getters.ozonSellerSelectedAccount;
    //     // const isPerfomanceAccount = getters.ozonPerfomanceSelectedAccount;
    //     return state.menuItems.reduce((acc, val) => {
    //         // const value = Object.assign({}, val);

    //         const value = {
    //             ...val,
    //         };
    //         if (value.routeObject && selectedMarketplaceSlug) {
    //             value.routeObject.marketplace = selectedMarketplaceSlug;
    //         }
    //         acc.push(value);
    //         return acc;
    //     }, []);
    // },
    isTariffPaid() {
        return true;
    },
    getExpandedState(state) {
        return state.isMenuExpanded;
    },
    getCompanies(state) {
        return state.companies;
    },
    isWelcomeGuideShown(state) {
        return state.welcomeGuideShown;
    },
};
export default {
    state: () => initialState,
    actions,
    getters: gettersModule,
    mutations,
};
