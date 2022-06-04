<template>
    <div class="product__container">
        <div class="table-head pt-4 pr-3 pl-3 pb-4">
            <div class="custom-search custom-search--width-middle">
                <SearchInput
                    v-model="search"
                    color="#710bff"
                    background-color="#f9f9f9"
                    label="Поиск"
                    outline-light
                />
                <icon-loading :pending="pending.show && pending.type === 'search'" size="20px" />
            </div>

            <ProductsCategories />
            <div class="table-filter__group">
                <VBtn
                    :class="$style.BtnDelete"
                    :disabled="!selectedProducts.length"
                    outlined
                    @click="removeProducts"
                >
                    <SvgIcon name="outlined/deleteTrash" />
                </VBtn>
            </div>
        </div>
        <div class="product__wrapper">
            <div class="product-table">
                <transition name="fade__slow">
                    <AgMyProductsTable
                        v-if="items.length"
                        :page-size="10"
                        :columns="Object.keys(items[0])"
                        :rows="items"
                        @sortChanged="sortChanged"
                    />
                </transition>
            </div>
        </div>

        <confirm ref="confirm" />
    </div>
</template>

<script>
    /* eslint-disable */
    import { mapActions, mapGetters, mapState } from 'vuex';
    import { debounce } from '~utils/helper.utils';
    import { errorHandler } from '~utils/response.utils';
    import IconLoading from '~/components/common/IconLoading.vue';
    import Confirm from '~/components/common/Confirm.vue';
    import AgMyProductsTable from '~/components/ag-tables/AgMyProductsTable/AgMyProductsTable';
    import onboarding from '~mixins/onboarding.mixin';

    export default {
        name: 'Products',
        mixins: [onboarding],
        components: {
            AgMyProductsTable,
            IconLoading,
            Confirm,
        },
        props: {
            items: {
                required: true,
                type: Array,
                default: () => [],
            },
        },
        data() {
            return {
                pageLoading: false,
                pageSetting: {
                    perPage: 25,
                    page: 1,
                    search: '',
                },
                lastPage: undefined,
                steps: 0,
                search: '',
                option: 'big-template',
                dialogImport: false,
            };
        },
        computed: {
            ...mapGetters(['isSelectedMp']),
            ...mapState('products', ['selectedProducts', 'activeDeleteBtn']),
            ...mapGetters({
                pending: 'products/GET_PENDING',
                getSelectedProducts: 'products/GET_ITEMS_SELECTED',
                isSelectAll: 'products/IS_SELECT_ALL',
                displayOptionBig: 'products/GET_DISPLAY_OPTION',
                marketplaceSlug: 'getSelectedMarketplaceSlug',
            }),
            selectAll: {
                get() {
                    return this.isSelectAll;
                },
                set() {
                    this.selectAllProducts();
                },
            },
            getDeleteUrl() {
                switch (this.marketplaceSlug) {
                    case 'wildberries':
                        return '/api/vp/v2/wildberries/products/delete';

                    default:
                        return '/api/vp/v2/remove-products';
                }
            },
        },
        watch: {
            search() {
                this.$store.commit('products/SET_SEARCH', this.search || -1);

                debounce({
                    time: 1000,
                    collBack: () => {
                        this.loadProducts({
                            type: 'search',
                            reload: false,
                            marketplace: this.marketplaceSlug,
                        });
                    },
                });
            },
        },
        mounted() {
            this.createOnBoarding();
        },
        methods: {
            ...mapActions({
                loadProducts: 'products/LOAD_PRODUCTS',
                selectAllProducts: 'products/SELECT_ALL_PRODUCTS',
                setDisplayOption: 'products/SET_DISPLAY_OPTION',
            }),
            createOnBoarding() {
                const elements = [
                    {
                        el: document.querySelector('.ob-add-product-01'),
                        intro: 'Нажмите, чтобы выбрать и добавить товары',
                        pos: 'left',
                        callback: () => {
                            this.$store.commit('onBoarding/setOnboardActive', true);
                        },
                        clickToNext: true,
                    },
                ];

                const isDisplayOnboard = !this.checkRouteForOnboarding();

                const createOnBoardingParams = {
                    elements,
                    routeNameFirstStep: this.$route.name,
                    isDisplayOnboard,
                };

                this.createOnBoardingByParams(createOnBoardingParams);
            },
            removeProducts() {
                this.$refs.confirm.show({
                    title: 'Удалить выбранные товары?',
                    btn: {
                        confirm: {
                            text: 'Удалить',
                            cls: 'primary-btn primary-btn--size-middle primary-btn--pink',
                        },
                        cancel: {
                            text: 'Отмена',
                            cls: 'default-btn default-btn--size-middle',
                        },
                    },
                    confirm: () => {
                        this.$axios
                            .$delete(this.getDeleteUrl, {
                                params: {
                                    ids: this.selectedProducts,
                                },
                            })
                            .then(() => {
                                this.loadProducts({
                                    type: 'common',
                                    reload: false,
                                    marketplace: this.marketplaceSlug,
                                });
                            })
                            .catch(({ response }) => {
                                errorHandler(response, this.$notify);
                            });
                    },
                });
            },
            async sortChanged(sortModel) {
                this.pageLoading = true;
                const { colId, sort } = sortModel[0] || '';

                this.$store.commit('products/SET_SORT', {
                    sortBy: colId || 'id',
                    sortType: sort || 'asc',
                });

                await this.loadProducts({
                    type: 'common',
                    marketplace: this.isSelectedMp.key,
                });

                this.pageLoading = false;
            },
        },
    };
</script>

<style lang="scss" module>
    .stepper {
        :global(.v-stepper__label) {
            text-align: center;
        }
    }

    .BtnSelectAll {
        margin-right: 8px;

        &:global(.v-btn):not(:global(.v-btn--round)):global(.v-size--default) {
            min-width: 40px;
            padding: 0;
        }
    }

    .BtnDelete {
        margin-left: 8px;

        &:global(.v-btn):not(:global(.v-btn--round)):global(.v-size--default) {
            min-width: 40px;
            padding: 0;

            &:global(.v-btn--outlined):not(:global(.v-btn--disabled)) {
                border-color: $color-pink-dark;
                color: $color-pink-dark;
                transition: all 200ms ease-in-out;
            }
        }
    }

    .btnWrapper {
        gap: 16px;
        display: flex;
        margin-top: 24px;

        @include respond-to(sm) {
            flex-direction: column;
        }
    }

    .btn {
        flex: 1 1 auto;
    }

    .SearchInput {
        width: 100%;
    }
</style>

<style lang="scss">
    /* stylelint-disable declaration-no-important */

    .custom-dialog__wrap {
        padding: 24px;
    }

    .product {
        &__container {
            overflow: hidden;
            display: flex;
            border-radius: 24px;
            background-color: $color-light-background;
            flex-direction: column;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
        }

        &__header {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 16px;

            &-box {
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                margin-right: auto;
            }

            &-control-btn {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin: 8px 0;

                @media (max-width: 1310px) {
                    width: 100%;
                }

                // button.el-button.default-btn {
                //     margin: 0;
                // }
            }

            &-control-txt {
                margin-right: 18px;

                span {
                    display: block;
                    text-align: right;
                    font-size: 14px;

                    @media (max-width: 1310px) {
                        text-align: left;
                    }
                }
            }

            &-control-title {
                margin-bottom: 1px;
                font-weight: 700;
            }

            .title-h1 {
                margin: 0 16px 8px 0;
            }

            // .el-button {
            //     margin: 0 16px 8px 0;

            //     &[class*="el-icon-"] + span {
            //         margin-left: 12px;
            //     }
            // }
        }

        &__sortable {
            display: flex;
        }

        &__wrapper {
            position: relative;
            overflow: auto hidden;
        }
    }

    .product-table,
    .table-filter {
        //min-width: size(1490);
        width: 100%;

        //@include phone-large {
        //    min-width: 1222px;
        //}
    }

    .product-table {
        display: flex;
        flex-direction: column;
        min-width: 850px;

        td {
            border-top: 1px solid #e9edf2;
            border-bottom: 1px solid #e9edf2;
        }

        &__block {
            display: flex;
            border-radius: 8px;
            border: 1px solid $color-gray-light;
            background-color: $color-light-background;
            flex-direction: column;

            &:not(:last-child) {
                margin-bottom: 8px;
            }

            &--big {
                &.product-table__block-group {
                    .product-table__col {
                        &:nth-child(1) {
                            position: relative;
                            justify-content: space-between;

                            &:after {
                                content: '';
                                position: absolute;
                                top: 50%;
                                left: 0;
                                width: 100%;
                                height: calc(100% + 1rem);
                                border-radius: 4px;
                                background-color: $color-gray-light;
                                transform: translateY(-50%);
                            }

                            .product-table__arrow {
                                margin-bottom: size(-8);
                            }
                        }

                        &:nth-child(2) {
                            display: grid;
                            align-self: flex-start;
                            width: size(104);
                            min-width: size(104);
                            height: unset;
                            padding: 0;
                            grid-template-columns: repeat(3, 1fr);
                            // grid-gap: size(4);
                        }

                        &:nth-child(4) {
                            justify-content: center;
                        }
                    }
                }

                .product-table__box {
                    min-height: size(144);
                    padding: 1rem 0.5rem;
                }

                .product-table__col {
                    &:nth-child(1) {
                        align-items: center;
                        width: size(32);
                    }

                    &:nth-child(2) {
                        justify-content: center;
                        width: size(112);
                        height: size(112);
                        padding: 4px;
                    }

                    &:nth-child(3) {
                        width: size(530);

                        .product-table__row {
                            margin-top: -4px;
                        }
                    }

                    &:nth-child(4) {
                        width: size(280);
                    }

                    &:nth-child(5) {
                        justify-content: center;
                        width: size(400);
                        margin: 0 auto !important;
                    }

                    &:nth-child(6) {
                        width: size(120);
                    }

                    &:not(:last-child) {
                        margin-right: size(16);
                    }

                    &:last-child {
                        margin-left: size(16);
                    }
                }

                .product-table__product-view,
                .product-table__rating,
                .product-table__messages,
                .product-table__code-product {
                    margin-bottom: 8px;
                }

                .product-table__description {
                    overflow: hidden;
                    display: -webkit-box;
                    -webkit-line-clamp: 4;
                    -webkit-box-orient: vertical;
                }
            }

            &--small {
                &.product-table__block-solo {
                    .product-table__col {
                        &:nth-child(1) {
                            align-items: center;
                            width: size(32);
                            min-width: size(32);
                        }

                        &:nth-child(2) {
                            justify-content: center;
                            width: size(40);
                            min-width: size(40);
                            height: size(40);
                        }

                        &:nth-child(3) {
                            width: size(340);
                            min-width: size(340);

                            @include table {
                                width: 236px;
                                min-width: 236px;
                            }
                        }

                        &:nth-child(4) {
                            width: 100%;
                            min-width: size(300);
                            max-width: size(464);
                        }

                        &:nth-child(5) {
                            width: size(186);
                            min-width: size(186);
                        }

                        &:nth-child(6) {
                            justify-content: center;
                            width: size(282);
                            min-width: size(282);
                            margin: 0 auto !important;
                        }

                        &:nth-child(7) {
                            width: size(112);
                            min-width: size(112);
                        }

                        &:not(:last-child) {
                            margin-right: size(16);
                        }

                        &:last-child {
                            margin-left: size(16);
                        }
                    }
                }

                &.product-table__block-group {
                    .product-table__col {
                        &:nth-child(1) {
                            align-items: center;
                            width: size(32);
                            min-width: size(32);
                        }

                        &:nth-child(2) {
                            align-items: center;
                            width: size(32);
                            min-width: size(32);
                        }

                        &:nth-child(3) {
                            position: relative;
                            display: grid;
                            align-self: flex-start;
                            width: size(104);
                            min-width: size(104);
                            height: unset;
                            padding: 0;
                            grid-template-columns: repeat(3, 1fr);
                            // grid-gap: size(4) 0;
                        }

                        &:nth-child(4) {
                            width: 160px;
                            min-width: 160px;
                        }

                        &:nth-child(5) {
                            width: 100%;
                            min-width: size(280);
                            max-width: size(464);
                        }

                        &:nth-child(6) {
                            justify-content: center;
                            width: size(186);
                            min-width: size(186);
                        }

                        &:nth-child(7) {
                            justify-content: center;
                            width: size(282);
                            min-width: size(282);
                            margin: 0 auto !important;
                        }

                        &:nth-child(8) {
                            width: size(112);
                            min-width: size(112);
                        }

                        &:not(:last-child) {
                            margin-right: size(16);
                        }

                        &:last-child {
                            margin-left: size(16);
                        }
                    }

                    .product-table__rating {
                        margin-right: 0;
                    }
                }

                .product-table__box {
                    align-items: center;
                    min-height: size(48);
                    padding: 0.5rem;
                }

                .product-table__description {
                    overflow: hidden;
                    display: -webkit-box;
                    -webkit-line-clamp: 2;
                    -webkit-box-orient: vertical;
                }

                .product-table__price {
                    span {
                        font-size: 16px;
                        font-weight: 600;
                    }

                    b {
                        display: flex;
                        font-size: 14px;
                        font-weight: 600;

                        &:after {
                            left: -3%;
                            width: 104%;
                            height: 1px;
                            transform: translate(0, -50%) rotate(10deg);
                        }
                    }
                }

                .product-table__rating {
                    .el-rate__item {
                        &:not(:first-child) {
                            display: none;
                        }

                        &:first-child {
                            margin-right: 2px;
                        }
                    }

                    .el-rate__text {
                        font-size: 12px;
                        color: $color-main-font !important;
                        font-weight: 700;
                    }
                }

                .product-table__product-view,
                .product-table__rating,
                .product-table__messages,
                .product-table__code-product {
                    margin-bottom: 4px;
                }

                .product-table__arrow {
                    border-radius: 4px;
                    background: $color-gray-light;
                }
            }

            .tooltip-btn {
                span {
                    margin-right: 0;
                }

                .el-popover__reference-wrapper {
                    display: flex;
                    font-size: 14px;
                }
            }
        }

        &__block-group {
            .product-table__img {
                width: size(32);
                height: size(32);
            }

            .product-table__price {
                align-items: center;

                .tooltip-btn--margin-left {
                    margin-left: 0;
                }
            }
        }

        &__arrow {
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            width: size(32);
            height: size(32);
            font-size: 16px;
            color: $color-gray-light-100;
            transition: $transition-fast;
            cursor: pointer;
            font-weight: 700;

            &:active {
                color: $color-main-font;
            }

            @include hover {
                &:hover {
                    color: $color-main-font;
                }
            }

            i {
                font-weight: 700;
            }
        }

        &__img-number-all {
            display: flex;
            align-items: center;
            justify-content: center;
            width: size(32);
            height: size(32);
            border-radius: 50%;
            border: 1px solid $color-gray-light;
            font-size: 12px;
            cursor: default;
            font-weight: bold;
        }

        &__box {
            display: flex;
        }

        &__col {
            display: flex;
            flex-direction: column;
        }

        &__row {
            display: flex;
            flex-wrap: wrap;

            &:not(:last-child) {
                margin-bottom: 8px;
            }
        }

        &__img {
            max-height: 100%;
            text-align: center;
            cursor: pointer;

            a {
                display: block;
                width: 100%;
                height: 100%;
            }

            img {
                display: inline-block;
                max-width: 100%;
                max-height: 100%;
                object-fit: contain;
            }
        }

        &__product-view {
            overflow: hidden;
            display: flex;
            align-items: center;
            width: 156px;
            height: 24px;
            margin-right: 8px;
            border-radius: 4px;
            border: 1px solid $color-gray-light;
            cursor: default;

            img {
                width: 16px;
                height: 16px;
                margin: 0 6px 0 0;
                object-fit: contain;
            }

            span {
                width: 50%;
                height: 22px;
                padding: 0 10px;
                background: $color-main-background;
                text-align: center;
                white-space: nowrap;
                font-size: 12px;
                line-height: 23px;
                font-weight: 700;
            }

            p {
                height: 22px;
                padding: 0 6px;
                white-space: nowrap;
                font-size: 12px;
                line-height: 23px;
                color: $color-green-secondary;
                font-weight: 700;

                &.minus-rating {
                    color: $color-red-secondary;
                }
            }
        }

        &__rating {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            height: 100%;
            min-height: 24px;
            margin-right: 8px;
            border-radius: 4px;
            cursor: default;

            .number {
                margin-top: 2px;
                margin-left: 4px;
                font-size: 12px;
                font-weight: 700;
                line-height: 1;
            }

            .el-rate {
                display: flex;
                align-items: center;
                pointer-events: none;

                &__item {
                    display: flex;
                }
            }

            .el-icon-star-off {
                min-width: 16px;
                font-size: 14px;
            }
        }

        &__messages {
            display: flex;
            align-items: center;
            height: 24px;
            margin-right: 8px;
            padding: 4px;
            border-radius: 4px;
            border: 1px solid $color-gray-light;
            cursor: default;

            span {
                margin-left: 4px;
                font-size: 12px;
                font-weight: 700;
            }

            svg {
                width: 16px;
                height: 16px;
                color: $color-purple-primary;
            }
        }

        &__code-product {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: $color-gray-dark;
            transition: $transition-fast;
            cursor: pointer;

            &:active {
                color: $color-purple-primary;
            }

            @include hover {
                &:hover {
                    color: $color-purple-primary;
                }
            }

            svg {
                width: 16px;
                height: 16px;
                margin-left: 4px;
            }

            span {
                border-bottom: 1px solid $color-gray-light-300;
                line-height: 1;
            }
        }

        &__price {
            display: flex;
            align-items: flex-end;
            flex-wrap: wrap;
            cursor: default;

            &:not(:last-child) {
                margin-bottom: 6px;
            }

            span {
                margin-right: 8px;
                font-size: size(28);
                font-weight: 500;
            }

            b {
                position: relative;
                font-weight: 500;
                font-size: size(24);
                color: $color-gray-light-100;

                &:after {
                    content: '';
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    width: 106%;
                    height: 2px;
                    background-color: $color-red-secondary;
                    transform: translate(-50%, -50%) rotate(10deg);
                    pointer-events: none;
                }
            }
        }

        &__group {
            display: flex;

            &--flex-end {
                justify-content: flex-end;
            }
        }

        &__renewal {
            display: flex;
            align-items: flex-end;
            margin: auto 0;
            padding-bottom: size(12);
            font-size: 12px;
            color: $color-gray-dark-800;
            flex-direction: column;

            b {
                font-size: 14px;
                font-weight: 600;
            }
        }
    }

    .product-table-status {
        display: flex;
        align-items: center;
        justify-content: center;
        width: size(32);
        height: size(32);
        border-radius: 8px;
        border: 1px solid $color-gray-light;
        font-size: 16px;
        color: $color-gray-light-100;
        transition: $transition-fast;

        &.border-hover {
            cursor: pointer;
        }

        &__wrapper {
            &:not(:last-child) {
                margin-right: size(8);
            }
        }

        &.active {
            border-color: $color-gray-light-200;
            background-color: $color-gray-light-200;
            color: $color-green-secondary;
        }

        svg {
            width: size(16);
            height: size(16);
        }
    }

    .product-table-improvement {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: size(32);
        height: size(32);
        padding: 0 size(4);
        border-radius: 8px;
        background-color: $color-purple-primary;
        font-size: size(14);
        line-height: 1;
        color: $color-light-background;
        cursor: default;
        font-weight: 700;

        &__wrapper {
            &:not(:last-child) {
                margin-right: size(8);
            }
        }
    }

    .table-head {
        display: flex;
        // .el-button ,
        .custom-search {
            margin-right: 8px;
        }

        @media screen and (max-width: 620px) {
            flex-wrap: wrap-reverse;

            .custom-search {
                max-width: 100%;
                margin: 8px 0 0;
            }
        }
    }

    .tile-option {
        display: flex;
        height: max-content;
        margin-left: auto;
        padding: 4px;
        border-radius: 8px;
        background: $color-gray-extra-light;

        &__btn {
            box-sizing: border-box;
            display: flex;
            justify-content: center;
            width: 32px;
            height: 32px;
            padding: 4px;
            border-radius: 4px;
            border: 1px solid transparent;
            background-color: $color-gray-extra-light;
            color: $color-gray-light;
            transition: $transition-fast;
            cursor: pointer;
            box-shadow: 0 4px 32px rgba(0, 0, 0, 0.06);
            flex-direction: column;

            &.active {
                border-color: $color-gray-blue-light;
                background-color: $color-light-background;
                color: $color-gray-dark;
            }

            &:first-child {
                .tile-option__line {
                    height: 100%;
                }
            }

            @include hover {
                &:hover {
                    color: $color-gray-dark;
                }
            }

            &:active {
                color: $color-gray-dark;
            }
        }

        &__line {
            width: 100%;
            height: 4px;
            border-radius: 2px;
            background-color: currentColor;

            &:not(:last-child) {
                margin-bottom: 2px;
            }
        }
    }

    .table-filter {
        display: flex;
        margin: 8px 0;

        .filter-btn {
            margin-right: 8px;
        }

        &__group {
            display: flex;
            margin-left: auto;
        }
    }

    .dialog-export-product {
        width: 100%;
        max-width: 800px;

        &__wrapper {
            display: flex;
            flex-direction: column;
        }

        &__container {
            display: grid;
            grid-auto-columns: min-content;
        }

        &__box {
            display: flex;
            align-items: center;
            padding: 7px 8px;
            border-radius: 8px;
            border: 1px solid #710bff;
            background-color: #fff;
            cursor: default;
            box-shadow: 0 12px 16px rgba(95, 69, 255, 0.16);

            img {
                width: 32px;
                height: 32px;
                margin-right: 8px;
                border-radius: 50%;
                object-fit: cover;
            }

            span {
                font-size: size(16);
                font-weight: 600;
            }
        }

        &__search {
            display: flex;
            margin-bottom: size(24);
            flex-direction: column;
        }

        &__step-1 {
            .custom-dialog__text {
                margin-bottom: size(16);
                font-weight: 600;
            }
        }

        .custom-add-photo {
            display: flex;
            align-items: center;
            margin-top: 16px;

            .base-txt {
                margin-left: 16px;
                cursor: default;
            }
        }
    }

    .export-product-box {
        position: relative;
        display: flex;
        align-items: center;
        margin-top: 16px;
        padding: 16px 18px;
        border-radius: 8px;
        border: 1px solid #e9edf2;

        &__img {
            width: 74px;
            height: 74px;
            margin-right: 20px;
            object-fit: contain;
        }

        &__group {
            display: flex;
            width: 100%;
            max-width: 408px;
            flex-direction: column;

            .small-txt--purple {
                margin-bottom: 8px;
                padding-right: 14px;
            }
        }

        .close-icon {
            position: absolute;
            top: 0;
            right: 0;
            margin: 8px 8px 0 0;
        }
    }
</style>
