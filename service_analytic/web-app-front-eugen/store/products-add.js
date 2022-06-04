import Vue from 'vue';
import { errorHandler } from '~utils/response.utils';

export default {
    namespaced: true,
    state: () => ({
        data: {},
        newBrands: [],
        newBrandsProcessed: [],
        loaded: false,
        totalBrands: 0,
        totalProducts: 0,
        currentPageBrands: 0,
        lastPageBrands: null,
        productsSelected: [],
    }),
    actions: {
        async fetchNewUserBrands({ commit, dispatch, state, rootState, rootGetters }, data = {}) {
            commit('SET_LOADED', true);
            const url = `/api/vp/v2/${rootGetters.getSelectedMarketplaceSlug}/select-not-active-brands`;
            const params = {};
            if (data.search) {
                params.search = data.search;
            } else {
                params.page = state.currentPageBrands + 1;
            }
            return this.$axios
                .$get(url, { params })
                .then(response => {
                    commit('SET_DATA', response.data);
                    commit('SET_NEW_BRANDS', response.data);
                    commit('SET_TOTAL_BRANDS', response.total);
                    commit('SET_CURRENT_PAGE_BRANDS', response.current_page);
                    commit('SET_LAST_PAGE_BRANDS', response.last_page);
                    dispatch('setNewUserBrandsProcessed', {
                        data: response.data,
                        search: data.search,
                    });
                })
                .catch(({ response }) => {
                    errorHandler(response, this.$notify);
                })
                .finally(() => {
                    commit('SET_LOADED', false);
                });
        },
        setNewUserBrandsProcessed({ commit }, data) {
            data.data.forEach(brand => {
                brand.checked = false;
                if (!brand.products) {
                    brand.products = [];
                }
            });
            if (data.search) {
                commit('SET_NEW_BRANDS_PROCESSED_FROM_SEARCH', data.data);
            } else {
                commit('SET_NEW_BRANDS_PROCESSED', data.data);
            }
        },
        setCheckboxBrand({ commit, getters, rootGetters }, data) {
            const brand = getters.getNewBrandsProcessed.find(el => el.brand === data.brand);
            if (brand) {
                commit('SET_CHECKBOX', { item: brand });

                const leftToAdd =
                    rootGetters['tariffs/getActivatedSku'] - getters.getProductsSelected.length;
                if (brand.products.length) {
                    for (let i = 0; i < Math.max(leftToAdd, brand.products.length); i++) {
                        commit('SET_CHECKBOX', { item: brand.products[i], value: true });
                    }
                }
            }
        },
        addProductToSelected({ commit, getters, dispatch, rootGetters }, data) {
            const limit =
                rootGetters['tariffs/getActivatedSku'] - getters.getProductsSelected.length - 1;

            const targetBrand = getters.getNewBrandsProcessed.find(el => el.brand === data.brand);
            const targetProduct = targetBrand.products.find(el => el.id === data.id);
            const isAlreadyAdded = Boolean(
                getters.getProductsSelected.find(el => el.id === data.id)
            );

            if (isAlreadyAdded) {
                dispatch('removeProductFromSelected', data.id);
            } else {
                if (limit < 0) {
                    // Todo поставить это видимо пытались недавать
                    // commit('REMOVE_EXCESSIVE_PRODUCTS_FROM_SELECTED', Math.abs(limit));
                }
                commit('ADD_PRODUCT_TO_SELECTED', targetProduct);
            }
        },
        removeAllProductsFromSelected({ commit }) {
            commit('REMOVE_ALL_PRODUCTS_FROM_SELECTED');
        },
        removeProductFromSelected({ commit, getters }, data) {
            const deletedProductIndex = getters.getProductsSelected.findIndex(el => el.id === data);
            commit('REMOVE_PRODUCT_FROM_SELECTED', deletedProductIndex);
        },
        resetData({ commit }) {
            commit('SET_DATA', {});
            commit('SET_NEW_BRANDS', []);
            commit('RESET_NEW_BRANDS_PROCESSED');
            commit('SET_TOTAL_BRANDS', 0);
            commit('SET_TOTAL_PRODUCTS', 0);
            commit('SET_CURRENT_PAGE_BRANDS', 0);
            commit('SET_LAST_PAGE_BRANDS', null);
            commit('REMOVE_ALL_PRODUCTS_FROM_SELECTED');
        },
    },
    mutations: {
        SET_DATA(state, data) {
            state.data = data;
        },
        SET_NEW_BRANDS(state, data) {
            state.newBrands.push(...data);
        },
        SET_NEW_BRANDS_PROCESSED(state, data) {
            state.newBrandsProcessed.push(...data);
        },
        RESET_NEW_BRANDS_PROCESSED(state) {
            state.newBrandsProcessed = [];
        },
        SET_NEW_BRANDS_PROCESSED_FROM_SEARCH(state, data) {
            state.newBrandsProcessed = data;
        },
        SET_CHECKBOX(state, data) {
            if (!Object.prototype.hasOwnProperty.call(data.item, 'checked')) {
                data.item.checked = false;
            }
            Vue.set(data.item, 'checked', data.value || !data.item.checked);
        },
        ADD_PRODUCTS_TO_BRAND(state, data) {
            data.data.forEach((el, index) => {
                Vue.set(data.item.products, data.item.products.length, data.data[index]);
            });
            // data.item.products.push(...data.data);
        },
        SET_LOADED(state, data) {
            state.loaded = data;
        },
        SET_TOTAL_BRANDS(state, data) {
            state.totalBrands = data;
        },
        SET_TOTAL_PRODUCTS(state, data) {
            state.totalProducts = data;
        },
        SET_CURRENT_PAGE_BRANDS(state, data) {
            state.currentPageBrands = data;
        },
        SET_LAST_PAGE_BRANDS(state, data) {
            state.lastPageBrands = data;
        },
        ADD_PRODUCT_TO_SELECTED(state, data) {
            state.productsSelected.push(data);
        },
        REMOVE_EXCESSIVE_PRODUCTS_FROM_SELECTED(state, data) {
            state.productsSelected.splice(0, data);
        },
        REMOVE_ALL_PRODUCTS_FROM_SELECTED(state) {
            state.productsSelected = [];
        },
        REMOVE_PRODUCT_FROM_SELECTED(state, data) {
            state.productsSelected.splice(data, 1);
        },
        SET_CURRENT_PRODUCT_PAGE_BY_BRAND(state, data) {
            Vue.set(data.item, 'current_page', data.data);
        },
        SET_LAST_PRODUCT_PAGE_BY_BRAND(state, data) {
            Vue.set(data.item, 'last_page', data.data);
        },
    },
    getters: {
        getNewBrands(state) {
            return state.newBrands;
        },
        getNewBrandsProcessed(state) {
            return state.newBrandsProcessed;
        },
        getBrandCheckboxsesState(state) {
            return state.newBrandsProcessed.map(el => el.checked);
        },
        isLoaded(state) {
            return state.loaded;
        },
        getTotalBrandsNumber(state) {
            return state.totalBrands || 0;
        },
        getTotalProductsNumber(state) {
            return state.totalProducts || 0;
        },
        isAllBrandsShown(state) {
            return state.currentPageBrands === state.lastPageBrands;
        },
        getProductsSelected(state) {
            return state.productsSelected;
        },
    },
};
