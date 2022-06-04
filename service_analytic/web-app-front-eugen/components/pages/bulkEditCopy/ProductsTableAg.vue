<template>
    <div v-if="isLoading" class="products-table d-flex justify-center pt-3 pb-3">
        <VProgressCircular indeterminate color="primary" size="60" />
    </div>
    <div v-else class="products-table">
        <div v-if="mode === 'copy'" class="select-char">
            <SeAlert class="mb-4">
                Выберите поля, в которые хотите скопировать характеристики
            </SeAlert>
            <ul class="select-char__list mb-8">
                <li class="select-char__list-item" style="grid-column: 1 / -1">
                    <BaseCheckbox
                        :value="Boolean(selectedFeatureForEdit.length)"
                        @click="selRemAllFeatures()"
                    />
                    <span class="select-char__list-title"
                        >{{ selectedFeatureForEdit.length ? 'Стереть' : 'Выбрать' }} все</span
                    >
                </li>
                <template v-for="item in columnsDefs">
                    <li v-if="item.editable" :key="item.field" class="select-char__list-item">
                        <BaseCheckbox
                            :value="selectedFeatureForEdit.includes(item.field)"
                            @click="selectAFeatureToEdit(item.field)"
                        />
                        <span class="select-char__list-title">{{ item.field }}</span>
                    </li>
                </template>
            </ul>
            <SeStepItem
                :number="3"
                title="Выберите товары, к которым хотите применить выбранные характеристики"
            ></SeStepItem>
        </div>
        <AgGridVue
            style="width: 100%"
            :dom-layout="domLayout"
            class="ag-theme-alpine bulk-products-ag-table"
            :grid-options="gridOptions"
            :column-defs="columnsDefs"
            :side-bar="sidebar"
            :default-col-def="defaultColDef"
            :row-data="rowData"
            :row-style="rowStyle"
            :get-row-height="getRowHeight"
            row-selection="multiple"
            :locale-text="localeText"
            :modules="modules"
            :enable-range-selection="mode !== 'copy'"
            :enable-fill-handle="mode !== 'copy'"
            :animate-rows="true"
            :fill-handle-direction="fillHandleDirection"
            @cell-value-changed="setCharacteristic"
            @grid-ready="onGridReady"
        />
    </div>
</template>

<script>
    /* eslint-disable */ import { mapMutations, mapGetters } from 'vuex';
    import { AgGridVue } from '@ag-grid-community/vue';
    import { LicenseManager } from '@ag-grid-enterprise/core';
    import { AllModules } from '@ag-grid-enterprise/all-modules';
    import { AG_GRID_LOCALE_RU } from '~utils/ag-grid_locale';
    import '@ag-grid-community/core/dist/styles/ag-grid.css';
    import '@ag-grid-community/core/dist/styles/ag-theme-alpine.css';
    import {
        getObjectFromArray,
        upperCaseFirst,
        getCoords,
        sortObjectByAbc,
    } from '~/assets/js/utils/helpers';
    import _l from 'lodash';
    import AgCustomImgCell from '~/components/ag-tables/AgCustomImgCell';
    import AgOptimization from '~/components/ag-tables/AgCustomOptimization.vue';
    import AgProductLink from '~/components/ag-tables/AgLinkProductOnMp.vue';
    import AgVisibleProduct from '~/components/ag-tables/AgVisibleProduct.vue';
    import AgVisibleProductHeader from '~/components/ag-tables/AgVisibleProductHeader.vue';
    import AgProductTableCellEditor from '~/components/ag-tables/AgProductTableCellEditor.vue';
    import AgCheckboxRender from '~/components/ag-tables/AgCheckboxRender.vue';
    import AgOzonCell from '~/components/ag-tables/AgOzonCell.vue';
    import { utimesSync } from 'fs';

    LicenseManager.setLicenseKey(process.env.AG_GRID_KEY);

    function getParamsFromAddin(item) {
        if (Array.isArray(item)) {
            return item.map(_ => _.value || _.count);
        }
        return item.value;
    }

    const additСolumnsOzon = {
        фото: 'photo_url',
        id: 'ID',
        наименование: 'name',
        оптимизация: 'content_optimization',
    };

    export default {
        name: 'ProductsTableAg',
        components: {
            AgGridVue,
            AgCustomImgCell,
            AgOptimization,
            AgProductLink,
            AgVisibleProduct,
            AgVisibleProductHeader,
            AgProductTableCellEditor,
            AgCheckboxRender,
            AgOzonCell,
        },
        props: {
            category: {
                type: [String, Number],
                default: '',
            },
            mode: {
                default: '',
            },
            selectedProductId: {
                default: '',
            },
        },
        data: () => ({
            isLoading: false,
            sidebar: {
                toolPanels: [
                    {
                        id: 'columns',
                        labelDefault: '',
                        labelKey: '',
                        iconKey: 'columns',
                        toolPanel: 'agColumnsToolPanel',
                        toolPanelParams: {
                            suppressRowGroups: true,
                            suppressValues: true,
                            suppressPivots: true,
                            suppressPivotMode: true,
                        },
                    },
                ],
            },
            ozonExeptionReqFieldsIds: [10289],
            gridApi: null,
            columnApi: null,
            localeText: null,
            defaultColDef: {
                suppressMenu: true,
                resizable: true,
                editable: true,
            },
            gridOptions: {
                suppressPropertyNamesCheck: true,
                suppressSelection: false,
                enableRangeSelection: true,
                editable: false,
                popupParent: document.querySelector('body'),
            },
            fillHandleDirection: null,
            modules: AllModules,
            rowStyle: { border: 'none' },
            rowHeight: 56,
            domLayout: null,
            uniqueFields: [],
            rowData: [],
            products: [],
            rowIndexWithError: [],
            closedRowForEdit: [],
            startValidate: false,
            start: false,
            selectedFeatureForEdit: [],
            horizontalScrollPrevValue: 0,
        }),
        computed: {
            ...mapGetters(['isSelectedMp', 'isSelMpIndex', 'selectedAccId']),
            uniqueFieldsObject() {
                return getObjectFromArray('type', this.uniqueFields);
            },
            columnsDefs() {
                /* eslint-disable */
                const exeptionFields = [
                    'видимость',
                    'бренд',
                    'описание',
                    'фото',
                    'статус',
                    'оптимизация',
                    'id',
                    'цвет',
                    'артикул',
                ];

                const additFieldsSettings = {
                    фото: {
                        cellRenderer: params =>
                            `<div style="height: 100%; display: flex; align-items: center;">
                                    <img src="${params.value}" style="width: 42px; height: 42px; object-fit: contain"/>
                                </div>`,
                        maxWidth: 80,
                    },
                    оптимизация: {
                        cellRenderer: 'AgOptimization',
                    },
                    id: {
                        headerName: 'Артикул',
                        cellRenderer: 'AgProductLink',
                        order: 3,
                    },
                    цвет: {
                        cellRenderer: params => {
                            if (!Array.isArray(params.value)) {
                                return params.value;
                            }

                            return `<div class="d-flex" style="">${params.value.join(
                                ',&nbsp;'
                            )}</div>`;
                        },
                    },
                    видимость: {
                        headerComponent: 'AgVisibleProductHeader',
                        headerComponentParams: {
                            clicked: this.setAllOpenOrCloseForEdit,
                        },
                        cellRenderer: 'AgVisibleProduct',
                        cellRendererParams: {
                            clicked: this.openOrCloseRowForEdit,
                        },
                        width: 70,
                        pinned: 'left',
                    },
                };

                const fieldsNotVisible = [
                    'код ролика на youtube',
                    '3d-изображение',
                    'название',
                    'rich-контент json',
                ];

                const wbHeaderNames = {
                    цвет: 'Основной цвет',
                };

                return this.uniqueFields
                    .filter(({ type }) => !fieldsNotVisible.includes(type.toLowerCase()))
                    .map(item => {
                        const { type } = item;
                        const headerName =
                            this.isSelectedMp.id === 2 && item.type.toLowerCase() in wbHeaderNames
                                ? wbHeaderNames[item.type.toLowerCase()]
                                : upperCaseFirst(item?.headerName || type.toLowerCase());
                        const isEditable = !exeptionFields.includes(type.toLowerCase());
                        const _ = {
                            field: type,
                            editable: isEditable,
                            cellClass: this.cellClass,
                            cellEditor: 'AgProductTableCellEditor',
                            cellEditorPopup: true,
                            headerName,
                            headerTooltip: headerName,
                            columnSetup: {
                                maxCount: Number(item.useOnlyDictionaryValues),
                                ...item,
                                gridApi: this.gridApi,
                                mode: this.mode,
                            },
                            category: this.category,
                            suppressKeyboardEvent: params => {
                                const key = params.event.key;
                                const gridShouldDoNothing = params.editing && key === 'Enter';
                                return gridShouldDoNothing;
                            },
                        };

                        if (item.editingMethod === 'boolean') {
                            _.cellRenderer = 'AgCheckboxRender';
                        }

                        if (
                            (item.useOnlyDictionaryValues || item.autoComplete) &&
                            this.isSelectedMp.id === 1
                        ) {
                            _.cellRenderer = 'AgOzonCell';
                        }

                        const field = type.toLowerCase();

                        return field in additFieldsSettings
                            ? { ..._, ...additFieldsSettings[field] }
                            : _;
                    });
            },
        },
        async created() {
            this.lastEditing = _l.debounce(this.createRows, 10);
            this.gridOptions.getRowStyle = params => {
                if (this.mode === 'copy' && params.node.rowIndex === 0)
                    return { 'border-bottom': '3px solid #d1d6dd' };
                else {
                    return this.closedRowForEdit.includes(params.rowIndex)
                        ? { background: '#f5f5f9', opacity: 0.4 }
                        : {};
                }
            };

            this.gridOptions.onBodyScroll = params => {
                this.remapMenuOnScroll();
            };

            this.gridOptions.onColumnVisible = params => {
                let { columns, source } = params;
                if (source !== 'toolPanelUi') return;
                columns = columns.map(({ colId }) => colId);
                const columnsForEdit = this.columnsDefs
                    .filter(({ editable }) => editable)
                    .map(({ field }) => field.toLowerCase());

                columns.forEach(field => {
                    if (columnsForEdit.includes(field.toLowerCase())) {
                        this.selectAFeatureToEdit(field);
                    }
                });
            };
            document.addEventListener('click', this.clickOutsideTheTable);

            this.localeText = AG_GRID_LOCALE_RU;
            this.domLayout = 'autoHeight';
            this.fillHandleDirection = 'y';
            await this.getProductsByCategoryForEdit();
            this.refreshOnOpenAndCloseSign(true);
        },
        beforeDestroy() {
            document.removeEventListener('click', this.clickOutsideTheTable);
        },
        methods: {
            ...mapMutations('product', ['setField']),
            remapMenuOnScroll() {
                const vMenu = document.querySelector('.menuable__content__active');
                const autoComplete = document.querySelector('.se-bulk-edit-ac');

                if (!(vMenu && autoComplete)) return;
                let { left } = getCoords(autoComplete);

                if (!this.horizontalScrollPrevValue) this.horizontalScrollPrevValue = left;
                const vMenuLeft = window.getComputedStyle(vMenu).left;
                const leftMenu = left - this.horizontalScrollPrevValue;
                this.horizontalScrollPrevValue = left;

                vMenu.style.left = `${Number(vMenuLeft.replace(/[^0-9]/g, '')) + leftMenu}px`;
            },
            clickOutsideTheTable(e) {
                const { target } = e;
                const popUpAgTable = document.querySelector('.ag-popup-editor');
                const vMenu = document.querySelector('.menuable__content__active');

                if ((vMenu && vMenu.contains(target)) || popUpAgTable.contains(target)) return;
                else this.gridApi.stopEditing();
            },
            // Методы для работы с данными в таблице
            selRemAllFeatures() {
                if (this.selectedFeatureForEdit.length) {
                    this.selectedFeatureForEdit.forEach(field => {
                        this.gridColumnApi.setColumnVisible(field, false);
                    });
                    this.selectedFeatureForEdit = [];
                } else {
                    this.selectedFeatureForEdit = this.columnsDefs
                        .filter(({ editable }) => editable)
                        .map(({ field }) => {
                            this.gridColumnApi.setColumnVisible(field, true);
                            return field;
                        });
                }
            },

            selectAFeatureToEdit(field) {
                const index = this.selectedFeatureForEdit.indexOf(field);
                if (index < 0) {
                    this.gridColumnApi.setColumnVisible(field, true);
                    this.selectedFeatureForEdit.push(field);
                } else {
                    this.gridColumnApi.setColumnVisible(field, false);
                    this.selectedFeatureForEdit.splice(index, 1);
                }
            },

            async onGridReady(params) {
                this.gridApi = params.api;
                this.gridColumnApi = params.columnApi;

                params.api.setRowData(this.rowData);
            },

            getRowHeight(params) {
                return this.rowHeight;
            },
            refreshOnOpenAndCloseSign(start) {
                this.setField({
                    field: 'showAllElementsForBulk',
                    value: this.closedRowForEdit.length,
                });

                this.setField({
                    field: 'closedRowForEdit',
                    value: [...this.closedRowForEdit],
                });

                if (!start) {
                    this.createRows(this.products);
                }
            },
            setAllOpenOrCloseForEdit() {
                if (this.closedRowForEdit.length) {
                    this.closedRowForEdit = [];
                } else {
                    for (let i = 0; i < this.products.length; i += 1) {
                        this.closedRowForEdit.push(i);
                    }
                }

                this.refreshOnOpenAndCloseSign();
            },

            openOrCloseRowForEdit({ rowIndex }) {
                const indexInArr = this.closedRowForEdit.indexOf(rowIndex);
                if (indexInArr < 0) {
                    this.closedRowForEdit.push(rowIndex);
                } else {
                    this.closedRowForEdit.splice(indexInArr, 1);
                }

                this.refreshOnOpenAndCloseSign();
            },

            cellClass(params) {
                const {
                    colDef: { field, editable },
                    value,
                } = params;

                const { required } = this.uniqueFieldsObject[field.toLowerCase()];
                const checkValue = Array.isArray(value) ? Boolean(value.length) : Boolean(value);

                if (required && !checkValue) {
                    return 'error-cell';
                } else if (!editable) {
                    return 'non-editable-cell';
                } else {
                    return '';
                }
            },

            createRows(data) {
                const createRowsOzon = () => {
                    this.rowData = [];

                    data.forEach((item, index) => {
                        const row = {};
                        const { characteristics } = item;

                        this.uniqueFields.forEach(feature => {
                            let {
                                type: field,
                                id,
                                useOnlyDictionaryValues,
                                autoComplete,
                            } = feature;

                            if (field === 'Видимость') {
                                row[field] = !this.closedRowForEdit.includes(index);
                                return;
                            } else if (field.toLowerCase() in additСolumnsOzon) {
                                row[field] = item[additСolumnsOzon[field.toLowerCase()]];
                                return;
                            } else if (characteristics[id]) {
                                const { value, selected_options } = characteristics[id];
                                row[field] =
                                    useOnlyDictionaryValues || autoComplete
                                        ? selected_options
                                        : value;
                                return;
                            }
                        });

                        row['id'] = {
                            id: item.sku_fbo,
                            url: item.url,
                        };

                        this.rowData.push(row);
                    });
                };
                const createRowsWb = () => {
                    const fieldsList = this.uniqueFields.map(({ type }) => type);
                    const rows = [];
                    const reservedFields = {
                        фото: 'image',
                        оптимизация: 'optimization',
                    };

                    data.forEach((item, index) => {
                        const row = {};
                        const {
                            data: { addin },
                        } = item;
                        fieldsList.forEach(field => {
                            for (let i = 0, l = addin.length; i < l; i += 1) {
                                if (addin[i].type === field) {
                                    row[field] = getParamsFromAddin(addin[i].params);
                                    return;
                                } else if (
                                    Object.keys(reservedFields).includes(field.toLowerCase())
                                ) {
                                    row[field] = item[reservedFields[field.toLowerCase()]];
                                    return;
                                } else if (field === 'Цвет') {
                                    let mainColor = item.data_nomenclatures[0].addin.find(
                                        ({ type }) => type.toLowerCase() === 'основной цвет'
                                    );
                                    mainColor = mainColor
                                        ? ((mc, nomLength) =>
                                              nomLength > 1 ? `${mc}, +${nomLength - 1}` : mc)(
                                              upperCaseFirst(mainColor.params[0].value),
                                              item.data_nomenclatures.length
                                          )
                                        : '';
                                    row[field] = mainColor;
                                    return;
                                } else if (field === 'ID') {
                                    row[field] = {
                                        id: item.data_nomenclatures[0].nmId,
                                        url: item.url,
                                    };
                                    return;
                                } else if (field === 'Видимость') {
                                    row[field] = !this.closedRowForEdit.includes(index);
                                    return;
                                }
                                row[field] = '';
                            }
                        });
                        rows.push(row);
                    });

                    this.rowData = rows;
                };
                try {
                    [createRowsOzon, createRowsWb][this.isSelMpIndex]();
                } catch (err) {
                    console.error(err);
                    this.$notify.create({
                        message: 'Ошибка при создании строк для таблицы.',
                        type: 'error',
                    });
                }
            },

            // Методы для таблицы - сохранение товара

            setCharacteristic(e) {
                const {
                    value,
                    rowIndex,
                    colDef: { field },
                } = e;

                this.setValueInData(rowIndex, field.toLowerCase(), value);
                this.lastEditing(this.products);
            },

            copyCharacteristic() {
                const mainProduct = this.products[0];
                const ozon = () => {
                    const characteristics = { ...mainProduct.characteristics };
                    const featureIds = [];

                    for (const featureName of this.selectedFeatureForEdit) {
                        featureIds.push(this.uniqueFieldsObject[featureName.toLowerCase()].id);
                    }

                    for (const id of Object.keys(characteristics)) {
                        if (!featureIds.includes(Number(id))) {
                            delete characteristics[id];
                        }
                    }
                    this.products.forEach((item, index) => {
                        if (index === 0 || this.closedRowForEdit.includes(index)) return;

                        for (const id of featureIds) {
                            if (id in characteristics)
                                item.characteristics[id] = characteristics[id];
                            else delete item.characteristics[id];
                        }
                    });
                };

                const wb = () => {
                    const characteristics = mainProduct.data.addin.filter(({ type }) =>
                        this.selectedFeatureForEdit.includes(type)
                    );

                    this.products.forEach((item, index) => {
                        if (index === 0 || this.closedRowForEdit.includes(index)) return;

                        const finAddin = [
                            ...item.data.addin.filter(
                                ({ type }) => !this.selectedFeatureForEdit.includes(type)
                            ),
                            ...characteristics,
                        ];

                        item.data.addin = finAddin;
                    });
                };

                [ozon, wb][this.isSelMpIndex]();
                this.createRows(this.products);
            },

            setValueInData(index, field, value) {
                if (this.closedRowForEdit.includes(index)) {
                    return;
                }
                const { setValueInDataOzon: ozon, setValueInDataWB: wb } = this;
                [ozon, wb][this.isSelMpIndex](index, field, value);
            },
            setValueInDataOzon(index, field, value) {
                const { characteristics } = this.products[index];
                if (field === 'наименование') {
                    this.products[index].name = value;
                    return;
                }
                console.log(field);
                const { id, useOnlyDictionaryValues, autoComplete } =
                    this.uniqueFieldsObject[field];
                const typeField =
                    useOnlyDictionaryValues || autoComplete ? 'selected_options' : 'value';

                characteristics[id] = {
                    id,
                };

                characteristics[id][typeField] = value;
            },
            setValueInDataWB(index, field, value) {
                const { units } = this.uniqueFieldsObject[field];
                const item = this.products[index];

                const {
                    data: { addin },
                } = item;
                const formatField = upperCaseFirst(field);

                for (let j = 0; j < addin.length; j += 1) {
                    const characteristic = addin[j];
                    if (characteristic.type === formatField) {
                        this.setCharacteristicInWb(characteristic, value, Boolean(units));
                        return;
                    }
                }

                if (Boolean(units)) {
                    addin.push({
                        type: formatField,
                        params: [{ count: value, units: units[0] }],
                    });
                } else {
                    addin.push({
                        type: formatField,
                        params: value.map(_ => ({ value: _ })),
                    });
                }
            },

            setCharacteristicInWb(characteristic, value, isUnits) {
                if (isUnits) {
                    characteristic.params[0].count = value;
                } else if (Array.isArray(value)) {
                    characteristic.params = value.map(_ => ({ value: _ }));
                }
            },

            // Общие методы для работы с бэком
            async getProductsByCategoryForEdit() {
                this.isLoading = true;
                const {
                    getProductListFromOzon: ozon,
                    getProductListFromWb: wb,
                    isSelMpIndex: index,
                } = this;
                try {
                    await [ozon, wb][index]();
                    this.selectedFeatureForEdit = this.columnsDefs
                        .filter(({ editable }) => editable)
                        .map(({ field }) => field);
                } catch (error) {
                    console.error(error);
                } finally {
                    this.isLoading = false;
                }
            },

            validateData() {
                this.rowIndexWithError = [];
                let showError = false;
                let requiredFields = this.uniqueFields.filter(({ required }) => required);
                const ozon = () => {
                    requiredFields = getObjectFromArray('id', requiredFields);
                    this.products.forEach((product, productIndex) => {
                        this.rowIndexWithError[productIndex] = [];
                        const { characteristics } = product;
                        for (const key in requiredFields) {
                            if (key in characteristics) {
                                const value =
                                    characteristics[key].value ||
                                    characteristics[key].selected_options;

                                !Boolean(value) && this.rowIndexWithError[productIndex].push(key);
                            } else {
                                this.rowIndexWithError[productIndex].push(key);
                            }
                        }
                    });
                };

                const wb = () => {
                    this.products.forEach((product, productIndex) => {
                        this.rowIndexWithError[productIndex] = [];
                        let {
                            data: { addin },
                        } = product;
                        addin = getObjectFromArray('type', addin);

                        for (const key of requiredFields) {
                            const type = key.type.toLowerCase();
                            if (type in addin) {
                                !Boolean(addin[type].params) &&
                                    this.rowIndexWithError[productIndex].push(type);
                            } else {
                                this.rowIndexWithError[productIndex].push(type);
                            }
                        }
                    });
                };

                [ozon, wb][this.isSelMpIndex]();

                for (let i = 0, l = this.rowIndexWithError.length && !showError; i < l; i += 1) {
                    if (this.rowIndexWithError[i].length) {
                        showError = true;
                    }
                }

                if (showError) {
                    this.$notify.create({
                        message: 'Обязательные поля не заполнены',
                        type: 'negative',
                    });
                }

                return showError;
            },

            async saveProducts() {
                if (this.validateData()) {
                    this.$emit('finishSaveLoader');
                    return;
                }

                const { saveWb: wb, saveOzon: ozon } = this;
                await [ozon, wb][this.isSelMpIndex]();
            },

            async saveOzon() {
                const url = '/api/vp/v2/ozon/products/update-mass';

                try {
                    const fProducts = [...this.products];
                    fProducts.forEach(item => {
                        Object.keys(item.characteristics).forEach(key => {
                            try {
                                if ('selected_options' in item.characteristics[key]) {
                                    item.characteristics[key].selected_options =
                                        item.characteristics[key].selected_options.map(
                                            ({ id }) => id
                                        );
                                }
                            } catch (error) {
                                console.error(this.uniqueFieldsObject[key]);
                            }
                        });
                    });

                    await this.$axios.put(url, { items: fProducts });
                    this.$notify.create({
                        message: 'Товары успешно сохранены',
                        type: 'positive',
                    });
                    this.$emit('finishSaveLoader', true);
                } catch (error) {
                    console.error(1, error);
                    this.$notify.create({
                        message: 'Ошибка при сохранении товаров.',
                        type: 'negative',
                    });
                    this.$emit('finishSaveLoader', false);
                }
            },

            async saveWb() {
                const urls = '/api/vp/v2/wildberries/products/mass-update';
                const userId = this.$store.state.auth.user.id;
                const params = {
                    user_id: userId,
                    account_id: this.selectedAccId,
                    products: [...this.products].map(item => {
                        const {
                            data: { addin },
                        } = item;
                        for (let i = 0, l = addin.length; i < l; i += 1) {
                            const { type, params } = addin[i];
                            if (type === 'Страна-изготовитель') {
                                item.country_production = params[0].value;
                                return item;
                            }
                        }
                        return item;
                    }),
                };

                try {
                    await this.$axios.put(urls, params);
                    this.$notify.create({
                        message:
                            'Данные товары успешно обновлены. Информация на сайте Wildberries обновляется 24 часа',
                        type: 'positive',
                    });
                    this.$emit('finishSaveLoader', true);
                } catch (error) {
                    this.$notify.create({
                        message: 'Ошибка при сохранении товаров.',
                        type: 'negative',
                    });
                    this.$emit('finishSaveLoader', false);
                }
            },

            // Методы для работы с данным по WB
            async getProductListFromWb() {
                const urls = 'api/vp/v2/wildberries/products';
                try {
                    const {
                        data: { data },
                    } = await this.$axios.get(urls, {
                        params: {
                            categoryNames: this.category,
                            withFeatures: true,
                        },
                    });

                    this.getAListOfCharacteristics(data[0]);

                    this.products = data.map(item => {
                        if (item.country_production) {
                            item.data.addin.push({
                                type: 'Страна-изготовитель',
                                params: [{ value: item.country_production }],
                            });
                        }
                        return item;
                    });

                    this.mode === 'copy' && this.mutationForCopyMode();

                    this.createRows(this.products);
                } catch (error) {
                    console.error(error);
                }
            },

            getAListOfCharacteristics(firstItem) {
                const setCharWb = () => {
                    const {
                        required_characteristics: fc1,
                        recommended_characteristics: { addin: fc2 },
                    } = firstItem;

                    const exeptionFields = ['ключевые слова', 'наименование', 'бренд'];

                    const { uniqueFields } = this;
                    let recChar = [...fc1, ...fc2];
                    const brandField = recChar.find(({ type }) => type.toLowerCase() === 'бренд');

                    recChar.push({
                        type: 'Страна-изготовитель',
                        useOnlyDictionaryValues: false,
                        maxCount: 1,
                        required: true,
                        dictionary: '/countries',
                    });

                    recChar = recChar
                        .sort(sortObjectByAbc('type'))
                        .filter(({ type }) => !exeptionFields.includes(type.toLowerCase()));

                    console.log(brandField);
                    const combinedArr = [
                        { type: 'Видимость' },
                        { type: 'Фото' },
                        { type: 'ID' },
                        {
                            type: 'Наименование',
                            maxCount: 100,
                            required: true,
                        },
                        { type: 'Оптимизация' },
                        { type: 'Цвет' },
                        brandField,
                        ...recChar,
                    ];

                    combinedArr.forEach(item => {
                        for (let i = 0, l = uniqueFields.length; i < l; i += 1) {
                            const uItem = uniqueFields[i];
                            const { type: typeU } = uItem;
                            if (typeU === item.type) {
                                return;
                            }
                        }
                        uniqueFields.push(item);
                    });
                };

                const setCharOzon = () => {
                    firstItem = [...firstItem];
                    const additFields = Object.keys(additСolumnsOzon).map(type => ({
                        type,
                    }));

                    const brandFieldsIndex = firstItem.findIndex(
                        ({ name }) => name.toLowerCase() === 'бренд'
                    );

                    additFields.unshift({ type: 'Видимость' });

                    if (brandFieldsIndex >= 0) {
                        additFields.push(this.createFieldOzon(firstItem[brandFieldsIndex]));
                        firstItem.splice(brandFieldsIndex, 1);
                    }

                    this.uniqueFields = firstItem.map(item => {
                        return this.createFieldOzon(item);
                    });

                    this.uniqueFields.sort(sortObjectByAbc('type'));
                    this.uniqueFields = [...additFields, ...this.uniqueFields];
                };

                try {
                    [setCharOzon, setCharWb][this.isSelMpIndex]();
                } catch (err) {
                    this.$notify.create({
                        message: 'Ошибка при формировании заголовков таблиц',
                        type: 'negative',
                    });
                    throw new Error('an error in the formation of unique characteristics', err);
                }
            },

            createFieldOzon(item) {
                const headerNameExeptionField = {
                    артикул: 'альтернативные артикулы товара',
                };

                return {
                    type: item.name,
                    required:
                        Boolean(item.is_required) &&
                        item.type !== 'Boolean' &&
                        !this.ozonExeptionReqFieldsIds.includes(item.id),
                    id: item.id,
                    autoComplete: item.count_values,
                    headerName: headerNameExeptionField[item.name.toLowerCase()],
                    useOnlyDictionaryValues: Boolean(item.is_collection),
                    isBoolean: item.type === 'Boolean',
                    editingMethod: Boolean(item.is_reference) ? 'default' : item.type.toLowerCase(),
                };
            },

            // Методы для работы с данными по Ozon

            async getProductListFromOzon() {
                const topic = '/api/vp/v2/ozon/product/list-with-feature';
                const params = {
                    page: 1,
                    per_page: 10,
                };
                const query = {
                    'query[filter][category_id]': this.category,
                    'query[with_features]': 1,
                    'query[order][id]': 'asc',
                };

                Object.keys(query).forEach(key => {
                    params[key] = query[key];
                });

                try {
                    await this.getOzonFields();

                    const {
                        data: {
                            data: { data },
                        },
                    } = await this.$axios.get(topic, {
                        params,
                    });

                    const fieldsById = getObjectFromArray('id', this.uniqueFields);

                    this.products = data.map(item => {
                        const { features_values } = item;
                        item.characteristics = {};

                        for (const feature of features_values) {
                            let { feature_id: id, value, option_id } = feature;
                            const { isBoolean } = fieldsById[id];

                            isBoolean && (value = Boolean(value));

                            const isInsertTypeArray =
                                fieldsById[id].useOnlyDictionaryValues ||
                                fieldsById[id].autoComplete;
                            const isValueAlreadyThere = id in item.characteristics;

                            if (isValueAlreadyThere) {
                                if (isInsertTypeArray) {
                                    item.characteristics[id].selected_options.push({
                                        id: option_id,
                                        title: value,
                                    });
                                } else {
                                    item.characteristics[id].value = value;
                                }
                            } else {
                                if (isInsertTypeArray) {
                                    item.characteristics[id] = {
                                        id,
                                        selected_options: [{ id: option_id, title: value }],
                                    };
                                } else {
                                    item.characteristics[id] = {
                                        id,
                                        value,
                                    };
                                }
                            }
                        }

                        delete item.features_values;
                        return item;
                    });

                    console.log(this.products);
                    this.mode === 'copy' && this.mutationForCopyMode();

                    this.createRows(this.products);
                } catch (error) {
                    console.error(error);
                }
            },

            mutationForCopyMode() {
                const index = this.products.findIndex(({ id }) => id === this.selectedProductId);
                if (index !== -1) {
                    const selectedProduct = this.products[index];
                    this.products.splice(index, 1);
                    this.products.unshift(selectedProduct);
                }
            },

            async getOzonFields() {
                const topic = 'api/vp/v2/ozon/category/feature-list';
                const params = {
                    'query[category_id]': this.category,
                };
                try {
                    const { data } = await this.$axios.get(topic, { params });
                    this.getAListOfCharacteristics(data.data);
                } catch (error) {
                    console.error(error);
                }
            },
        },
    };
</script>

<style lang="scss" scoped>
    /* stylelint-disable selector-pseudo-element-no-unknown */

    .products-table {
        position: relative;
        margin-top: 16px;

        &::v-deep .ag-theme-alpine {
            & .ag-root-wrapper {
                border-radius: 16px;
                border-color: $border-color;
            }

            & .ag-popup-child:not(.ag-tooltip-custom) {
                border-radius: 16px;
                box-shadow: none;
            }

            & .ag-tab {
                border-bottom-color: $primary-color;
                color: $primary-color;
            }

            & .ag-floating-filter-button-button:hover {
                border-bottom-color: $primary-color;
                color: $primary-color;
            }

            & .ag-filter-apply-panel {
                display: flex;
                justify-content: space-between;

                & > button {
                    margin-left: 0;
                }
            }

            & .ag-standard-button {
                border-color: $primary-color;
                color: $primary-color;

                &:hover {
                    background-color: $primary-color;
                    color: $white;
                }
            }

            & .ag-header {
                border: none;

                &-row {
                    background-color: $border-color;
                }

                &-cell-text {
                    font-family: $heading-font-family;
                    font-size: 12px;
                    line-height: 16px;
                    font-weight: 500;
                }

                &-icon {
                    color: $primary-color;
                }

                &-cell {
                    .ag-floating-filter .ag-focus-managed:after {
                        border: none;
                    }
                }
            }

            & .ag-group-title-bar-icon:hover {
                color: $primary-color;
            }

            .ag-row .ag-cell {
                display: flex;
                align-items: center;
            }

            & .ag-ltr .ag-side-bar-right,
            & .ag-ltr .ag-side-bar-left {
                border-left-color: $border-color;

                & .ag-selected .ag-side-button-button {
                    border-left-color: $primary-color;
                }
            }

            & .ag-checkbox-input-wrapper.ag-checked:after,
            & .ag-radio-button-input-wrapper.ag-checked:after {
                color: $primary-color;
            }

            & .ag-side-buttons {
                padding: 0;

                & .ag-side-button {
                    &-button {
                        min-height: fit-content;
                        padding: 14px 0;

                        &:hover {
                            color: $primary-color;
                        }
                    }
                }
            }

            & .ag-select:focus {
                border-color: $primary-color;
                box-shadow: none;
            }

            & .ag-text-field-input:focus,
            & .ag-number-field-input:focus,
            & .ag-select .ag-picker-field-display:focus {
                border-color: $primary-color;
                box-shadow: none;
            }
        }
    }

    .ag-grid-filter {
        position: absolute;
        top: 0;
        right: 0;
        width: 300px;
        height: 100%;
        background: #fff;
    }

    .select-char {
        margin-bottom: 24px;

        &__list {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin: 0;
            padding: 0;
        }

        &__list-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        &__list-title {
            font-size: 14px;
        }
    }
</style>
