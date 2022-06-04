<template>
  <div>
    <heading class="mb-6">Статистика</heading>

    <card
        class="bg-90 flex flex-col items-center justify-center"
        style="min-height: 300px"
    >
      <ag-grid-vue style="width: 100%; height: 500px;"
                   class="ag-theme-fresh"
                   :columnDefs="columnDefs"
                   :autoGroupColumnDef="autoGroupColumnDef"
                   :isGroupOpenByDefault="isGroupOpenByDefault"
                   :defaultColDef="defaultColDef"
                   :rowData="rowData"
                   suppressAggFuncInHeader="true"
                   @grid-ready="onGridReady"
                   @first-data-rendered="onFirstDataRendered"
      >
      </ag-grid-vue>
    </card>
  </div>
</template>

<script>
import { AgGridVue } from 'ag-grid-vue'
import { LicenseManager } from 'ag-grid-enterprise'

LicenseManager.setLicenseKey(process.env.MIX_AG_GRID_KEY);

export default {
  metaInfo () {
    return {
      title: 'Статистика',
    }
  },
  data () {
    return {
      rowData: [],
    }
  },
  mounted () {
    //
  },
  components: {
    AgGridVue
  },
  beforeMount () {
    this.defaultColDef = {
      sortable: true
    }
    this.autoGroupColumnDef = {
      headerName: 'Месяц',
      field: 'date',
      filter: 'agDateColumnFilter',
      filterParams: {
        comparator: (filterLocalDateAtMidnight, cellValue) => {
          const dateAsString = cellValue;

          if (dateAsString == null) {
            return 0;
          }

          // In the example application, dates are stored as dd/mm/yyyy
          // We create a Date object for comparison against the filter date
          let day, month, year;
          [day, month, year] = dateAsString.split('.');
          day = Number(day);
          month = Number(month) - 1;
          year = Number(year);
          let cellDate = new Date(year, month, day);

          // Now that both parameters are Date objects, we can compare
          if (cellDate < filterLocalDateAtMidnight) {
            return -1;
          } else if (cellDate > filterLocalDateAtMidnight) {
            return 1;
          }
          return 0;
        }
      }
    }
    this.columnDefs = [
      {
        headerName: 'Дата',
        field: 'month',
        rowGroup: true,
        hide: true,
      },
      /*{
        headerName: 'Дата', field: 'date', type: ['dateColumn'],
        showRowGroup: false,
        cellRenderer: 'agGroupCellRenderer',
        comparator: (valueA, valueB, nodeA, nodeB, isInverted) => {
          return (nodeA.data.timestamp > nodeB.data.timestamp) ? 1 : 1
        }
      },*/
      {
        headerName: 'Регистраций',
        children: [
          {headerName: 'На дату', field: 'registrations_by_date', aggFunc: 'sum'},
          {headerName: 'Всего', field: 'registrations_to_date'},
        ]
      },
      {
        headerName: 'Пользователей, подтвердивших регистрации по почте',
        children: [
          {headerName: 'На дату', field: 'verified_by_date', aggFunc:'sum'},
          {headerName: 'Конверсия на дату, %', field: 'verified_conversion_by_date'},
          {headerName: 'Всего', field: 'verified_to_date'},
          {headerName: 'Конверсия всего, %', field: 'verified_conversion_to_date'},
        ]
      },
      {
        headerName: 'Пользователей добавило аккаунты',
        children: [
          {headerName: 'На дату', field: 'with_account_by_date', aggFunc:'sum'},
          {headerName: 'Конверсия на дату, %', field: 'with_account_conversion_by_date'},
          {headerName: 'Всего', field: 'with_account_to_date'},
          {headerName: 'Конверсия всего, %', field: 'with_account_conversion_to_date'},
        ]
      },
      {
        headerName: 'Оплаты',
        children: [
          {headerName: 'На дату, шт оплат', field: 'payment_count_by_date', aggFunc:'sum'},
          {headerName: 'На дату, сумма оплат', field: 'payment_sum_by_date', aggFunc:'sum'},
          {headerName: 'По счету на дату', field: 'payment_via_bank_count_by_date', aggFunc:'sum'},
          {headerName: 'По карте на дату', field: 'payment_via_card_count_by_date', aggFunc:'sum'},
          {headerName: 'Всего, шт оплат', field: 'payment_count_to_date'},
          {headerName: 'Всего, сумма оплат', field: 'payment_sum_to_date'},
          {headerName: 'По карте всего', field: 'payment_via_card_count_to_date'},
          {headerName: 'По счету всего', field: 'payment_via_bank_count_to_date'},
          {headerName: 'Выставлено счетов на дату', field: 'orders_via_bank_count_by_date', aggFunc:'sum'},
          {headerName: 'Выставлено счетов всего', field: 'orders_via_bank_count_to_date'},
        ]
      },
    ]
  },

  methods: {
    onGridReady (params) {
      this.gridApi = params.api
      this.gridColumnApi = params.columnApi

      const updateData = (data) => params.api.setRowData(data)

      fetch('/nova-vendor/user-statistics/statistics')
          .then((resp) => resp.json())
          .then((data) => updateData(data))
    },
    onFirstDataRendered (params) {
      params.columnApi.autoSizeColumns()
    },
    isGroupOpenByDefault() {
      return true;
    }
  }
}
</script>

<style lang="scss">
@import "~ag-grid-community/dist/styles/ag-grid.css";
@import "~ag-grid-community/dist/styles/ag-theme-fresh.css";
</style>
