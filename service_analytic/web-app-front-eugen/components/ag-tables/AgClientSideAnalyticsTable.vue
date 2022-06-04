<template>
    <div class="se-table">
        <AgGridVue
            style="width: 100%; height: 600px"
            :dom-layout="domLayout"
            class="ag-theme-alpine grid-data"
            :grid-options="gridOptions"
            :column-defs="columns"
            :default-col-def="defaultColDef"
            :row-data="rows"
            :row-style="rowStyle"
            :row-height="rowHeight"
            row-selection="multiple"
            :pagination-page-size="pageSize"
            :row-drag-managed="true"
            :pagination="true"
            :locale-text="localeText"
            :side-bar="sideBar"
            :enable-charts="true"
            :modules="modules"
            :animate-rows="true"
            :tooltip-show-delay="tooltipShowDelay"
            :tooltip-hide-delay="tooltipHideDelay"
            @range-selection-changed="onRangeSelectionChanged"
            @grid-ready="onGridReady"
        />
        <Statistics :table-values="tableStatistics" />
    </div>
</template>

<script>
    import { AgGridVue } from '@ag-grid-community/vue';
    import Statistics from '~/components/pages/analytics/brand/statistics/Statistics';
    import { LicenseManager } from '@ag-grid-enterprise/core';
    import { AllModules } from '@ag-grid-enterprise/all-modules';
    import { AG_GRID_LOCALE_RU } from '~utils/ag-grid_locale';
    import {
        categoriesHeaderNames,
        sellersHeaderNames,
    } from '~/components/pages/analytics/brand/helpers/headerNames';
    import { roundNumber } from '~utils/numbers.utils';
    import '@ag-grid-community/core/dist/styles/ag-grid.css';
    import '@ag-grid-community/core/dist/styles/ag-theme-alpine.css';

    LicenseManager.setLicenseKey(process.env.AG_GRID_KEY);

    export default {
        name: 'AgClientSideAnalyticsTable',
        components: {
            AgGridVue,
            Statistics,
        },
        props: {
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
                required: true,
            },
        },
        data: () => ({
            gridApi: null,
            columnApi: null,
            localeText: null,
            rowHeight: 40,
            rowStyle: { border: 'none', background: 'white' },
            domLayout: null,
            modules: AllModules,
            defaultColDef: {
                resizable: true,
                suppressMenu: true,
                floatingFilter: true,
            },
            tooltipShowDelay: null,
            tooltipHideDelay: null,
            gridOptions: {
                suppressPropertyNamesCheck: true,
                enableRangeSelection: true,
                animateRows: true,
            },
            sideBar: {
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
            },
            tableStatistics: {
                avg: 0,
                count: 0,
                min: 0,
                max: 0,
                sum: 0,
            },
        }),
        created() {
            // this.domLayout = 'autoHeight';
            this.localeText = AG_GRID_LOCALE_RU;
            this.tooltipShowDelay = 0;
            this.tooltipHideDelay = 0;
        },
        methods: {
            onGridReady(params) {
                this.gridApi = params.api;
                this.columnApi = params.columnApi;

                const categoriesTable = categoriesHeaderNames
                    .filter(({ autoSize }) => autoSize)
                    .map(({ field }) => field);
                const sellersTable = sellersHeaderNames
                    .filter(({ autoSize }) => autoSize)
                    .map(({ field }) => field);
                params.columnApi.autoSizeColumns([...categoriesTable, ...sellersTable]);
            },
            onRangeSelectionChanged(params) {
                const { api } = params;
                const cellRanges = api.getCellRanges();
                if (!cellRanges || cellRanges.length === 0) {
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

                if (valuesArr.length > 0) {
                    const sum = valuesArr.reduce((acc, curr) => acc + curr);
                    const min = Math.min(...valuesArr.filter(Boolean));

                    this.tableStatistics.sum = roundNumber(sum, 1);
                    this.tableStatistics.count = valuesArr.length;
                    this.tableStatistics.max = roundNumber(Math.max(...valuesArr), 1);
                    this.tableStatistics.min = roundNumber(min === Infinity ? 0 : min, 1);
                    this.tableStatistics.avg = roundNumber(sum / this.tableStatistics.count, 1);
                }
            },
        },
    };
</script>

<style lang="scss" scoped>
    /* stylelint-disable selector-pseudo-element-no-unknown */
    .se-table {
        display: block;
        height: 100%;
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

            & .ag-row {
                height: 40px;
                max-height: 40px;
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
