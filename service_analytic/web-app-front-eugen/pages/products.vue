<template>
    <div v-if="$fetchState.pending" class="page-loading">
        <v-progress-circular indeterminate size="120" color="primary"></v-progress-circular>
    </div>
    <div v-else :class="$style.ProductsPage">
        <Page
            :title="title"
            :is-display-main-tpl="userActiveAccounts.length > 0"
            :select="select"
            :select-reset-account="setDefault"
            :btn-characteristics="btnCharacteristics"
            :btn-characteristics-click="btnCharacteristicsClick"
            :btn-request="btnRequest"
            :btn-request-click="handleOpenModalRequest"
            :btn-add="btnAdd"
            :btn-add-click="handleAddProduct"
        >
            <SeAlert v-if="isSelectedMp.id === 2" class="mb-6">
                Сейчас наблюдаются сбои в обмене информацией с маркетплейсами. Если вы столкнулись с
                ошибками при загрузке или отправке товаров, напишите в поддержку на
                <a href="mailto:team@sellerexpert.ru">team@sellerexpert.ru</a>
            </SeAlert>
            <div
                v-if="
                    (noAddedAcc || !products.items || !products.items.length) && !isSearchAndFilter
                "
                class="se-card d-flex align-center justify-center"
                style="height: 80vh"
            >
                <NoAddedAccPage :no-added-acc="noAddedAcc" style="flex: auto"></NoAddedAccPage>
            </div>
            <template v-else-if="firstLoading">
                <DashboardChart />
                <IndexSection :items="products.items" />
                <div :class="$style.paginationNav">
                    <VPagination
                        v-if="getPaginationLength > 1"
                        v-model="pagination"
                        :class="$style.pagination"
                        :disabled="products.pending.show"
                        :length="getPaginationLength"
                        total-visible="5"
                        circle
                    />
                    <div class="per-page d-flex align-center" style="width: 250px">
                        <div class="per-page__text mr-3">Карточек на странице</div>
                        <VSelect
                            v-model="perPageM"
                            :items="itemsPerPage"
                            class="per-page__select light-outline"
                            outlined
                            dense
                            hide-details
                        />
                    </div>
                </div>
            </template>
        </Page>
    </div>
</template>
<script>
    /* eslint-disable */
    import { mapGetters, mapState, mapActions } from 'vuex';
    import LazyHydrate from 'vue-lazy-hydration';
    import DashboardChart from '~/components/pages/products/DashboardChart';

    import { isSet } from '~utils/helpers';
    import IndexSection from '~/components/pages/products/Products';
    import Page from '~/components/ui/SeInnerPage';

    export default {
        name: 'ProductsPage',
        components: {
            IndexSection,
            LazyHydrate,
            DashboardChart,
            Page,
        },

        data() {
            return {
                title: {
                    isActive: true,
                    text: 'Мои товары',
                },
                firstLoading: false,
                initialDataFetched: false,
                pendingLocal: false,
                itemsPerPage: [10, 15, 20, 25, 30, 40, 50],
                productsPerPage: 10,
            };
        },
        async fetch() {
            try {
                if (this.accounts.length) {
                    await this.loadProducts({
                        type: 'common',
                        reload: true,
                        marketplace: this.isSelectedMp.key,
                    });
                }
                this.initialDataFetched = true;
            } catch (error) {
                if (typeof error !== 'string' && error !== 'no added accounts') {
                    const errorMessage =
                        error?.response?.statusText ||
                        error?.message ||
                        'Произошла ошибка при получении данных';
                    await this?.$sentry?.captureException(error);
                    throw new Error(errorMessage);
                }
            } finally {
                this.firstLoading = true;
            }
        },
        head() {
            return {
                title: this.title.text,
                htmlAttrs: {
                    class: 'static-rem',
                },
            };
        },
        computed: {
            ...mapState('products', ['perPage', 'pageLoading']),
            ...mapGetters(['isSelectedMp', 'userActiveAccounts']),
            ...mapGetters({
                accounts: 'getAccounts',
                products: 'products/GET_PRODUCTS',
                isSearchAndFilter: 'products/IS_SEARCH_AND_FILTER',
                marketplaceSlug: 'getSelectedMarketplaceSlug',
            }),
            perPageM: {
                get() {
                    return this.perPage;
                },
                set(value) {
                    this.$store.commit('products/setField', { field: 'perPage', value });
                },
            },
            noAddedAcc() {
                return !this.accounts.length;
            },
            showStub() {
                return (
                    !this.products.total && !this.products.pending.show && !this.isSearchAndFilter
                );
            },
            isPageLoading() {
                return this.products.pending.show && this.products.pending.type === 'common';
            },
            pagination: {
                get() {
                    return this.products.page;
                },
                async set(val) {
                    await this.$store.commit('products/CHANGE_PAGE', val);
                    return this.loadProducts({
                        type: 'common',
                        reload: false,
                        marketplace: this.marketplaceSlug,
                    });
                },
            },
            getPaginationLength() {
                return Math.ceil(this.products.total / this.perPage);
            },
            select() {
                return {
                    isActive: !this.noAddedAcc,
                };
            },
            btnCharacteristics() {
                return {
                    isActive: !this.noAddedAcc,
                    isImg: true,
                    text: 'Копирование характеристик',
                };
            },
            btnRequest() {
                return {
                    isActive: true,
                    isImg: true,
                    text: 'Оставить заявку',
                };
            },
            btnAdd() {
                return {
                    isActive: !this.noAddedAcc && this.products.items.length,
                    isImg: true,
                    text: 'Добавить товар',
                    class: 'ob-add-product-01',
                };
            },
            isShowAddBtn() {
                return this.products?.items?.length;
            },
        },
        watch: {
            perPage() {
                const { key } = this.isSelectedMp;
                this.loadProducts({
                    type: 'common',
                    reload: true,
                    marketplace: key,
                });
            },
            async isPageLoading(val, oldVal) {
                if (!this.initialDataFetched) {
                    return;
                }
                if (val && isSet(oldVal)) {
                    this.pendingLocal = true;
                } else if (!val) {
                    this.pendingLocal = false;
                    return this.$nuxt.$vuetify.goTo(0);
                }
            },
            needToOpenModalProductsAdd: {
                immediate: true,
                handler(val) {
                    if (val) {
                        setTimeout(() => this.handleAddProduct(), 800);
                    }
                },
            },
        },
        beforeDestroy() {
            this.$store.commit('products/setField', {
                field: 'nameSortField',
                value: '',
            });
        },
        methods: {
            ...mapActions({
                loadProducts: 'products/LOAD_PRODUCTS',
            }),
            setDefault() {
                this.firstLoading = false;
                setTimeout(() => {
                    this.firstLoading = true;
                }, 300);
            },
            async setPerPage(val) {
                await this.$store.commit('products/CHANGE_PAGE', 1);
                await this.$store.commit('products/CHANGE_PER_PAGE', val);

                return this.loadProducts({
                    type: 'common',
                    reload: false,
                    marketplace: this.marketplaceSlug,
                });
            },
            handleAddProduct() {
                return this.$modal.open({
                    component: 'AddProductsFromMarketplace',
                });
            },
            handleRefetchProducts() {
                return this.$fetch();
            },
            handleOpenModalRequest() {
                return this.$modal.open({
                    component: 'ModalProductsRequest',
                });
            },
            btnCharacteristicsClick() {
                this.$router.push({ name: 'bulk-edit-copy' });
            },
        },
    };
</script>
<style lang="scss" scoped>
    .page-loading {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
    }

    @media screen and (min-width: 768px) {
        .per-page {
            position: absolute;
            top: 0;
            right: 0;

            &__text {
                font-size: 14px;
                color: $color-gray-dark;
            }

            &__select {
                width: 75px;
            }
        }
    }

    @media screen and (max-width: 768px) {
        .per-page {
            display: none !important;
        }
    }
</style>
<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .ProductsPage {
        position: relative;
        display: flex;
        flex-direction: column;
        min-height: 100%;

        @include respond-to(sm) {
            padding: 1.5rem 8px;
        }
    }

    .paginationNav {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 24px;
    }

    .perPage {
        display: flex;
        align-items: center;

        :global(.v-input__slot) {
            padding-right: 0 !important;
        }

        :global(.v-select) {
            width: 72px;
        }
    }

    .perPageTxt {
        display: block;
        margin-left: 16px;
        font-size: 14px;
        font-weight: 500;
    }

    .loadingWrapper {
        @include centerer;
    }

    .headingWrapper {
        display: flex;
        justify-content: space-between;
        width: 100%;
        margin-bottom: 24px;
        gap: 16px;
        flex-direction: column;
    }

    .headingInner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;

        .heading {
            font-size: 32px;
            user-select: none;

            @extend %text-h1;
        }

        .btnsWrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;

            @include respond-to(md) {
                justify-content: space-between;
                width: 100%;
            }
        }
    }

    .headingControls {
        display: flex;
        align-items: center;
        gap: 16px;

        .headingControlsTitle {
            display: block;
            margin-bottom: 1px;
            text-align: right;
            font-size: 14px;
            font-weight: 700;

            @include respond-to(lg) {
                text-align: left;
            }
        }

        .headingControlsSubtitle {
            display: block;
            text-align: right;
            font-size: 14px;

            @include respond-to(lg) {
                text-align: left;
            }
        }

        @include respond-to(lg) {
            justify-content: space-between;
        }
    }

    .notContent {
        position: absolute;
        top: 50%;
        left: 50%;
        max-width: 350px;
        padding: 22px 24px;
        border-radius: 16px;
        border: 1px dashed #7e8793;
        text-align: center;
        font-size: 1rem;
        line-height: 1.5;
        -webkit-transform: translate(-50%);
        transform: translate(-50%);
        font-weight: 600;
        transition: background-color 300ms ease-in-out;
        cursor: pointer;

        &:hover {
            background-color: $base-400;
        }
    }
</style>
