import Vue from 'vue';
import Img from '~/components/ag-tables/AgMyProductsTable/components/Img';
import Optimization from '~/components/ag-tables/AgMyProductsTable/components/Optimization';
import VendorCode from '~/components/ag-tables/AgMyProductsTable/components/VendorCode';
import Rating from '~/components/ag-tables/AgMyProductsTable/components/Rating';
import Price from '~/components/ag-tables/AgMyProductsTable/components/Price';
import Checkbox from '~/components/ag-tables/AgMyProductsTable/components/Checkbox';
import Name from '~/components/ag-tables/AgMyProductsTable/components/Name';
import Status from '~/components/ag-tables/AgMyProductsTable/components/Status';
import HeaderCheckbox from '~/components/ag-tables/AgMyProductsTable/components/headerCheckbox';

export const ozonHeaderNames = [
    {
        // pinned: 'left',
        field: 'id',
        headerName: '',
        order: 0,
        cellRendererFramework: Vue.extend(Checkbox),
        headerComponentFramework: Vue.extend(HeaderCheckbox),
        maxWidth: 40,
    },
    {
        field: 'photo_url',
        headerName: 'Фото',
        order: 0,
        sortable: false,
        cellRendererFramework: Vue.extend(Img),
        maxWidth: 80,
    },
    {
        field: 'name',
        headerName: 'Название',
        order: 1,
        sortable: true,
        cellRendererFramework: Vue.extend(Name),
        minWidth: 100,
    },
    {
        field: 'title',
        headerName: 'Название',
        order: 1,
        sortable: true,
        cellRendererFramework: Vue.extend(Name),
        minWidth: 100,
    },
    {
        field: 'brand',
        headerName: 'Бренд',
        order: 2,
        minWidth: 100,
    },
    {
        field: 'price',
        headerName: 'Цена',
        order: 3,
        sortable: true,
        cellRendererFramework: Vue.extend(Price),
        minWidth: 100,
    },
    {
        field: 'optimization',
        headerName: 'Оптимизация',
        order: 4,
        sortable: true,
        cellRendererFramework: Vue.extend(Optimization),
        minWidth: 200,
    },
    {
        field: 'rating',
        headerName: 'Рейтинг',
        order: 5,
        sortable: true,
        cellRendererFramework: Vue.extend(Rating),
        maxWidth: 120,
    },
    {
        field: 'fbo',
        headerName: 'FBO',
        sortable: false,
        order: 6,
        minWidth: 200,
        cellRendererFramework: Vue.extend(VendorCode),
    },
    {
        field: 'fbs',
        headerName: 'FBS',
        sortable: false,
        order: 7,
        minWidth: 200,
        cellRendererFramework: Vue.extend(VendorCode),
    },
    {
        field: 'status',
        headerName: 'Статус',
        sortable: true,
        order: 9,
        cellRendererFramework: Vue.extend(Status),
        maxWidth: 140,
    },
];

export const wbHeaderNames = [
    ...ozonHeaderNames.filter(({ field }) => !['fbo', 'fbs'].includes(field)),
    {
        field: 'nmid',
        headerName: '',
        order: 0,
        cellRenderer: 'agGroupCellRenderer',
        maxWidth: 40,
    },
];
