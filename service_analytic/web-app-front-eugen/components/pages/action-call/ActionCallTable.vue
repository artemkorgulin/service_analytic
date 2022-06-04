<template>
    <div ref="actionCallTable" class="action-call-table">
        <AgGridVue
            style="width: 100%; height: 400px"
            class="ag-theme-alpine grid-data"
            :row-style="rowStyle"
            :column-defs="tableColumns"
            :row-data="total"
            :default-col-def="defaultColDef"
            :grid-options="gridOptions"
            :locale-text="localeText"
            :modules="modules"
            row-selection="single"
            :process-cell-for-clipboard="processCellForClipboard"
            @selection-changed="onRowSelectionChanged"
            @grid-ready="onGridReady"
            @grid-size-changed="getActionTableWidth"
        />
    </div>
</template>

<script>
    /* eslint-disable no-unused-vars, vue/no-unused-components */
    import { mapActions, mapGetters, mapState } from 'vuex';
    import { AgGridVue } from '@ag-grid-community/vue';
    import { LicenseManager } from '@ag-grid-enterprise/core';
    import { AllModules } from '@ag-grid-enterprise/all-modules';
    import '@ag-grid-enterprise/all-modules/dist/styles/ag-grid.css';
    import '@ag-grid-enterprise/all-modules/dist/styles/ag-theme-alpine.css';
    import { AG_GRID_LOCALE_RU } from '~utils/ag-grid_locale';
    import PendingOverlay from '/components/pages/analytics/PendingOverlay';
    import AgCustomCellLink from '/components/ag-tables/AgCustomCellLink';
    import AgCustomCellNumberAndTrend from '/components/ag-tables/AgCustomCellNumberAndTrend';

    LicenseManager.setLicenseKey(process.env.AG_GRID_KEY);

    export default {
        name: 'ActionCallTable',
        components: { AgGridVue, PendingOverlay, AgCustomCellLink, AgCustomCellNumberAndTrend },
        data: () => ({
            gridApi: null,
            columnApi: null,
            tableStatistics: {
                avg: 0,
                count: 0,
                min: 0,
                max: 0,
                sum: 0,
            },
            rowStyle: { border: 'none' },
            defaultColDef: {
                resizable: true,
                sortable: true,
                menuTabs: ['filterMenuTab'],
            },
            gridOptions: {
                suppressPropertyNamesCheck: true,
                enableRangeSelection: true,
                animateRows: true,
                loadingOverlayComponentFramework: 'PendingOverlay',
                noRowsOverlayComponentFramework: 'PendingOverlay',
                pagination: true,
                paginationAutoPageSize: false,
                paginationPageSize: 20,
            },
            localeText: null,
            modules: AllModules,
            tableWidth: 0,
        }),
        computed: {
            ...mapState('analytics', ['products']),
            ...mapState('action-call', ['activeProduct', 'total', 'activeProductId']),
            ...mapGetters('analytics', ['productsRows']),
            tableColumns() {
                const cellRendererNumberAndTrend = (params) => {
                    let value = null;
                    let trend = 0;
                    let alert = false;

                    if (params?.value) {
                        value = params.value.value;
                        trend = params.value.trend;
                        alert = params.value.alert;
                    }

                    return {
                        frameworkComponent: 'AgCustomCellNumberAndTrend',
                        params: { value, trend, alert }
                    };
                };
                const compareByValue = (a, b) => a.value - b.value;
                return [
                    {
                        field: 'name',
                        tooltipField: 'name.name',
                        copyField: 'name',
                        headerName: 'Товары',
                        width: 300,
                        autoHeight: true,
                        filter: 'agTextColumnFilter',
                        filterParams: {
                            textFormatter: val => val.name || val,
                        },
                        cellRendererSelector: (params) => {
                            const { link, name, sku } = params.value;
                            return {
                                frameworkComponent: 'AgCustomCellLink',
                                params: { link, name, sku }
                            };
                        },
                        comparator: (a, b) => a.name.localeCompare(b.name),
                    },
                    {
                        field: 'optimization',
                        copyField: 'value',
                        comparator: compareByValue,
                        headerName: 'Степень оптимизации',
                        width: 160,
                        keyCreator: params => params.data.optimization.value,
                        filter: 'agSetColumnFilter',
                        cellRendererSelector: cellRendererNumberAndTrend,
                        // icons: {
                        //     menu: () => '<i class="fas fa-search"></i>',
                        // },
                    },
                    {
                        field: 'rating',
                        headerName: 'Рейтинг',
                        marryChildren: true,
                        children: [
                            {
                                field: 'rating.user',
                                copyField: 'value',
                                comparator: compareByValue,
                                headerName: 'Ваш товар',
                                width: 100,
                                // icons: {
                                //     menu: () => '<i class="fas fa-search"></i>',
                                // },
                                keyCreator: params => {
                                    console.log('keyCreator ', params);
                                    return params.data.rating.user.value;
                                },
                                filter: 'agSetColumnFilter',
                                // valueGetter: (params) => params.data.rating.user.value,
                                cellRendererSelector: cellRendererNumberAndTrend,
                            },
                            {
                                field: 'rating.top36',
                                copyField: 'value',
                                comparator: compareByValue,
                                headerName: 'Топ 36',
                                width: 80,
                                keyCreator: params => params.data.rating.top36.value,
                                filter: 'agSetColumnFilter',
                                cellRendererSelector: cellRendererNumberAndTrend,
                            }
                        ]
                    },
                    {
                        field: 'feedbacks',
                        headerName: 'Количество отзывов',
                        marryChildren: true,
                        children: [
                            {
                                field: 'feedbacks.user',
                                copyField: 'value',
                                comparator: compareByValue,
                                headerName: 'Ваш товар',
                                 width: 100,
                                keyCreator: params => params.data.feedbacks.user.value,
                                filter: 'agSetColumnFilter',
                                cellRendererSelector: cellRendererNumberAndTrend,
                            },
                            {
                                field: 'feedbacks.top36',
                                copyField: 'value',
                                comparator: compareByValue,
                                headerName: 'Топ 36',
                                 width: 80,
                                keyCreator: params => params.data.feedbacks.top36.value,
                                filter: 'agSetColumnFilter',
                                cellRendererSelector: cellRendererNumberAndTrend,
                            }
                        ]
                    },
                    {
                        field: 'avg_position_category',
                        headerName: 'Средняя позиция в категории',
                        children: [
                            {
                                field: 'avg_position_category.user',
                                copyField: 'value',
                                comparator: compareByValue,
                                headerName: 'Ваш товар',
                                 width: 160,
                                keyCreator: params => params.data.avg_position_category.user.value,
                                filter: 'agSetColumnFilter',
                                cellRendererSelector: cellRendererNumberAndTrend,
                            },
                        ]
                    },
                    {
                        field: 'avg_position_search',
                        headerName: 'Средняя позиция в поиске',
                        children: [
                            {
                                field: 'avg_position_search.user',
                                copyField: 'value',
                                comparator: compareByValue,
                                headerName: 'Ваш товар',
                                 width: 160,
                                keyCreator: params => params.data.avg_position_search.user.value,
                                filter: 'agSetColumnFilter',
                                cellRendererSelector: cellRendererNumberAndTrend,
                            },
                        ]
                    },
                    {
                        field: 'images_count',
                        headerName: 'Количество изображений',
                        marryChildren: true,
                        children: [
                            {
                                field: 'images_count.user',
                                headerName: 'Ваш товар',
                                copyField: 'value',
                                comparator: compareByValue,
                                 width: 100,
                                keyCreator: params => params.data.images_count.user.value,
                                filter: 'agSetColumnFilter',
                                cellRendererSelector: cellRendererNumberAndTrend,
                            },
                            {
                                field: 'images_count.top36',
                                headerName: 'Топ 36',
                                copyField: 'value',
                                comparator: compareByValue,
                                 width: 80,
                                keyCreator: params => params.data.images_count.top36.value,
                                filter: 'agSetColumnFilter',
                                cellRendererSelector: cellRendererNumberAndTrend,
                            }
                        ]
                    },
                    {
                        headerName: 'Защита авторства',
                        children: [
                            {
                                field: 'authorship_protection',
                                copyField: 'value',
                                comparator: compareByValue,
                                headerName: '',
                                width: 150,
                                keyCreator: params => params.data.authorship_protection.value,
                                filter: 'agSetColumnFilter',
                                cellRendererSelector: cellRendererNumberAndTrend,
                            },
                        ],
                    },
                    {
                        field: 'sales',
                        headerName: 'Продажи, ₽',
                        marryChildren: true,
                        children: [
                            {
                                field: 'sales.user',
                                headerName: 'Ваш товар',
                                width: 100,
                                sortable: false,
                                filter: false,
                                supressMenu: true,
                                headerClass: 'supressMenu',
                                cellRenderer: 'agSparklineCellRenderer',
                                cellRendererParams: {
                                    sparklineOptions: {
                                        type: 'column',
                                        axis: {
                                            strokeWidth: 1,
                                        },
                                        fill: '#5DDC98',
                                        stroke: '#5DDC98',
                                        highlightStyle: { fill: '#FAC858' },
                                    },
                                },
                            },
                            {
                                field: 'sales.top36',
                                headerName: 'Топ 36',
                                width: 100,
                                sortable: false,
                                filter: false,
                                supressMenu: true,
                                headerClass: 'supressMenu',
                                cellRenderer: 'agSparklineCellRenderer',
                                cellRendererParams: {
                                    sparklineOptions: {
                                        type: 'column',
                                        axis: {
                                            strokeWidth: 1,
                                        },
                                        fill: '#5DDC98',
                                        stroke: '#5DDC98',
                                        highlightStyle: { fill: '#FAC858' },
                                    },
                                },
                            }
                        ]
                    },
                ];
            },
            needToStretchColumns() {
                const columnsWidth = this.tableColumns?.reduce((acc, curr) => acc += curr.width || curr.children?.reduce((accum, current) => accum += current.width || 0, 0), 0);
                return columnsWidth < this.tableWidth;
            },
            columnsFitTrigger() {
                return this.needToStretchColumns ? this.tableWidth : false;
            },
        },
        watch: {
            columnsFitTrigger(val) {
                this.gridApi?.sizeColumnsToFit(val);
            },
        },
        created() {
            this.localeText = AG_GRID_LOCALE_RU;
        },
        methods: {
            ...mapActions('action-call', [
                'fetchProductData',
            ]),
            onRowSelectionChanged(params) {
                const { api } = params;
                const selectedRow = api.getSelectedRows();
                const selectedRowId = selectedRow[0].id;
                if (selectedRowId) {
                    this.fetchProductData(selectedRowId);
                }
            },
            onGridReady(params) {
                this.gridApi = params.api;
                this.columnApi = params.columnApi;
                this.getActionTableWidth();
                this.gridApi.getModel().rowsToDisplay[0].setSelected(true);
            },
            getActionTableWidth() {
                this.tableWidth = this.$refs.actionCallTable.clientWidth;
            },
            processCellForClipboard(params) {
                const { copyField = null } = params.column.colDef;
                if (
                    copyField &&
                    (params.value || params.value !== null)
                ) {
                    return params.value[copyField];
                }
                return params.value;
            },
        }
    };
</script>

<style scoped lang="scss">
    /* stylelint-disable selector-pseudo-element-no-unknown */
    .action-call-table::v-deep .ag-theme-alpine {
        & .ag-root-wrapper {
            border-radius: 16px;
            border-color: $border-color;

            &-body.ag-layout-normal.ag-focus-managed {
                height: 100%;
            }
        }

        & .ag-header {
            //height: 40px !important;
            //min-height: 40px !important;
            border: none;
            background-color: $border-color;
        }

        & .ag-header-cell,
        & .ag-header-group-cell {
            padding-right: 12px;
            padding-left: 12px;
        }

        & .supressMenu {
            &.ag-header-cell.ag-header-active .ag-header-cell-menu-button {
                opacity: 0 !important;
            }
        }

        & .ag-header-row {
            //height: 40px !important;
            background-color: $border-color;

            & .ag-header-group-text {
                white-space: break-spaces;
            }
        }

        & .ag-header-cell-text {
            font-family: $heading-font-family;
            font-size: 12px;
            line-height: 16px;
            font-weight: 500;
        }

        & .ag-ltr .ag-side-bar-right {
            border-left-color: $border-color;
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

        & .ag-cell {
            padding-right: 0;
            padding-left: 0;
        }

        & .ag-row {
            &.ag-row-odd {
                background-color: $base-100;
            }

            &.ag-row-hover {
                background-color: $background-2;
            }

            &.ag-row-selected {
                background-color: #E9E9EF;
            }

            &:not(:last-child) {
                border-bottom: 1px solid $color-gray-light !important;
            }
        }

        & .ag-tabs-header {
            display: none;
        }
    }
</style>
