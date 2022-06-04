import { getObjectFromArray } from '~utils/helpers';
import { TYPE_PAYMENT_FOR_SERVICES, MIN_SKU } from '~utils/global-constants';

const initialState = {
    userHelperCalledBtn: undefined,
    selectedCompanyId: undefined,
    openHelper: false,
    showUserHelper: false,
    addMarketPlaceTick: false,
    addMpBtnEnable: true,
    isLoadingAddMp: false,
    marketplaces: {
        showSettings: false,
        selected: null,
        // Attention not to change the sequence of online stores,
        // these are constants, you can only add new ones
        items: [
            {
                id: 1,
                name: 'Ozon',
                key: 'ozon',
                image: '/images/icons/ozon.png',
                disable: false,
            },
            {
                id: 2,
                name: 'Wildberries',
                key: 'wildberries',
                image: '/images/icons/wb.png',
                disable: false,
            },
            {
                id: 3,
                name: 'Amazon',
                key: 'amazon',
                image: '/images/icons/amazon.png',
                disable: true,
            },
        ],
        fetched: false,
    },
};
const gettersModule = {
    getObjectAllMpByName: state => getObjectFromArray('name', state.marketplaces.items),
    selectedAccId: state => {
        const { accounts } = state.auth.user;
        return Object.keys(accounts).find(key => accounts[key].pivot.is_selected !== 0);
    },
    getAccounts: state => {
        try {
            return Object.values(state.auth.user.accounts);
        } catch {
            return [];
        }
    },
    isSelectedMp: (state, getters) => {
        const { items: mp } = state.marketplaces;

        try {
            const accounts = getters.getAccounts;
            if (!Array.isArray(accounts) && !accounts.length) {
                return undefined;
            }

            let selectedMp = accounts.find(({ pivot }) => Boolean(pivot.is_selected));

            const { platform_title, id: userMpId } = selectedMp;

            selectedMp = mp.find(({ key }) => {
                const re = new RegExp(key);
                return re.test(platform_title.toLowerCase());
            });

            return {
                userMpId,
                ...selectedMp,
            };
        } catch (error) {
            console.error(error);
            return {
                id: 1,
            };
        }
    },

    isSelMpIndex: (state, getters) => getters.isSelectedMp.id - 1,

    firstLogin: state => {
        try {
            const {
                user: { first_login },
            } = state.auth;

            return first_login === 1;
        } catch {
            return false;
        }
    },
    userActiveAccounts: state => {
        try {
            let {
                auth: {
                    user: { accounts },
                },
            } = state;
            const getImgMarketPlace = title =>
                title === 'Ozon' ? '/images/icons/ozon.png' : '/images/icons/wb.png';
            accounts = Object.values(accounts);
            accounts.forEach(item => {
                item.img = getImgMarketPlace(item.platform_title);
            });
            return accounts;
        } catch {
            return [];
        }
    },
    userPlan: (state, getters) => {
        try {
            const { tariffs: userPlan } = state.auth.user;
            const renderFunc = userPlan => {
                const conServ = userPlan.tariff.map(({ name }) => name);
                const {
                    end_at: expDate,
                    start_at: startDate,
                    amount: price = '',
                    type,
                } = userPlan?.order ?? {};

                const sku = userPlan?.tariff[0].sku ?? 3;
                const desc = userPlan?.tariff[0].description.split('|') ?? [];

                conServ.push(`До ${sku} SKU`);

                const nameRate = sku <= MIN_SKU ? 'Бесплатный' : 'Промо';

                return {
                    nameRate,
                    conServ,
                    expDate: expDate || '',
                    startDate: startDate || '',
                    price,
                    typePayment: TYPE_PAYMENT_FOR_SERVICES[type] || 'Бесплатно',
                    paid: sku > MIN_SKU,
                    sku,
                    desc,
                };
            };
            return renderFunc(getters.userPlanCompany || userPlan);
        } catch (error) {
            return {
                nameRate: '',
                conServ: '',
                expDate: '',
                startDate: '',
                price: '',
                typePayment: '',
                paid: '',
                sku: '',
                desc: [],
            };
        }
    },
    userPlanCompany: state => {
        /* eslint-disable */
        try {
            const { selectedCompanyId } = state;
            const selectedCompany = state.auth.user.companies.reduce(
                (acc, el) => ((acc[el.id] = el), acc),
                {}
            )[selectedCompanyId];

            return selectedCompany.tariffs;
        } catch {
            return undefined;
        }
    },
    getTariffId: state => state?.auth || [],
    getIsPayedSubscription: state => state?.auth?.user?.subscription?.tariff_id === 2,
    getMarketplaces: state =>
        state.marketplaces.items.map(item => {
            const { ...newItem } = item;
            newItem.selected = false;
            if (newItem.id === state.marketplaces.selected) {
                newItem.selected = true;
            }
            if (item.key === 'wildberries') {
                newItem.disable = false;
            }
            return newItem;
        }),
    getMarketplacesItems: state => state.marketplaces.items,
    getSelectedMarketplace: (state, getters) => getters.isSelectedMp?.id,
    getSelectedMarketplaceSlug: (state, getters) => getters.isSelectedMp?.key,
    isProductHasOptions: (state, getters) => getters.isSelectedMp?.id === 2,
    showSettings: state => state.marketplaces.showSettings,
};
const actions = {
    async nuxtServerInit({ dispatch, $auth }) {
        return Promise.all([
            dispatch('initMarketplace'),
            dispatch('initMenuState'),
            // dispatch('user/fetchCompanies'),
        ]);
    },
    async handleChangeAccount({ state, commit, getters }, id) {
        try {
            /* eslint-disable */
            const topic = '/api/v1/set-default-account';

            await this.$axios.$post(topic, {
                id,
            });

            commit('setActiveUserAccount', id);
        } catch (error) {
            this.isLoading = false;
        }
    },
    async reloadUserAccounts({ commit }) {
        try {
            const {
                data: { user },
            } = await this.$axios.$get('/api/v1/me');

            commit('SET_USER', user);
        } catch (error) {
            console.log('🚀 ~ file: index.js ~ line 88 ~ reloadUserAccounts ~ data', error);
        }
    },
    async initMarketplace({ dispatch }) {
        let marketplace = this.$cookies.get('marketplace');
        if (!marketplace) {
            marketplace = '1';
        }
        return dispatch('setSelectedMarketplace', marketplace);
    },
    async initMenuState({ dispatch }) {
        /* eslint-disable */
        const menustate =
            this.$cookies.get('menustate') == undefined ? true : this.$cookies.get('menustate');

        return dispatch('user/setMenuState', { val: menustate });
    },
    async setSelectedMarketplace({ commit }, payload) {
        if (!payload) {
            console.warn('[vuex action setSelectedMarketplace] NO MARKETPLACE PROVIDED');
            return;
        }
        await this.$cookies.set('marketplace', String(payload), {
            path: '/',
            maxAge: 60 * 60 * 24 * 365,
        });
        await commit('SET_MARKETPLACE_SELECED', String(payload));
    },
};
const mutations = {
    setActiveUserAccount(state, value) {
        const {
            auth: {
                user: { accounts },
            },
        } = state;

        Object.values(accounts).forEach(item => {
            item.pivot.is_selected = item.id === value ? 1 : 0;
        });
    },
    setField(state, { field, value }) {
        state[field] = value;
    },
    SET_USER_ACCOUNTS(state, payload) {
        state.auth.user.accounts = payload;
    },
    SET_USER(state, user) {
        state.auth.user = user;
    },
    SET_ACCOUNT(state, payload) {
        state.auth.user.accounts[payload.id] = payload;
    },
    SET_MENU_ITEMS(state, payload) {
        state.menuItems = payload;
    },
    SET_MARKETPLACE_SELECED(state, payload) {
        state.marketplaces.selected = payload;
    },
    SET_MARKETPLACES(state, payload) {
        state.marketplaces.items = payload;
        state.marketplaces.fetched = true;
    },
    SET_OZON_CATEGORIES(state, data) {
        state.categories.ozon.items = data;
    },
    SHOW_SETTINGS(state, payload) {
        state.marketplaces.showSettings = payload;
    },
};
export default {
    state: () => initialState,
    actions,
    getters: gettersModule,
    mutations,
};
