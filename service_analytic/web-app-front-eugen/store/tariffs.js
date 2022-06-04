import Vue from 'vue';
import { errorHandler } from '~utils/response.utils';

export default {
    namespaced: true,
    state: () => ({
        tariffs: [],
        tariffsGrouped: [
            {
                sku: 3,
                id: '3',
                data: [],
            },
            {
                sku: 30,
                promo: true,
                id: 'promo',
                data: [
                    {
                        active: 1,
                        visible: 1,
                        checked: true,
                        name: 'Оптимизация карточек товара',
                        description: [
                            'Оптимизация видимости в категории',
                            'Оптимизация по поисковым запросам',
                            'Динамика позиций в категории',
                            'Динамика позиций по запросам (WB)',
                        ],
                        price: '',
                        id: 1,
                    },
                    {
                        active: 1,
                        visible: 1,
                        checked: true,
                        name: 'Управление рекламными кампаниями',
                        description: [
                            'Подбор семантического ядра',
                            'Продвижение по стратегии',
                            'Аналитика РК',
                        ],
                        price: '',
                        id: 1,
                    },
                    {
                        active: 1,
                        visible: 1,
                        checked: true,
                        name: 'Аналитика маркетплейсов',
                        description: ['Статистика продаж конкурентов'],
                        price: '',
                        id: 1,
                    },
                ],
            },
        ],
        promocode: {
            entered: null,
            discount: null,
            valid: false,
        },
        tariffSelected: {
            sku: '3',
            months: 1,
        },
        tariffActivated: {
            sku: 3,
        },
        tariffPeriods: [
            {
                id: 1,
                text: '30 дней',
                discount: 0,
            },
            {
                id: 6,
                text: '6 месяцев',
                discount: 10,
            },
            {
                id: 12,
                text: '12 месяцев',
                discount: 15,
            },
        ],
        paymentOptions: [
            {
                title: 'По карте',
                id: 'card',
            },
            {
                title: 'По счёту',
                id: 'bank',
            },
        ],
        paymentOptionSelected: 'card',
    }),
    actions: {
        async fetchTariffs({ commit, dispatch }) {
            return this.$axios
                .$get('/api/v1/tariffs')
                .then(response => {
                    // commit('SET_TARIFFS', response.data);
                    // dispatch('setTariffsGrouped', response.data);
                    dispatch('setTariffs', response.data);
                })
                .catch(error => {
                    console.log('ERR ve ', error);
                    errorHandler(error, this.$notify);
                });
        },
        setTariffs({ commit, dispatch }, data) {
            commit('SET_TARIFFS', data);
            dispatch('setTariffsGrouped', data);
        },
        setTariffsGrouped({ commit, getters }, data) {
            const result = [...getters.getTariffsGrouped];
            const resultObj = {};
            data.forEach(val => {
                if (val.visible && val.name !== 'Промо') {
                    const key = val.sku || 'other';
                    if (!resultObj[key]) {
                        resultObj[key] = {
                            data: [],
                            id: val.sku.toString() || '0',
                            promo: false,
                        };
                    }

                    if (val.sku === 3) {
                        if (
                            val.name === 'Аналитика маркетплейсов' ||
                            val.name === 'Управление рекламными кампаниями'
                        ) {
                            // val.price_tariff_id = -1;
                            val.checked = false;
                            val.price = -1;
                        } else {
                            val.checked = true;
                            val.price = val.price_tariff_id;
                        }
                    } else {
                        val.checked = true;
                        val.price = val.price_tariff_id;
                    }

                    resultObj[key].sku = val.sku;
                    resultObj[key].data.push(val);
                }
            });

            if (!getters.isPromocodeEnteredAndValid && resultObj.other) {
                delete resultObj.other;
            }

            for (const value of Object.values(resultObj)) {
                const existing = result.find(el => el.id === value.id);

                if (existing) {
                    const index = result.indexOf(existing);
                    result[index] = { ...value };
                } else {
                    result.push(value);
                }
            }

            const sortOrder = ['3', 'promo', '30', '100'];
            const sortedResult = result.sort(
                (a, b) => sortOrder.indexOf(a.id) - sortOrder.indexOf(b.id)
            );

            commit('SET_TARIFFS_GROUPED', sortedResult);
        },
        setCheckboxBySkuAndId({ commit, getters }, data) {
            const arrBySku = getters.getTariffsGrouped.find(el => el.id === data.sku).data;
            const arrById = arrBySku.find(item => item.id === data.id);
            commit('SET_CHECKBOX', { obj: arrById });
        },
        setPromocode({ commit }, data) {
            commit('SET_PROMOCODE', data);
        },
        setSelectedSku({ commit }, data) {
            commit('SET_SELECTED_SKU', data);
        },
        setSelectedPeriod({ commit }, data) {
            commit('SET_SELECTED_PERIOD', data);
        },
        setSelectedPaymentOption({ commit }, data) {
            commit('SET_SELECTED_PAYMENT_OPTION', data);
        },
        setAllCurrentCkeckboxesBySku({ commit, getters }, data) {
            getters.getItemsPerSku.forEach(el => {
                commit('SET_CHECKBOX', { obj: el, value: true });
            });
        },
    },
    mutations: {
        SET_TARIFFS(state, data) {
            state.tariffs = data;
        },
        SET_TARIFFS_GROUPED(state, data) {
            state.tariffsGrouped = data;
        },
        SET_PROMOCODE(state, data) {
            state.promocode.entered = data;
        },
        SET_SELECTED_SKU(state, data) {
            state.tariffSelected.sku = data;
        },
        SET_SELECTED_PERIOD(state, data) {
            state.tariffSelected.months = data;
        },
        SET_SELECTED_PAYMENT_OPTION(state, data) {
            state.paymentOptionSelected = data;
        },
        SET_CHECKBOX(state, data) {
            Vue.set(data.obj, 'checked', data.value || !data.obj.checked);
        },
    },
    getters: {
        getTariffs(state) {
            return state.tariffs;
        },
        getTariffsGrouped(state) {
            return state.tariffsGrouped;
        },
        getPromocodeEntered(state) {
            return state.promocode.entered;
        },
        isPromocodeEnteredAndValid(state) {
            return state.promocode.entered && state.promocode.valid;
        },
        getSelectedSku(state) {
            return state.tariffSelected.sku;
        },
        getSelectedPeriod(state) {
            return state.tariffSelected.months;
        },
        getSelectedPeriodDiscount(state) {
            const selected = state.tariffPeriods.find(el => el.id === state.tariffSelected.months);
            return selected ? selected.discount : 0;
        },
        getActivatedTariffId(state, getters, rootState, rootGetters) {
            return rootState?.auth?.user?.subscription?.tariff_id;
        },
        getActivatedSku(state, getters) {
            return (
                state.tariffs.find(el => el.tariff_id === getters.getActivatedTariffId)?.sku || 0
            );
        },
        getTariffPeriods(state) {
            return state.tariffPeriods;
        },
        getPaymentOptions(state) {
            return state.paymentOptions;
        },
        getPaymentOptionSelected(state) {
            return state.paymentOptionSelected;
        },
        isTariffPromo(state) {
            const tarifFound = state.tariffsGrouped.find(
                tarif => tarif.id === state.tariffSelected.sku
            );
            return state.tariffsGrouped && tarifFound ? tarifFound.promo : false;
            // return false;
        },
        getTariffIndex(state) {
            return (
                state.tariffsGrouped.findIndex(tarif => tarif.id === state.tariffSelected.sku) || 0
            );
        },
        getCheckboxsesState(state, getters) {
            return state.tariffsGrouped[getters.getTariffIndex].data.map(el => el.checked);
        },
        getItemsPerSku(state, getters) {
            return state.tariffsGrouped[getters.getTariffIndex].data;
        },
    },
};
