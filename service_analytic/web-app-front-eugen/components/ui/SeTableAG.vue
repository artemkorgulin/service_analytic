<template>
    <div class="se-table">
        <template v-if="pagination">
            <AgGridVue
                ref="table"
                style="width: 100%; height: 600px"
                :dom-layout="domLayout"
                class="ag-theme-alpine grid-data"
                :grid-options="gridOptions"
                :column-defs="columns"
                :default-col-def="defaultColDef"
                row-selection="multiple"
                :row-drag-managed="true"
                :row-model-type="rowModelType"
                :pagination="pagination"
                :pagination-page-size="pageSize"
                :cache-block-size="pageSize"
                :server-side-store-type="serverSideStoreType"
                :locale-text="localeText"
                :side-bar="sideBar"
                :enable-charts="true"
                :auto-group-column-def="autoGroupColumnDef"
                :modules="modules"
                :tooltip-show-delay="tooltipShowDelay"
                :tooltip-hide-delay="tooltipHideDelay"
                :framework-components="frameworkComponents"
                @range-selection-changed="onRangeSelectionChanged"
                @grid-ready="onGridReady"
            />
        </template>
        <template v-else>
            <AgGridVue
                ref="table"
                style="width: 100%; height: 600px"
                :dom-layout="domLayout"
                class="ag-theme-alpine grid-data"
                :grid-options="gridOptions"
                :column-defs="columns"
                :default-col-def="defaultColDef"
                :row-data="rows"
                row-selection="multiple"
                :row-drag-managed="true"
                :locale-text="localeText"
                :side-bar="sideBar"
                :enable-charts="true"
                :auto-group-column-def="autoGroupColumnDef"
                :modules="modules"
                :animate-rows="true"
                :tooltip-show-delay="tooltipShowDelay"
                :tooltip-hide-delay="tooltipHideDelay"
                :framework-components="frameworkComponents"
                @range-selection-changed="onRangeSelectionChanged"
                @grid-ready="onGridReady"
            />
        </template>
        <Statistics v-if="!nostat" :table-values="tableStatistics" />
    </div>
</template>
<script>
    /* eslint-disable */
    import { AgGridVue } from '@ag-grid-community/vue';
    import PerfectScrollbar from 'perfect-scrollbar';
    import Statistics from '~/components/pages/analytics/brand/statistics/Statistics';
    import { LicenseManager } from '@ag-grid-enterprise/core';
    import { AllModules } from '@ag-grid-enterprise/all-modules';
    import { AG_GRID_LOCALE_RU } from '~utils/ag-grid_locale';
    import { roundNumber } from '~utils/numbers.utils';
    import '@ag-grid-community/core/dist/styles/ag-grid.css';
    import '@ag-grid-community/core/dist/styles/ag-theme-alpine.css';
    import {
        categoriesHeaderNames,
        productsHeaderNames,
        sellersHeaderNames,
    } from '~/components/pages/analytics/brand/helpers/headerNames';
    import AgCellOfEmptyColumn from '/components/ag-tables/AgCellOfEmptyColumn';
    LicenseManager.setLicenseKey(process.env.AG_GRID_KEY);

    export default {
        name: 'SeTableAG',
        components: {
            AgGridVue,
            Statistics,
            AgCellOfEmptyColumn,
        },
        props: {
            nostat: Boolean,
            nobar: Boolean,
            rows: {
                type: Array,
                required: true,
            },
            columns: {
                type: Array,
                required: true,
            },
            pageSize: {
                type: Number,
            },
            total: {
                type: Number,
                default: 1,
            },
            rowHeight: {
                type: Number,
                default: 34,
            },
            pagination: Boolean,
            сomponents: {
                type: Object,
            },
            callBack: Function,
            noRowsMessage: {
                type: null,
                default: null,
            },
            noRowsOverlay: {
                type: Boolean,
                default: false,
            },
        },
        data: () => ({
            loadRow: null, // get params from getRows
            gridApi: null,
            columnApi: null,
            localeText: null,
            rowModelType: null,
            serverSideStoreType: null,
            paginationPageSize: null,
            cacheBlockSize: null,
            autoGroupColumnDef: null,
            modules: AllModules,
            domLayout: null,
            defaultColDef: {
                resizable: true,
                suppressMenu: true,
            },
            gridOptions: {
                enableRangeSelection: true,
                suppressCopyRowsToClipboard: true,
            },
            tooltipShowDelay: null,
            tooltipHideDelay: null,
            frameworkComponents: null,
            tableStatistics: {
                avg: 0,
                count: 0,
                min: 0,
                max: 0,
                sum: 0,
            },
        }),
        computed: {
            enableServerSideNoRowsOverlay() {
                return this.noRowsOverlay && this.pagination;
            },
            showServerSideNoRowsOverlay() {
                return this.enableServerSideNoRowsOverlay && !this.rows.length;
            },
            showServerSideNoRowsOverlayTrigger() {
                return this.showServerSideNoRowsOverlay + this.gridApi;
            },

            sideBar() {
                return this.nobar
                    ? {
                          hiddenByDefault: true,
                      }
                    : {
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
                              {
                                  id: 'filters',
                                  labelDefault: '',
                                  labelKey: '',
                                  iconKey: 'filter',
                                  toolPanel: 'agFiltersToolPanel',
                              },
                          ],
                      };
            },
        },
        watch: {
            rows: {
                handler() {
                    if (!this.pagination) {
                        return;
                    }
                    if (this.loadRow?.success) {
                        this.loadRow.success({
                            rowData: this.rows,
                            rowCount: this.total,
                        });
                    }
                },
                deep: true,
            },
            showServerSideNoRowsOverlayTrigger: {
                handler() {
                    if (!this.enableServerSideNoRowsOverlay || !this.gridApi) {
                        return;
                    }
                    if (this.showServerSideNoRowsOverlay) {
                        this.gridApi.showNoRowsOverlay();
                    } else {
                        this.gridApi.hideOverlay();
                    }
                },
            },
        },
        created() {
            this.gridOptions.getRowHeight = () => this.rowHeight;
            this.gridOptions.onCellMouseOver = params => {
                this.$emit('mouseover', params);
            };
            this.gridOptions.onCellMouseOut = params => {
                this.$emit('mouseout', params);
            };
            this.rowModelType = 'serverSide';
            this.serverSideStoreType = 'partial';
            this.localeText = AG_GRID_LOCALE_RU;

            this.frameworkComponents = this.сomponents;
            this.tooltipShowDelay = 0;
            this.tooltipHideDelay = 0;

            const isFilter = this.columns.filter(item => !!item.filter);
            this.defaultColDef.floatingFilter = !!isFilter.length;
        },
        methods: {
            autoSizeColumns(params) {
                const productsTable = productsHeaderNames
                    .filter(({ autoSize }) => autoSize)
                    .map(({ field }) => field);
                const categoriesTable = categoriesHeaderNames
                    .filter(({ autoSize }) => autoSize)
                    .map(({ field }) => field);
                const sellersTable = sellersHeaderNames
                    .filter(({ autoSize }) => autoSize)
                    .map(({ field }) => field);
                params.columnApi.autoSizeColumns([
                    ...productsTable,
                    ...categoriesTable,
                    ...sellersTable,
                ]);
            },
            onGridReady(params) {
                this.gridApi = params.api;
                this.columnApi = params.columnApi;
                let firstTime = false;

                this.$emit('onGridReady', { gridApi: params.api, columnApi: params.columnApi });

                const agBodyViewport = this.$refs.table.$el.querySelector('.ag-body-viewport');
                const agBodyHorizontalViewport = this.$refs.table.$el.querySelector(
                    '.ag-body-horizontal-scroll-viewport'
                );
                const psViewport = new PerfectScrollbar(agBodyViewport);
                psViewport.update();
                const psHorizontal = new PerfectScrollbar(agBodyHorizontalViewport);
                psHorizontal.update();

                if (!this.pagination) {
                    return;
                }

                this.gridApi.setServerSideDatasource({
                    getRows: params => {
                        const currentPage = this.gridOptions.api.paginationGetCurrentPage();
                        const sortModel = params.request.sortModel;
                        const filterModel = params.request.filterModel;

                        this.loadRow = params;

                        if (!firstTime) {
                            firstTime = true;

                            this.loadRow.success({
                                rowData: this.rows,
                                rowCount: this.total,
                            });
                        } else {
                            this.$emit('dataChanged', currentPage, sortModel, filterModel);
                        }
                    },
                });
            },
            onRangeSelectionChanged(params) {
                const { api } = params;
                const cellRanges = api.getCellRanges();
                if (!cellRanges || !cellRanges?.length) {
                    this.tableStatistics.sum = '0';
                }
                const valuesArr = [];
                cellRanges.forEach((range, i) => {
                    const startRow = Math.min(range.startRow.rowIndex, range.endRow.rowIndex);
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
                if (valuesArr?.length) {
                    const sum = valuesArr.reduce((acc, curr) => acc + curr);
                    const min = Math.min(...valuesArr.filter(Boolean));
                    this.tableStatistics.sum = roundNumber(sum, 1);
                    this.tableStatistics.count = valuesArr?.length;
                    this.tableStatistics.max = roundNumber(Math.max(...valuesArr), 1);
                    this.tableStatistics.min = roundNumber(min === Infinity ? 0 : min, 1);
                    this.tableStatistics.avg = roundNumber(sum / this.tableStatistics.count, 1);
                }
            },
        },
    };
</script>
<style lang="scss">
    .se-table {
        .grid-data .ag-body-viewport,
        .grid-data .ag-body-horizontal-scroll-viewport {
            position: relative;
            overflow: hidden !important;
        }

        .ps__rail-x,
        .ps__rail-y {
            opacity: 0.6;
        }
    }
</style>
<style lang="scss" scoped>
    /* stylelint-disable selector-pseudo-element-no-unknown */
    .se-table {
        .ag-cell {
            font-size: 12px !important;
        }

        display: block;
        margin-top: 16px;

        &::v-deep .ag-theme-alpine {
            font-family: $body-font-family;

            & .ag-center-cols-clipper,
            & .ag-center-cols-container {
                min-height: 600px !important;
            }

            & .ag-root-wrapper {
                border-radius: 16px;
                border-color: $border-color;
            }

            & .ag-popup-child:not(.ag-tooltip-custom) {
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
</style>
