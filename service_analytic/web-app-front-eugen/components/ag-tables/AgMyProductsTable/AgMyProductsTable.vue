<template>
    <div class="products_table">
        <AgGridVue
            style="width: 100%"
            :dom-layout="domLayout"
            class="ag-theme-alpine"
            :grid-options="gridOptions"
            :server-side-store-type="serverSideStoreType"
            :column-defs="columnDefs"
            :default-col-def="defaultColDef"
            :row-style="rowStyle"
            :row-data="rows"
            :row-height="60"
            :row-model-type="rowModelType"
            :locale-text="localeText"
            :modules="modules"
            :suppress-row-click-selection="true"
            :row-selection="rowSelection"
            :master-detail="true"
            :animate-rows="true"
            :detail-cell-renderer="detailCellRenderer"
            @grid-ready="onGridReady"
        />
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex';
    import { AgGridVue } from '@ag-grid-community/vue';
    import { LicenseManager } from '@ag-grid-enterprise/core';
    import { AllModules } from '@ag-grid-enterprise/all-modules';
    import { AG_GRID_LOCALE_RU } from '~utils/ag-grid_locale';
    import Options from '~/components/ag-tables/AgMyProductsTable/components/Options';
    import {
        ozonHeaderNames,
        wbHeaderNames,
    } from '~/components/ag-tables/AgMyProductsTable/helpers/headerNames';
    import '@ag-grid-community/core/dist/styles/ag-grid.css';
    import '@ag-grid-community/core/dist/styles/ag-theme-alpine.css';

    LicenseManager.setLicenseKey(process.env.AG_GRID_KEY);

    export default {
        name: 'AgMyProductsTable',
        components: {
            AgGridVue,
            // eslint-disable-next-line vue/no-unused-components
            Options,
        },
        props: {
            columns: {
                type: Array,
                required: true,
            },
            rows: {
                type: Array,
                required: true,
            },
            pageSize: {
                type: Number,
                required: true,
            },
        },
        data: () => ({
            loadRow: null,
            gridApi: null,
            columnApi: null,
            localeText: null,
            rowModelType: null,
            paginationPageSize: null,
            cacheBlockSize: null,
            serverSideStoreType: null,
            modules: AllModules,
            domLayout: null,
            gridOptions: {
                suppressPropertyNamesCheck: true,
                suppressContextMenu: true,
                suppressCellSelection: true,
                suppressRowHoverHighlight: true,
                detailRowAutoHeight: true,
            },
            defaultColDef: {
                resizable: true,
                suppressMenu: true,
                sortable: true,
                flex: 1,
            },
            rowSelection: null,
            rowStyle: { background: 'white' },
            detailCellRenderer: null,
        }),
        computed: {
            ...mapGetters(['isSelectedMp']),
            columnDefs() {
                return this.columns
                    .map(col => {
                        if (this.isSelectedMp.id === 1) {
                            return {
                                field: col,
                                ...ozonHeaderNames.find(({ field }) => field === col),
                            };
                        } else {
                            return {
                                field: col,
                                ...wbHeaderNames.find(({ field }) => field === col),
                            };
                        }
                    })
                    .filter(item => Object.prototype.hasOwnProperty.call(item, 'headerName'))
                    .sort((a, b) => a['order'] - b['order']);
            },
        },
        watch: {
            rows: {
                deep: true,
                handler(value) {
                    try {
                        this.loadRow.success({
                            rowData: value,
                            rowCount: value.length,
                        });
                    } catch (error) {
                        console.error(error);
                    }
                },
            },
        },
        created() {
            this.rowModelType = 'serverSide';
            this.serverSideStoreType = 'partial';
            this.domLayout = 'autoHeight';
            this.localeText = AG_GRID_LOCALE_RU;
            this.rowSelection = 'multiple';
            this.detailCellRenderer = 'Options';
        },
        methods: {
            ...mapActions('products', ['SELECT_PRODUCT', 'DESELECT_PRODUCT']),

            onGridReady(params) {
                this.gridApi = params.api;
                this.columnApi = params.columnApi;
                let firstTime = true;

                this.gridApi.setServerSideDatasource({
                    getRows: params => {
                        const sortModel = params.request.sortModel;
                        this.loadRow = params;

                        if (firstTime) {
                            params.success({
                                rowData: this.rows,
                                rowCount: this.rows.length,
                            });
                            firstTime = false;
                        } else {
                            this.$emit('sortChanged', sortModel);
                        }
                    },
                });
            },
        },
    };
</script>

<style scoped lang="scss">
    /* stylelint-disable selector-pseudo-element-no-unknown */
    .products_table {
        &::v-deep .ag-theme-alpine {
            font-family: $body-font-family;

            & .ag-root-wrapper {
                border-color: $border-color;
                border-bottom: none;
            }

            & .ag-header {
                border: none;

                &-row {
                    background-color: $border-color;
                }

                &-icon {
                    color: $primary-color;
                }

                &-cell-text {
                    font-family: $heading-font-family;
                    font-size: 12px;
                    line-height: 16px;
                    font-weight: 500;
                }

                &-cell {
                    .ag-floating-filter .ag-focus-managed:after {
                        border: none;
                    }
                }
            }

            & .ag-icon-tree-open:hover,
            & .ag-icon-tree-closed:hover {
                color: $primary-color;
            }

            & .ag-checkbox-input-wrapper.ag-checked:after,
            & .ag-radio-button-input-wrapper.ag-checked:after {
                color: $primary-color;
            }
        }
    }
</style>
