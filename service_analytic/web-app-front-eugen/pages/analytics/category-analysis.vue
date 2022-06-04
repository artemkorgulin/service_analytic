<template>
    <div class="category-analysis">
        <div class="category-analysis__header d-flex align-center">
            <h1 class="category-analysis__title flex-grow-1">Категорийный анализ</h1>
            <SeDatePickerPeriod v-if="showDateEl" @change="handleDatesChange" />
        </div>
        <div class="category-analysis__body">
            <div v-if="isLoading" class="progress-loader">
                <v-progress-circular
                    indeterminate
                    :size="120"
                    color="primary"
                ></v-progress-circular>
            </div>
            <div class="category-analysis-block table-custom">
                <SePageTab
                    ref="sePageTab"
                    v-model="selectedTab"
                    :items="tabItems"
                    class="category-analysis-block_page-tab"
                ></SePageTab>

                <div class="table-custom-diagrams">
                    <template v-if="brandsTable.length">
                        <AgGridVue
                            ref="gridTableDiagrams"
                            style="width: 100%; height: auto"
                            class="ag-theme-alpine"
                            :column-defs="columnDefsDiagrams"
                            :grid-options="gridOptionsDiagrams"
                            :row-data="tableDataDiagrams"
                            :default-col-def="defaultColDefDiagrams"
                            :modules="['AllModules']"
                            :components="['AnalyticsBubbleDiagram']"
                            :tooltip-show-delay="tooltipShowDelay"
                            :tooltip-hide-delay="tooltipHideDelay"
                        ></AgGridVue>
                    </template>
                    <template v-else>
                        <div class="table-custom-diagrams__no-data">
                            <span class="mt-4">
                                Выберите в таблице ниже хотя бы один бренд, чтобы увидеть данные
                            </span>
                        </div>
                    </template>
                    <VBtn
                        tile
                        depressed
                        class="category-analysis-block__expand-button"
                        :style="expandButtonStyle"
                        @click="diagramsTableExpanded = !diagramsTableExpanded"
                    >
                        {{ diagramsTableExpanded ? 'Свернуть' : 'Развернуть' }}
                        <SvgIcon
                            class="ml-2"
                            :name="`outlined/${
                                diagramsTableExpanded ? 'chevronUp' : 'chevronDown'
                            }`"
                        />
                    </VBtn>
                </div>
                <div class="table-custom-master">
                    <AgGridVue
                        ref="gridTableMaster"
                        style="width: 100%"
                        :style="{height: masterTableHeight}"
                        class="ag-theme-alpine"
                        :column-defs="columnDefsMaster"
                        :grid-options="gridOptionsMaster"
                        :row-data="tableData"
                        :default-col-def="defaultColDef"
                        :modules="modules"
                        :components="['AgCustomCellSelect']"
                        :tooltip-show-delay="tooltipShowDelay"
                        :tooltip-hide-delay="tooltipHideDelay"
                    ></AgGridVue>
                    <div class="table-footer">
                        <div class="table-footer-data">
                            <span class="table-footer-data__label">Ср. значение:</span>
                            <span class="table-footer-data__value">
                                {{ masterTableSummary.avg }}
                            </span>
                        </div>
                        <div class="table-footer-data">
                            <span class="table-footer-data__label">Количество:</span>
                            <span class="table-footer-data__value">
                                {{ masterTableSummary.number }}
                            </span>
                        </div>
                        <div class="table-footer-data">
                            <span class="table-footer-data__label">Мин:</span>
                            <span class="table-footer-data__value">
                                {{ masterTableSummary.min }}
                            </span>
                        </div>
                        <div class="table-footer-data">
                            <span class="table-footer-data__label">Макс:</span>
                            <span class="table-footer-data__value">
                                {{ masterTableSummary.max }}
                            </span>
                        </div>
                        <div class="table-footer-data">
                            <span class="table-footer-data__label">Сумма:</span>
                            <span class="table-footer-data__value">
                                {{ masterTableSummary.sum }}
                            </span>
                        </div>
                    </div>
                </div>

                <AnalyticsRecommendations />
            </div>
        </div>
    </div>
</template>

<script>
    /* eslint-disable no-extra-parens, consistent-this, vue/no-unused-components */

    import { mapGetters, mapState, mapActions, mapMutations } from 'vuex';
    import qs from 'qs';
    import { AgGridVue } from 'ag-grid-vue';
    import { AllModules } from '@ag-grid-enterprise/all-modules';
    import { LicenseManager } from '@ag-grid-enterprise/core';
    import '@ag-grid-community/core/dist/styles/ag-grid.css';
    import '@ag-grid-community/core/dist/styles/ag-theme-alpine.css';
    import '@fortawesome/fontawesome-free/js/all';
    import AgCustomCellSelect from '/components/ag-tables/AgCustomCellSelect';
    import AgCellOfEmptyColumn from '/components/ag-tables/AgCellOfEmptyColumn';
    import AnalyticsBubbleDiagram from '/components/pages/analytics/category-analysis/AnalyticsBubbleDiagram';
    import { splitThousands, roundNumber } from '/assets/js/utils/numbers.utils';
    import PendingOverlay from '/components/pages/analytics/PendingOverlay';
    import chartMixin from '~mixins/chart.mixin';
    import SeDatePickerPeriod from '~/components/ui/SeDatePickerPeriod';
    import Page from '~/components/ui/SeInnerPage';

    export default {
        name: 'CategoryAnalysis',
        components: {
            SeDatePickerPeriod,
            AgGridVue,
            AgCustomCellSelect,
            PendingOverlay,
            AgCellOfEmptyColumn,
            AnalyticsBubbleDiagram,
            Page,
        },
        mixins: [chartMixin],
        data() {
            return {
                title: {
                    isActive: true,
                    text: 'Категорийный анализ',
                },
                tooltipShowDelay: null,
                tooltipHideDelay: null,
                modules: AllModules,
                selectedDates: [],
                dateFacet: {},
                defaultColDef: {
                    sortable: true,
                    // suppressMenu: true,
                    suppressMovable: true,
                    resizable: true,
                    filter: true,
                    tooltip: params => params.value,
                },
                defaultColDefDiagrams: {
                    resizable: true,
                },
                columnDefsMaster: null,
                columnDefsDiagrams: null,
                firstColumnWidth: 200,
                gridOptionsMaster: {
                    rowHeight: 42,
                    headerHeight: 48,
                    columnHoverHighlight: true,
                    suppressRowTransform: true,
                    alignedGrids: [],
                    enableRangeSelection: true,
                    loadingOverlayComponentFramework: 'PendingOverlay',
                    noRowsOverlayComponentFramework: 'PendingOverlay',
                    context: {
                        componentParent: this,
                    },
                    onPaginationChanged: params => {
                        const gridApi = (this.gridApiMaster = params.api);
                        gridApi.setPinnedBottomRowData([this.pinnedBottomData]);
                    },
                    onRangeSelectionChanged: params => {
                        const api = params.api;
                        const cellRanges = api.getCellRanges();
                        if (!cellRanges || cellRanges.length === 0) {
                            this.masterTableSummary.sum = '0';
                            return;
                        }

                        const valuesArr = [];
                        cellRanges.forEach((range, i) => {
                            const startRow = Math.min(
                                range.startRow.rowIndex,
                                range.endRow.rowIndex
                            );
                            const endRow = Math.max(range.startRow.rowIndex, range.endRow.rowIndex);
                            for (let rowIndex = startRow; rowIndex <= endRow; rowIndex++) {
                                range.columns.forEach(function (column) {
                                    const rowModel = api.getModel();
                                    const rowNode = rowModel.getRow(rowIndex);
                                    const value = api.getValue(column, rowNode);
                                    valuesArr.push(Number(value) || 0);
                                });
                            }
                        });
                        const sum = valuesArr.reduce((acc, curr) => acc + curr);
                        // Ищем min среди всех значений, кроме нолей:
                        const min = Math.min(...valuesArr.filter(Boolean));
                        this.masterTableSummary.sum = roundNumber(sum, 1);
                        this.masterTableSummary.number = valuesArr.length;
                        this.masterTableSummary.max = roundNumber(Math.max(...valuesArr), 1);
                        this.masterTableSummary.min = roundNumber(min === Infinity ? 0 : min, 1);
                        this.masterTableSummary.avg = roundNumber(
                            sum / this.masterTableSummary.number,
                            1
                        );
                    },
                },
                gridOptionsDiagrams: {
                    rowHeight: 52,
                    columnHoverHighlight: false,
                    suppressRowTransform: true,
                    suppressRowHoverHighlight: true,
                    alignedGrids: [],
                    headerHeight: 0,
                    domLayout: 'autoHeight',
                    context: {
                        componentParent: this,
                    },
                    loadingOverlayComponentFramework: 'PendingOverlay',
                    noRowsOverlayComponentFramework: 'PendingOverlay',
                    pinnedBottomData: this.pinnedBottomData,
                    onGridReady: params => {
                        this.gridApiDiagrams = params.api;
                        this.gridColumnApiDiagrams = params.columnApi;
                    },
                    onColumnResized: () => {
                        this.firstColumnWidth = this.getFirstColumnWidth();
                    },
                },
                diagramsTableExpanded: false,
                masterTableSummary: {
                    avg: 0,
                    number: 0,
                    min: 0,
                    max: 0,
                    sum: 0,
                },
                gridApiMaster: null,
                gridColumnApiMaster: null,
                gridApiDiagrams: null,
                gridColumnApiDiagrams: null,
                showDateEl: false,
                isLoading: false,
                routeInitialPasdingDone: false,
                masterTableHeights: {
                    cell: 42,
                    header: 48,
                    max: 685,
                    min: 264,
                },
            };
        },
        head() {
            return {
                title: 'Категорийный анализ',
            };
        },
        computed: {
            ...mapGetters('category-analysis', ['getSelectedTabName']),
            ...mapState('category-analysis', [
                'subjectsTotal',
                'subjectsTotal',
                'brandsTable',
                'tableData',
                'selectedTab',
                'tabItems',
                'userSearchParams',
                'brandsFrontend',
                'pinnedBottomData',
            ]),
            selectedTab: {
                get: function () {
                    return this.getSelectedTab;
                },
                set: function (payload) {
                    return this.setStoreKey({ key: 'selectedTab', payload });
                },
            },
            tableDataDiagrams() {
                if (this.tableData) {
                    return this.diagramsTableExpanded
                        ? this.tableData
                        : this.tableData.slice(0, 10);
                }
                return null;
            },
            expandButtonStyle() {
                return {
                    width: `calc(100% - ${this.firstColumnWidth}px)`,
                };
            },
            period() {
                return {
                    isActive: this.showDateEl,
                };
            },
            masterTableHeight() {
                const { cell, header, max, min } = this.masterTableHeights;
                let calcHeight = (2 * header) + ((this.tableData?.length + 1) * cell);
                if (calcHeight > max) {
                    calcHeight = max;
                } else if (calcHeight < min) {
                    calcHeight = min;
                }
                return `${calcHeight}px`;
            },
        },
        watch: {
            brandsTable(val) {
                this.columnDefsMaster = this.getColumnDefsMaster(val);
                this.recalculateDiagramTableColumns();
            },
            selectedTab() {
                this.recalculateDiagramTableColumns();
            },
            selectedDates() {
                this.fetchData();
            },
            brandsFrontend(val) {
                if (this.routeInitialPasdingDone) {
                    this.$router.push({
                        name: this.$route.name,
                        params: this.$route.params,
                        query: {
                            ...this.$route.query,
                            brand: val,
                        }
                    });
                }
            }
        },
        async created() {
            this.routeInitialPasdingDone = false;
            const brandsParsed = this.$route.query.brand;
            let brandsArrNum = null;
            if (brandsParsed) {
                const brandsArr = Array.isArray(brandsParsed) ? brandsParsed : [brandsParsed];
                brandsArrNum = brandsArr.map(el => Number(el));
            }
            this.selectedPeriod = 'week';
            this.tooltipShowDelay = 0;
            this.tooltipHideDelay = 0;
            try {
                const [{ brands }] = await Promise.all([this.fetchUserSearchParams(brandsArrNum)]);
                this.setStoreKey({ key: 'brandsFrontend', payload: brands });
                this.showDateEl = true;
                this.routeInitialPasdingDone = true;
                this.fetchData();
            } catch {
                this.$notify.create({
                    message: 'Произошла ошибка! Попробуйте перезагрузить страницу.',
                    type: 'negative',
                });
            }
        },
        beforeDestroy() {
            this.setStoreKey({ key: 'brandsFrontend', payload: [] });
            this.$router.push({
                name: this.$route.name,
                params: this.$route.params,
                query: {}
            });
        },
        methods: {
            ...mapActions('category-analysis', ['fetchCategoryAnalysis', 'fetchUserSearchParams']),
            ...mapMutations('category-analysis', ['setStoreKey']),
            getColumnDefsMaster(brandsTable) {
                let brandIndex = -1;
                const columnsStart = [
                    {
                        field: 'subject_name',
                        headerName: '',
                    },
                ];

                const brands = brandsTable.reduce((acc, key, index) => {
                    const brandId = key.brand_id;
                    const groupColorClass =
                        index % 2 === 0 ? 'column-color-even' : 'column-color-odd';
                    const brandChildObject = {
                        headerClass: groupColorClass,
                        cellClass: groupColorClass,
                        brand: key,
                        menuTabs: ['filterMenuTab'],
                        icons: {
                            menu: () => '<i class="fas fa-search"></i>',
                        },
                    };
                    brandIndex = index;

                    const valueFormatter = params =>
                        params.value ? `${splitThousands(params.value)} ₽` : '0 ₽';
                    const comparator = (val1, val2) => Number(val1) - Number(val2);

                    if (key.brand_empty) {
                        acc.push(
                            {
                                brand_empty: true,
                                headerClass: groupColorClass,
                                headerGroupComponentFramework: AgCustomCellSelect,
                                headerGroupComponentParams: {
                                    items: brandsTable,
                                    selectValue: key.brand_id,
                                    index: index,
                                    itemText: 'brand',
                                    itemValue: 'brand_id',
                                },
                                children: [{
                                    headerName: '',
                                    suppressSizeToFit: false,
                                    flex: 4,
                                    minWidth: 262,
                                    sortable: false,
                                    suppressMenu: true,
                                    suppressMovable: true,
                                    cellClass: 'empty-column',
                                    cellRendererSelector: params => ({
                                        component: params.node.childIndex === 0 ? 'AgCellOfEmptyColumn' : null,
                                    }),
                                    cellRendererParams: {
                                        message: `Для бренда "${key.brand}" не найдено данных за выбранный период`,
                                    },
                                    rowSpan: params =>
                                        params.node.childIndex === 0 && this.tableData
                                            ? this.tableData.length
                                            : 1,
                                }],
                            }
                        );
                    } else {
                        acc.push({
                            headerClass: groupColorClass,
                            headerGroupComponentFramework: AgCustomCellSelect,
                            marryChildren: true,
                            headerGroupComponentParams: {
                                items: brandsTable,
                                selectValue: key.brand_id,
                                index: index,
                                itemText: 'brand',
                                itemValue: 'brand_id',
                            },
                            children: [
                                {
                                    ...brandChildObject,
                                    field: `sku_count_${brandId}`,
                                    headerName: 'Количество SKU',
                                    headerTooltip: 'Количество SKU',
                                    minWidth: 72,
                                    flex: 1,
                                    tooltipField: `sku_count_${brandId}`,
                                    menuTabs: ['filterMenuTab'],
                                },
                                {
                                    ...brandChildObject,
                                    field: `take_${brandId}`,
                                    headerName: 'Общая выручка',
                                    headerTooltip: 'Общая выручка',
                                    minWidth: 115,
                                    flex: 1,
                                    tooltipField: `take_${brandId}`,
                                    valueFormatter,
                                    comparator,
                                },
                                {
                                    ...brandChildObject,
                                    field: `suppliers_count_${brandId}`,
                                    headerName: 'Поставщики',
                                    headerTooltip: 'Поставщики',
                                    minWidth: 72,
                                    flex: 1,
                                    tooltipField: `suppliers_count_${brandId}`,
                                },
                                {
                                    ...brandChildObject,
                                    field: `avg_take_${brandId}`,
                                    headerName: 'Выручка на SKU',
                                    headerTooltip: 'Выручка на SKU',
                                    minWidth: 83,
                                    flex: 1,
                                    tooltipField: `avg_take_${brandId}`,
                                    valueFormatter,
                                    comparator,
                                },
                            ],
                        });
                    }

                    return acc;
                }, []);
                const columnsEnd = [
                    {
                        headerComponentFramework: AgCustomCellSelect,

                        headerComponentParams: {
                            items: [],
                            label: 'Выберите бренд',
                            index: brandIndex !== -1 ? brandIndex + 1 : 0,
                            addBrand: true,
                        },
                        field: 'subject_more',
                        cellClass: 'empty-column',
                        headerName: '',
                        suppressSizeToFit: false,
                        flex: 4,
                        minWidth: 262,
                        sortable: false,
                        suppressMenu: true,
                        suppressMovable: true,
                        cellRendererSelector: params => ({
                            component: params.node.childIndex === 0 ? 'AgCellOfEmptyColumn' : null,
                        }),
                        cellRendererParams: {
                            message: 'Добавьте бренд, чтобы видеть статистику',
                        },
                        rowSpan: params =>
                            params.node.childIndex === 0 && this.tableData
                                ? this.tableData.length
                                : 1,
                    },
                ];
                return [...columnsStart, ...brands, ...columnsEnd];
            },
            getColumnDefsDiagrams() {
                if (this.columnDefsMaster) {
                    return this.columnDefsMaster.reduce((acc, el) => {
                        if (el.children && !el.brand_empty) {
                            const childOfSelectedType = el.children.find(child =>
                                child.field.includes(this.getSelectedTabName)
                            );
                            if (childOfSelectedType) {
                                acc.push({
                                    field: childOfSelectedType.field,
                                    width: 342,
                                    cellRendererFramework: 'AnalyticsBubbleDiagram',
                                    cellRendererParams: {
                                        brand: childOfSelectedType.brand,
                                        diagramTableDataLength: this.tableDataDiagrams.length,
                                    },
                                });
                            }
                        } else if (el.field === 'subject_more') {
                            acc.push({
                                ...el,
                                rowSpan: null,
                                cellRendererSelector: null,
                                headerComponentFramework: null,
                                headerComponentParams: null,
                            });
                        } else {
                            acc.push(el);
                        }
                        return acc;
                    }, []);
                } else {
                    return null;
                }
            },
            handleDatesChange(val) {
                this.selectedDates = val;
            },
            async fetchData() {
                this.isLoading = true;
                try {
                    const [start_date, end_date] = this.selectedDates;
                    const brands = this.brandsFrontend;
                    if (!brands.length) return;

                    const searchParams = {
                        brands,
                        start_date,
                        end_date,
                    };

                    const params = qs.stringify(searchParams, {
                        arrayFormat: 'brackets',
                        encode: false,
                    });
                    await this.fetchCategoryAnalysis({ params, brands });
                } catch {
                    this.$notify.create({
                        message: 'Произошла ошибка! Попробуйте перезагрузить страницу.',
                        type: 'negative',
                    });
                } finally {
                    this.isLoading = false;
                }
            },
            getFirstColumnWidth() {
                return (
                    this.gridColumnApiDiagrams
                        .getColumnState()
                        .find(el => el.colId === 'subject_name').width || 200
                );
            },
            recalculateDiagramTableColumns() {
                this.columnDefsDiagrams = this.getColumnDefsDiagrams();
            },
        },
    };

    LicenseManager.setLicenseKey(process.env.AG_GRID_KEY);
</script>

<style lang="scss" scoped>
    /* stylelint-disable selector-pseudo-element-no-unknown */
    .category-analysis {
        @include flex-grid-y;

        padding: 32px;

        &__header {
            gap: 8px;

            &::v-deep .el-input__inner {
                background-color: $white;
            }
        }

        &__title {
            font-size: 28px;
            line-height: 1.4;
            color: #2f3640;
            font-weight: 600;
        }

        &__select-period {
            max-width: 115px;
        }

        &__datepicker {
            min-width: 200px;
        }

        &__body {
            @include cardShadow;

            position: relative;
            padding: 16px;
            border-radius: 24px;
            border: 1px solid $border-color;
            background: #fff;

            .progress-loader {
                position: absolute;
                z-index: 2;
                display: flex;
                justify-content: center;
                width: 100%;
                height: 100%;
                padding-top: 200px;
                background: rgba(255, 255, 255, 0.6);
            }
        }

        &-block {
            @include flex-grid-y;

            &__expand-button {
                display: block;
                margin-left: auto;
            }

            &_page-tab {
                width: fit-content;
            }
        }

        &-supertable {
            display: grid;
            grid-template-columns: 160px repeat(4, 1fr);
            grid-template-rows: auto 56px auto;
            padding: 16px;
        }
    }

    .table-footer {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 10px;
        padding: 10px;

        &-data {
            display: flex;
            gap: 4px;
            font-size: 12px;
            line-height: 1.33;

            &__label {
                color: $base-700;
            }

            &__value {
                font-weight: bold;
                color: $base-900;
            }
        }
    }

    .tabs {
        max-width: fit-content;
        border-radius: 8px;
        border: 2px solid black;

        &__tab {
            height: 36px;
            border-radius: 5px;
            border: 2px solid black;
            text-transform: unset;
            letter-spacing: normal;

            &:before {
                opacity: 0 !important;
            }
        }
    }

    .table-custom {
        &::v-deep .ag-row:not(.ag-row-hover) .ag-cell:not(.ag-column-hover).column-color-even {
            background-color: $base-100;
        }

        &::v-deep .ag-theme-alpine {
            & .ag-header,
            & .ag-row-odd:not(.ag-row-hover) {
                background-color: transparent;
            }

            & .ag-header-cell {
                padding: 0 8px;
            }
        }

        &::v-deep .v-text-field__details {
            display: none;
        }

        &::v-deep .ag-header-group-cell {
            padding-top: 4px;
        }

        &::v-deep .ag-floating-bottom-container {
            font-weight: bold;
        }

        &::v-deep .v-select {
            & :not(.v-text-field--single-line):not(.v-text-field--outlined) .v-select__slot {
                & > input {
                    text-align: center;
                }

                & .v-select__selection {
                    width: 100%;
                    text-align: center;
                }
            }
        }
    }

    .table-custom-diagrams {
        &__no-data {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            height: 400px;
            border-radius: 8px;
            border: 1px solid $base-500;
            font-size: 14px;
            color: $base-700;
            font-weight: 400;

            img {
                width: 51px;
                height: 44px;
                object-fit: cover;
            }
        }

        &::v-deep .ag-theme-alpine {
            & .ag-root-wrapper {
                border: none;
            }

            & .ag-header {
                border-bottom: none;
            }

            & .ag-row {
                border: none;

                &:not(:last-child) {
                    & .ag-cell {
                        border-bottom: none;
                    }
                }

                & .ag-cell {
                    border: 1px solid $base-400;

                    &[aria-colindex='1'] {
                        border: none;
                    }

                    &:not([col-id='subject_more']) {
                        border-right: none;
                    }

                    &.empty-column {
                        padding: 0;
                    }
                }
            }
        }

        &::v-deep .ag-cell-value {
            &:not([aria-colindex='1']) {
                overflow: visible;
            }
        }
    }

    .table-custom-master {
        &::v-deep .ag-theme-alpine {
            & .ag-root-wrapper {
                border-color: $base-400;
            }

            & .ag-row {
                border: none;
            }

            & .ag-header-row {
                &:not(:first-child) {
                    & .ag-header-cell {
                        border-color: $base-400;
                    }
                }

                .ag-header-group-cell {
                    padding: 0;
                }

                & .ag-header-cell {
                    &[col-id='subject_more'] {
                        padding: 0;
                    }
                }
            }

            & .ag-header {
                border-color: $base-400;
            }

            & .ag-header-container {
                background-color: $base-400;
            }

            & .ag-set-filter-list {
                height: auto;
            }

            & .ag-cell {
                &[col-id='subject_more'],
                &.empty-column {
                    padding: 0;
                }
            }
        }

        &::v-deep .ag-root-wrapper {
            border-radius: 8px;
        }

        &::v-deep .ag-floating-bottom {
            border-color: $base-400;
        }

        &::v-deep .ag-tabs-header {
            display: none;
        }
    }
</style>
