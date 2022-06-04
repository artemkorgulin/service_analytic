import { errorHandler, showSuccessMessage } from '~utils/response.utils';

function sortPickListByPopularity(data) {
    return data.flat().sort((a, b) => b.popularity - a.popularity);
}

export default {
    namespaced: true,
    state: () => ({
        showAllElementsForBulk: false,
        closedRowForEdit: [],
        selectedWbNmID: 0,
        commonData: {
            title: '',
            descr: '',
        },
        contentAlert: {
            grade: false,
            price: false,
            photo: false,
            name: false,
            char: false,
            descr: false,
        },
        pickList: [],
        pending: false,
        productId: null,
        data: null,
        dataWildberries: null,
        startData: null,
        isPriceCheckAborted: false,
        additionalPricesWB: [],
        additionalPricesOzon: [],
        emptyCharacterisctics: 0,
        infoTop36: undefined,
        name: null,
        changeCount: 0,
        actionOzon: 0,
        actionWildberries: 0,
        dictionariesWildberries: {},
        statsTabActiveIndex: 0,
        changes: {
            items: [],
            restore: {
                count: 0,
                key: null,
                nomenclature: null,
                size: null,
            },
        },
        pickListSorted: [],
        pickListUnsortedExtra: [],
        pickListModal: [],
        activeField: {
            field: null,
            newValue: null,
            panelsOpen: [],
        },
        searchOptimizationPanels: {
            ozon: [
                {
                    id: 1,
                    title: 'Высокая популярность',
                    subTitle: 'Название товара',
                    capacity: 2,
                },
                {
                    id: 2,
                    title: 'Средняя популярность',
                    subTitle: 'Описание',
                    capacity: 6,
                },
            ],
            wildberries: [
                {
                    id: 1,
                    title: 'Высокая популярность',
                    subTitle: 'Название товара',
                    capacity: 2,
                },
                {
                    id: 2,
                    title: 'Средняя популярность',
                    subTitle: 'Назначение, Направление',
                    capacity: 3,
                },
                {
                    id: 3,
                    title: 'Низкая популярность',
                    subTitle: 'Описание',
                    capacity: 11,
                },
            ],
        },
        selectedOptions: {},
        escrow: {},
        certificates: [],
        hashes: [],
    }),
    getters: {
        getImages(state, getters, rootState, rootGetters) {
            const { id: idMp } = rootGetters.isSelectedMp;

            if (idMp === 2) {
                const addin = state.dataWildberries.data_nomenclatures[0].addin;
                const field = addin.find(({ type }) => type === 'Фото');
                return field.params.map(item => item.value);
            } else {
                return state.data.images;
            }
        },
        showRecom(state) {
            try {
                const exceptionsKeys = ['product_rating'];
                const top36Total = Object.keys(state.infoTop36).reduce(
                    (acc, keys) =>
                        exceptionsKeys.includes(keys)
                            ? acc + 0
                            : acc + Number(state.infoTop36[keys]),
                    0
                );

                return top36Total > 0;
            } catch {
                return false;
            }
        },
        getQuantityProduct(state, getters, rootState, rootGetters) {
            const { getProduct } = getters;
            const { isSelMpIndex } = rootGetters;
            return isSelMpIndex == 0
                ? getProduct.quantity
                : getProduct.nomenclatures[state.statsTabActiveIndex].quantity;
        },

        getProduct(state, getters, rootState, rootGetters) {
            const { id: idMp } = rootGetters.isSelectedMp;

            try {
                return idMp === 2 ? state.dataWildberries : state.data;
            } catch {
                return '';
            }
        },
        getProductStartData(state) {
            return state.startData;
        },
        getProdGrade(state, getters, rootState, rootGetters) {
            /* eslint-disable */
            const { id: idMp } = rootGetters.isSelectedMp;

            try {
                // TODO: сделать универсальный метод
                if (idMp === 1) {
                    const { reviews = 0, rating: grade = 0 } = getters.getProduct;
                    return { reviews, grade };
                } else {
                    const { product_rating: grade = 0, product_comments: reviews = 0 } =
                        state.infoTop36;
                    return { reviews, grade };
                }
            } catch {
                return { reviews: 0, grade: 0 };
            }
        },
        getNameWb(state) {
            try {
                const {
                    data: { addin },
                } = state.dataWildberries;
                const findByField = 'Наименование';
                const findedFiled = addin.find(({ type }) => type === findByField);
                const [valueField] = findedFiled.params;
                return valueField.value;
            } catch {
                return '';
            }
        },
        getNameProduct(state, getters, rootState, rootGetters) {
            const { id: idMp } = rootGetters.isSelectedMp;
            try {
                return idMp === 2 ? getters.getNameWb : state.data.name;
            } catch {
                return '';
            }
        },
        getDescrProduct(state, getters, rootState, rootGetters) {
            try {
                const { id: idMp } = rootGetters.isSelectedMp;
                if (idMp === 2) {
                    const {
                        dataWildberries: {
                            data: { addin: data },
                        },
                    } = state;

                    const field = data.find(({ type }) => type === 'Описание');
                    return field.params[0].value;
                } else {
                    const { data } = state;
                    return data.descriptions[0] || '';
                }
            } catch {
                return '';
            }
        },
        getWbImages(state, getters, rootState, rootGetters) {
            const { id: idMp } = rootGetters.isSelectedMp;

            if (idMp !== 2) {
                return;
            }
            const { data_nomenclatures } = getters.getProduct;

            return data_nomenclatures.reduce((acc, _) => {
                const { addin, nmId } = _;
                let photo = addin.find(({ type }) => type === 'Фото');
                acc[nmId] = photo ? photo.params.map(({ value }) => value) : [];
                return acc;
            }, {});
        },
        getCategory(state, getters, rootState, rootGetters) {
            const { idMp } = rootGetters.isSelectedMp;
            const { getProduct: product } = getters;

            try {
                if (idMp === 2) {
                    const {
                        category: { name: cat, parent: parentCat },
                    } = product;
                    return parentCat;
                } else {
                    const { bread_crumbs } = produgetProductIdct;
                    return bread_crumbs.split('-')[0].trim();
                }
            } catch {
                return '';
            }
        },

        GET_PRODUCT(state) {
            const { data, pending } = state;
            return {
                data,
                pending,
            };
        },
        GET_CHANGES(state) {
            return state.changes.items;
        },
        GET_RESTORE(state) {
            return state.changes.restore;
        },
        GET_PRODUCT_NAME(state) {
            return state.name;
        },
        GET_ACTION_OZON(state) {
            return state.actionOzon;
        },
        GET_ACTION_WILDBERRIES(state) {
            return state.actionWildberries;
        },
        GET_CHANGE_COUNT(state) {
            return state.changeCount;
        },
        getProductWildberries(state) {
            const { pending } = state;
            return {
                ...state.dataWildberries,
                ...pending,
            };
        },
        getAdditionalPricesWB(state) {
            return state.additionalPricesWB;
        },
        getAdditionalPricesOzon(state) {
            return state.additionalPricesOzon;
        },
        getEmptyCharacteristics(state) {
            return state.emptyCharacterisctics;
        },
        getChangesNomenclature(state) {
            return state.changes.restore.nomenclature;
        },
        getDictionariesWildberries(state) {
            return state.dictionariesWildberries;
        },
        getActiveOptionIndex(state) {
            return state.statsTabActiveIndex;
        },
        getPickList(state) {
            return state.pickList;
        },
        getPickListModal(state) {
            return state.pickListModal;
        },
        getPickListSorted(state) {
            return state.pickListSorted;
        },
        getSearchOptimizationPanel(state, getters, rootState, rootGetters) {
            return state.searchOptimizationPanels[rootGetters.getSelectedMarketplaceSlug];
        },
        getPickListSortedExtra(state) {
            return state.pickListUnsortedExtra;
        },
        getProductId(state) {
            return state.productId;
        },
        getProductWildberriesBrand(state) {
            return state?.dataWildberries?.brand;
        },
        getActiveField(state) {
            return state.activeField.field;
        },
        getActiveFieldNewValue(state) {
            return state.activeField.newValue;
        },
        getOpenPanels(state) {
            return state.activeField.panelsOpen;
        },
    },
    mutations: {
        setCommonField(state, { value, field }) {
            state.commonData[field] = value;
        },
        setSignalAlert(state, { value, field }) {
            state.contentAlert[field] = value;
        },
        setWbAddin(state, { field, value }) {
            if (!value.length) return;
            const {
                data: { addin },
            } = state.dataWildberries;

            // TODO: Надо срочно решить, так как изначально пустые характеристики, не заполняет

            const index = addin.findIndex(({ type }) => type === field);
            if (index < 0) {
                addin.push({ type: field, params: value });
            }

            const fCheckParam = values => {
                return values
                    .map(({ value }) => value)
                    .sort()
                    .join()
                    .toLowerCase();
            };

            for (let i = 0; i < addin.length; i += 1) {
                const item = addin[i];
                if (item.type === field) {
                    // TODO: Временное решение, пока не придумаю, как сразу мутировать Характеристики во Vuex
                    if (fCheckParam(value) === fCheckParam(item.params)) {
                        return;
                    }
                    item.params = value;
                    return;
                }
            }
        },
        setField(state, { value, field }) {
            state[field] = value;
        },
        setAdditUnitOzon(state, { field, value }) {
            try {
                state.data[field] = value;
            } catch (error) {
                console.error(error);
            }
        },
        setPickList(state, data) {
            state.pickList = data;
        },
        setActiveOptionIndex(state, data) {
            state.statsTabActiveIndex = data;
        },
        setProduct(state, data) {
            state.startData = data;
            state.data = data;
        },
        setProductWildberries(state, data) {
            state.startData = data;
            state.dataWildberries = data;
        },
        setAdditionalPricesWildberries(state, payload) {
            payload.forEach(payloadEl => {
                let isExistPaloadEl = false;
                state.additionalPricesWB.forEach(el => {
                    if (el.nmid == payloadEl.nmid) {
                        isExistPaloadEl = true;
                    }
                });

                if (!isExistPaloadEl) {
                    state.additionalPricesWB.push(payloadEl);
                }
            });
        },
        setAdditionalPricesOzon(state, payload) {
            payload.forEach(payloadEl => {
                let isExistPaloadEl = false;
                state.additionalPricesOzon.forEach(el => {
                    if (el.id == payloadEl.id) {
                        isExistPaloadEl = true;
                    }
                });

                if (!isExistPaloadEl) {
                    state.additionalPricesOzon.push(payloadEl);
                }
            });
        },
        EDIT_PRODUCT(state, data) {
            let key = data.key;

            if (key.includes('.')) {
                key = key.split('.');

                if (key[0] === 'recomended_characteristics') {
                    const item = { ...state.data[key[0]][key[1]] };
                    item.value = data.value;

                    state.data[key[0]].splice(Number(key[1]), 1, item);
                } else {
                    state.data[key[0]][key[1]][key[2]] = data.value;
                }
            } else {
                state.data[data.key] = data.value;
            }
        },
        SET_PENDING(state, data) {
            state.pending = data;
        },
        SET_CHANGES(state, data) {
            state.changes.items = data;
        },
        SET_RESTORE(state, data) {
            let key = data;

            if (key.includes('.')) {
                key = key.split('.')[1];
            }

            state.changes.restore.count += 1;
            state.changes.restore.key = key;
        },
        SET_RESTORE_WB(state, data) {
            let key = data.prop;
            let keyArr = null;
            let dataKeyArr = null;
            if (key.includes('.')) {
                keyArr = key.split('.');
                if (keyArr[0] === 'characteristics') {
                    key = keyArr[1];
                }
            }

            if (data.key.includes('.')) {
                dataKeyArr = data.key.split('.');
                if (dataKeyArr[0] === 'nomenclature') {
                    state.changes.restore.nomenclature = Number(dataKeyArr[1]);
                    if (dataKeyArr[2] === 'size') {
                        state.changes.restore.size = Number(dataKeyArr[3]);
                    } else {
                        state.changes.restore.size = null;
                    }
                } else {
                    state.changes.restore.size = null;
                }
            }

            state.changes.restore.count += 1;
            state.changes.restore.key = key;
        },
        SET_PRODUCT_NAME(state, data) {
            state.name = data;
        },
        SET_ACTION_OZON(state) {
            state.actionOzon += 1;
        },
        SET_ACTION_WILDBERRIES(state) {
            state.actionWildberries += 1;
        },
        SET_CHANGES_COUNT(state) {
            state.changeCount += 1;
        },
        SET_EMPTY_CHARACTERISTICS(state, data) {
            state.emptyCharacterisctics = data;
        },
        SET_CHANGES_NOMENCLATURE(state, data) {
            state.changes.restore.nomenclature = data;
        },
        SET_DICTIONARY_WILDBERRIES(state, data) {
            state.dictionariesWildberries[data.slug] = data.data;
        },
        SET_PICK_LIST_SORTED(state, data) {
            state.pickListSorted = data;
        },
        SET_PICK_LIST_MODAL(state, data) {
            state.pickListModal = data;
        },
        SET_PICK_LIST_UNSORTED_EXTRA(state, data) {
            state.pickListUnsortedExtra = data;
        },
        REMOVE_FIRST_PICK_LIST_UNSORTED_EXTRA(state) {
            if (state.pickListUnsortedExtra.length) {
                state.pickListUnsortedExtra.splice(0, 1);
            }
        },
        REMOVE_FROM_PICK_LIST_MODAL(state, data) {
            if (state.pickListModal.length) {
                state.pickListModal.splice(data, 1);
            }
        },
        SET_KEYWORD_ACTIVENESS(state, data) {
            this._vm.$set(data.item, 'isActive', true);
        },
        SET_PRODUCT_ID(state, data) {
            state.productId = data;
        },
        SET_ACTIVE_FIELD(state, payload) {
            state.activeField.field = payload;
        },
        CLEAR_ACTIVE_FIELD(state) {
            state.activeField.field = null;
        },
        SET_ACTIVE_FIELD_NEW_VALUE(state, payload) {
            // this._vm.$set(state.activeField, 'newValue', payload);
        },
        CLEAR_ACTIVE_FIELD_NEW_VALUE(state) {
            this._vm.$set(state.activeField, 'newValue', null);
        },
        SET_OPEN_PANELS(state, payload) {
            this._vm.$set(state.activeField, 'panelsOpen', payload);
        },
        setPriceCheckAbort(state, payload) {
            state.isPriceCheckAborted = payload;
        },
    },
    actions: {
        async getPickList({ commit, rootGetters }, productId) {
            const { isSelectedMp } = rootGetters;
            let topic = ['/api/vp/v2/saved_goods_list', '/api/vp/v2/wildberries/saved_pick_list'];
            topic = `${topic[isSelectedMp.id - 1]}/?product_id=${productId}`;

            try {
                const {
                    data: { data },
                } = await this.$axios.get(topic);

                commit('setField', { field: 'pickList', value: data });
            } catch (error) {
                console.error('Error loading previously loaded requests', error);
            }
        },
        async getDataOnTop36({ commit, getters, rootGetters }, productId) {
            /* eslint-disable */
            const product = getters.getProduct;

            const {
                isSelectedMp: { id: idMp },
            } = rootGetters;

            let topic = [
                '/api/vp/v2/ozon/detail/recommendations',
                '/api/an/v1/wb/detail/recommendations',
            ];

            try {
                topic = `${topic[idMp - 1]}/${productId}`;

                let {
                    data: { data },
                } = await this.$axios.get(topic);

                if (rootGetters.isSelectedMp.id === 2) {
                    data = data[0];
                    Object.keys(data).forEach(key => {
                        data[key] = Number(data[key]);
                    });
                }
                commit('setField', { field: 'infoTop36', value: data });
            } catch (error) {
                commit('setField', {
                    field: 'infoTop36',
                    value: { rating: 0, comment: 0, price: 0 },
                });
                console.error(error);
            }
        },
        async loadProductOzon({ commit, state, dispatch, getters }, id) {
            const topic = `/api/vp/v2/products/${id}`;

            commit('SET_PENDING', true);
            commit('setProduct', null);

            try {
                const {
                    data: { data },
                } = await this.$axios.get(topic);

                commit('setProduct', data);
                commit('SET_PRODUCT_NAME', data.name);
                commit('SET_CHANGES_COUNT');

                const {
                    escrow: { hashes, certificates, remainLimit, totalLimit },
                } = data;
                commit('setField', { field: 'hashes', value: hashes });
                commit('setField', { field: 'certificates', value: certificates });
                commit('setField', { field: 'escrow', value: { remainLimit, totalLimit } });
                if (data.listGoodsAdds) {
                    data.listGoodsAdds.forEach(function (el) {
                        el.name = el.search_responce;
                        el.isActive = false;
                    });
                }
            } catch (error) {
                throw error.response.status;
            }
        },
        async loadProductWildberries({ commit, state, getters, rootState, rootGetters }, id) {
            try {
                const topic = `/api/vp/v2/wildberries/products/${id}`;

                const { data } = await this.$axios.get(topic);
                const { title } = data;
                const { addin } = data.data;

                const findedField = addin.find(({ type }) => type === 'Наименование');

                if (!findedField) {
                    addin.push({ type: 'Наименование', params: [{ value: title }] });
                }

                commit('setProductWildberries', data);
                commit('SET_CHANGES_COUNT');
            } catch (error) {
                throw error.response.status;
            }
        },
        RESTORE_CHANGES({ commit, state, rootState, rootGetters }, index) {
            const changes = [...state.changes.items];
            const item = changes[index];
            commit(
                'SET_CHANGES',
                changes.filter(change => {
                    if (item.key === change.key) {
                        return change.timestamp < item.timestamp;
                    } else {
                        return true;
                    }
                })
            );

            if (rootGetters.getSelectedMarketplaceSlug === 'wildberries') {
                commit('SET_RESTORE_WB', item);
            } else {
                commit('SET_RESTORE', item.prop);
                commit('EDIT_PRODUCT', {
                    key: item.key,
                    value: item.value,
                });
            }
        },
        restoreChangesAll({ state, dispatch }) {
            const changes = [...state.changes.items];

            for (let i = changes.length - 1; i >= 0; i--) {
                dispatch('RESTORE_CHANGES', i);
            }
        },
        setEmptyCharacterisctics({ commit }, data) {
            commit('SET_EMPTY_CHARACTERISTICS', data);
        },
        setChangesNomenclature({ commit }, data = null) {
            commit('SET_CHANGES_NOMENCLATURE', data);
        },
        setDictionaryWildberries({ commit }, data) {
            commit('SET_DICTIONARY_WILDBERRIES', data);
        },
        fetchDictionaryWildberries({ commit, state }, slug) {
            return this.$axios
                .$get(`/api/vp/v2/wildberries/directories${slug}`)
                .then(data => {
                    if (data.data) {
                        data.data.forEach(function (el) {
                            el.value = el.title;
                        });
                    }
                    commit('SET_DICTIONARY_WILDBERRIES', { slug, data: data.data });
                })
                .catch(({ response }) => {
                    errorHandler(response, this.$notify);
                });
        },
        setActiveOptionIndex({ commit }, data) {
            commit('setActiveOptionIndex', data);
        },
        setProductOzonUpdated({ commit }, data) {
            commit('setProduct', data);
        },
        setProductWildberriesUpdated({ commit }, data) {
            commit('setProductWildberries', data);
        },
        async fetchPickListWidlberries({ commit, dispatch, state, rootState, rootGetters }, id) {
            const params = { product_id: id };

            return this.$axios
                .$get('/api/vp/v2/wildberries/pick_list', { params })
                .then(response => {
                    if (response.data) {
                        response.data.forEach(el => {
                            el.isActive = false;
                            el.conv = 0;
                        });
                    }
                })
                .catch(({ response }) => {
                    errorHandler(response, this.$notify);
                });
        },
        async fetchKeywordByFilters({ commit, dispatch, state, rootState, rootGetters }, data) {
            const params = { search_responces: data.filterArray };
            const { id: idMp } = rootGetters.isSelectedMp;
            const url =
                idMp === 2 ? '/api/vp/v2/wildberries/key_requests' : 'api/vp/v2/key_requests';

            return new Promise((resolve, reject) => {
                this.$axios.$get(url, { params }).then(response => {
                    if (response.data) {
                        response.data.forEach(el => {
                            el.isActive = false;
                            if (idMp === 2) {
                                el.conv = 0;
                            }
                        });
                        commit('SET_PICK_LIST_MODAL', response.data);
                    }
                    resolve();
                });
            }).catch(({ response }) => {
                errorHandler(response, this.$notify);
            });
        },
        addPickListFromModal({ dispatch, getters }) {
            dispatch('setPickListSorted', {
                data: sortPickListByPopularity(
                    getters.getPickList.concat(getters.getPickListModal)
                ),
                setSpareWords: true,
            });
        },
        setPickListSorted({ commit, getters, stateGetters }, payload) {
            const setSpareWords = payload.setSpareWords || false;

            const srcData = [...payload.data];
            const panels = getters.getSearchOptimizationPanel;
            const result = [];
            panels.forEach((panelItem, panelsIndex) => {
                let indexInsideSection = 0;
                result[panelsIndex] = [];
                for (let i = 0; i < panelItem.capacity; i++) {
                    if (srcData[i]) {
                        srcData[i].section = panelsIndex + 1;
                        srcData[i].indexInsideSection = indexInsideSection++;
                        result[panelsIndex].push(srcData[i]);
                    }
                }
                srcData.splice(0, panels[panelsIndex].capacity);
            });
            commit('SET_PICK_LIST_SORTED', result);

            if (setSpareWords) {
                commit('SET_PICK_LIST_UNSORTED_EXTRA', srcData);
            }
        },
        handleKeywordDelete({ commit, getters, dispatch }, data) {
            const localPickList = JSON.parse(JSON.stringify(getters.getPickListSorted));
            const newKeyword = getters.getPickListSortedExtra[0] || false;
            const index = localPickList[data.index].findIndex(
                el => el.name === data.keywordObject.name
            );
            localPickList[data.index].splice(index, 1);
            const localPickListFlattened = localPickList.flat();
            if (newKeyword) {
                localPickListFlattened.push(newKeyword);
            }

            dispatch('setPickListSorted', { data: localPickListFlattened });
            commit('REMOVE_FIRST_PICK_LIST_UNSORTED_EXTRA');
        },
        handleKeywordDeleteModal({ commit, getters, dispatch }, data) {
            const index = getters.getPickListModal.findIndex(el => el.name === data.name);
            commit('REMOVE_FROM_PICK_LIST_MODAL', index);
        },
        setKeywordActiveness({ commit, getters }, data) {
            const identifier = data.keywordObject.id ? 'id' : 'name';
            const keyword = getters.getPickListSorted[data.index].find(
                el => el[identifier] === data.keywordObject[identifier]
            );
            commit('SET_KEYWORD_ACTIVENESS', { item: keyword, keywordObject: data.keywordObject });
        },
        saveOptimizationWildberries({ commit, getters }) {
            const params = {
                product_id: getters.getProductId,
                data: getters.getPickListSorted.flat().filter(el => el.isActive === true),
            };
            return this.$axios
                .post('/api/vp/v2/wildberries/pick_list', { ...params })
                .then(response => {
                    showSuccessMessage('Список ключевых слов сохранён', this.$notify);
                })
                .catch(({ response }) => {
                    errorHandler(response, this.$notify);
                });
        },
        saveOptimizationOzon({ commit, getters }) {
            const params = {
                oz_product_id: getters.getProductId,
                data: getters.getPickListSorted.flat().filter(el => el.isActive === true),
            };
            return this.$axios
                .post('api/vp/v2/add_goods_list', { ...params })
                .then(response => {
                    showSuccessMessage('Список ключевых слов сохранён', this.$notify);
                })
                .catch(({ response }) => {
                    errorHandler(response, this.$notify);
                });
        },
        setProductId({ commit }, data) {
            commit('SET_PRODUCT_ID', data);
        },
        setActiveField({ commit }, data = null) {
            if (data) {
                commit('SET_ACTIVE_FIELD', data);
            } else {
                commit('CLEAR_ACTIVE_FIELD');
            }
        },
        setOpenPanels({ commit }, data = null) {
            const arr = data || [];
            commit('SET_OPEN_PANELS', arr);
        },

        setActiveFieldNewValue({ commit }, data = null) {
            if (data) {
                commit('SET_ACTIVE_FIELD_NEW_VALUE', data);
            } else {
                commit('CLEAR_ACTIVE_FIELD_NEW_VALUE');
            }
        },
        async fetchCertificatesWB({ state, commit }, nmId) {
            try {
                const url = `/api/vp/v2/wildberries/show-escrow/${state.productId}/${nmId}`;
                const { hashes, certificates, remainLimit, totalLimit } = await this.$axios.$get(
                    url
                );
                commit('setField', { field: 'hashes', value: hashes });
                commit('setField', { field: 'certificates', value: certificates });
                commit('setField', { field: 'escrow', value: { remainLimit, totalLimit } });
            } catch (error) {
                const errorsList = Object.values(error.response.data.error.advanced);
                errorsList.forEach(error => {
                    this.$notify.create({
                        message: error,
                        type: 'negative',
                    });
                });
            }
        },
    },
};
