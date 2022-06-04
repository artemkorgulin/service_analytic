<template>
    <div ref="page" v-scroll="handleSetScrollAreaHeight" :class="$style.CampaignsPage">
        <NuxtChild />
        <VFadeTransition mode="out-in" appear>
            <div
                v-if="!isInitialDataFetched && $fetchState.pending"
                key="loading"
                :class="$style.loadingWrapper"
            >
                <VProgressCircular indeterminate size="100" color="accent" />
            </div>
            <BlankCover
                v-else-if="showBlankPage"
                text='Пожалуйста, добавьте performance-api ключ OZON, чтобы начать пользоваться функционалом "Рекламные кампании"'
                image="/images/adm-request.svg"
                has-button
                :button="{ text: 'Подключить рекламу', link: '/settings' }"
                style="flex: auto"
            ></BlankCover>
            <ErrorBanner
                v-else-if="$fetchState.error"
                key="error"
                :status-code="$fetchState.error.statusCode"
                :message="$fetchState.error.message"
            />
            <div v-else-if="!isMobile" key="content" :class="$style.pageWrapper">
                <div :class="$style.pageHeadingWrapper">
                    <h1 :class="$style.pageHeading">{{ pageTitle }}</h1>
                    <div :class="$style.pageHeadingButtonsWrapper">
                        <VBtn
                            color="white"
                            elevation="0"
                            :class="$style.outlinedButton"
                            @click="handleOpenModalRequest"
                        >
                            <SvgIcon name="outlined/speaker" data-left />
                            Оставить заявку
                        </VBtn>
                        <VBtn color="accent" :to="{ name: 'campaign-create' }">
                            <SvgIcon name="outlined/plus" data-left />
                            Создать кампанию
                        </VBtn>
                    </div>
                </div>
                <AdmChartMultiple :campaigns="selectedItems" :class="$style.chartDesktop" />
                <LazyAdmFiltersDesktop
                    v-if="!isMobile"
                    :class="$style.filtersBlockWrapper"
                    :values="filtersValues"
                    :options="filters"
                    @change="handleChangeFiltersDesktop"
                />
                <div
                    v-if="!isMobile"
                    id="main-adm-area"
                    ref="scrollableTableWrapper"
                    :class="$style.mainArea"
                >
                    <LazyAdmTable
                        ref="scrollableTable"
                        v-model="selectedItems"
                        :headings="$options.pageValuesDictionary"
                        :items="items"
                        :counters="counters"
                        :loading="loading"
                        :blocking-loading="$fetchState.pending"
                        :is-end="isEnd"
                        @end="handleReachEnd"
                        @clicked="handleItemClick"
                        @onContext="handleContextOpen"
                    />
                </div>
            </div>
            <div v-else key="contentMobile" :class="$style.pageWrapperMobile">
                <Portal to="header">
                    <div :class="$style.mobilePortalWrapper">
                        <h1 :class="$style.pageHeading">{{ pageTitle }}</h1>
                    </div>
                </Portal>
                <VSheet color="#710BFF" :class="$style.ctaWrapper">
                    <h2 :class="$style.ctaHeader">
                        Ведение
                        <br />
                        рекламной кампании
                    </h2>
                    <VBtn
                        outlined
                        :class="$style.ctaBtn"
                        small
                        dark
                        @click="handleOpenModalRequest"
                    >
                        Оставить заявку
                    </VBtn>
                </VSheet>
                <AdmChartMultiple :class="$style.chartMobile" :campaigns="selectedItems" mobile />
                <div :class="$style.selectedBlock">
                    <div :class="$style.selectedTitle">
                        <template v-if="selectedItems.length">
                            {{ selectedItems.length }}&nbsp;{{
                                $options.filters.plural(selectedItems.length, [
                                    'кампания',
                                    'кампании',
                                    'кампаний',
                                ])
                            }}
                        </template>
                        <template v-else>Все кампании</template>
                    </div>
                    <div :class="$style.selectedSubtitle">
                        Выберите кампании для отображения статистики
                    </div>
                </div>
                <AdmChartMultiple :campaigns="selectedItems" :class="$style.chartDesktop" />
                <LazyAdmListMobile
                    v-model="selectedItems"
                    :items="items"
                    :blocking-loading="$fetchState.pending"
                    is-enable-infinity
                    :is-end="isEnd"
                    @end="handleReachEnd"
                    @onContext="handleContextOpen"
                />
                <!-- @click="handleItemClick" -->
            </div>
        </VFadeTransition>
    </div>
</template>
<router>
{
name: 'adm-campaigns',
path: '/:marketplace/adm',
meta:{
pageGroup: "perfomance"
}
}
</router>

<script>
    /* eslint-disable no-unused-expressions,camelcase,no-empty-function, no-extra-parens */
    import { mapActions, mapState, mapGetters } from 'vuex';
    import { isEqual, pick, debounce } from 'lodash';
    import { isSet } from '~utils/helpers';
    import PageErrors from '~mixins/pageErrors.js';
    const AVALIABLE_FILTERS = [
        'campaign_page_types',
        'campaign_payment_types',
        'campaign_placements',
        'campaign_statuses',
        'campaign_types',
        'strategy_statuses',
        'strategy_types',
    ];
    const initialFilters = {
        campaign_page_types: undefined,
        campaign_payment_types: undefined,
        campaign_placements: undefined,
        campaign_statuses: undefined,
        campaign_types: undefined,
        strategy_statuses: undefined,
        strategy_types: undefined,
    };
    const TABLE_FIELDS_ARRAY = [
        {
            key: 'name',
            text: 'Название рекламной кампании',
        },
        {
            key: 'status_name',
            text: 'Статус',
        },
        {
            key: 'popularity',
            text: 'Популярность',
        },
        {
            key: 'Дневное ограничение',
            text: 'Популярность',
        },
        {
            key: 'shows',
            text: 'Показы',
        },
        {
            key: 'purchased_shows',
            text: 'Выкупленных показов, %',
        },
        {
            key: 'avg_1000_shows_price',
            text: 'СРМ, ₽',
            tooltip: 'Цена / 1000 показов',
        },
        {
            key: 'clicks',
            text: 'Клики',
        },
        {
            key: 'ctr',
            text: 'CTR, %',
            tooltip: 'Клики / Показы',
        },
        {
            key: 'orders',
            text: 'Заказы',
        },
        {
            key: 'profit',
            text: 'Выручка, ₽',
        },
        {
            key: 'cost',
            text: 'Расход, ₽',
        },
        {
            key: 'drr',
            text: 'ДРР, %',
        },
        {
            key: 'cpo',
            text: 'СРО, ₽',
            tooltip: 'Цена / Заказ',
        },
        {
            key: 'avg_click_price',
            text: 'СРС, ₽',
            tooltip: 'Цена / Клик',
        },
        /* нету в макете */
        {
            key: 'placement_name',
            text: 'Тип рекламных кампаний',
        },
        {
            key: 'payment_type_name',
            text: 'Тип оплаты',
        },
        {
            key: 'strategy_status_name',
            text: 'Статус стратегии',
        },
        {
            key: 'strategy_name',
            text: 'Тип стратегии',
        },
    ];
    export default {
        name: 'CampaignsPage',
        mixins: [PageErrors],

        transition: {
            name: 'fade',
            mode: 'out-in',
        },
        pageValuesDictionary: TABLE_FIELDS_ARRAY,
        data() {
            return {
                selectedItems: [],
                isMounted: false,
                isInitialDataFetched: false,
                pageTitle: 'Рекламные кaмпании',
                resizeObserver: null,
                width: null,
                search: '',
                contextMenuItemCached: {},
                notification: () => {},
            };
        },
        async fetch() {
            try {
                this.isInitialDataFetched = true;
                await Promise.all([
                    this.fetchCampaignsData(this.filtersToRequest),
                    this.fetchFilters(),
                ]);
            } catch (error) {
                await this?.$sentry?.captureException(error);
                if (process.server) {
                    this.$nuxt.context.res.statusCode = 404;
                }
                throw new Error(this.$_getResponseErrorMessage(error));
            }
        },
        head() {
            return {
                title: this.pageTitle,
            };
        },
        computed: {
            ...mapState('adm', {
                counters: state => state.campaigns.data.counters,
                pageData: state => state.campaigns.data.pageData,
                filters: state => state.filters.data,
                loading: state => state.campaigns.isLoading,
                isFiltersFetched: state => state.filters.isInitialDataFetched,
            }),
            ...mapState('auth', {
                user: 'user',
            }),
            ...mapGetters('adm', {
                isEnd: 'isCampaignsReachEnd',
                items: 'getCampaigns',
            }),
            ...mapGetters({
                selectedMarketplaceSlug: 'getSelectedMarketplaceSlug',
            }),

            contextMenuItems() {
                return [
                    {
                        title: 'Товары',
                        value: 'goods',
                        icon: 'outlined/cart',
                        route: {
                            name: 'campaign-goods',
                            params: {
                                id: this.contextMenuItemCached.id,
                                ...this.$route.params,
                            },
                        },
                    },
                    {
                        title: 'Ключевые слова и ставки',
                        value: 'keywords',
                        icon: 'outlined/card',
                        route: {
                            name: 'campaign-keywords',
                            params: {
                                id: this.contextMenuItemCached.id,
                                ...this.$route.params,
                            },
                        },
                    },
                    {
                        title: 'Статистика',
                        value: 'open',
                        icon: 'outlined/presentation',
                        route: {
                            name: 'campaign-statistic',
                            params: {
                                id: this.contextMenuItemCached.id,
                                ...this.$route.params,
                            },
                        },
                    },
                    {
                        title: 'Настройки',
                        value: 'settings',
                        icon: 'outlined/settings',
                        route: {
                            name: 'campaign-settings',
                            params: {
                                id: this.contextMenuItemCached.id,
                                ...this.$route.params,
                            },
                        },
                    },
                ];
            },
            filtersToRequest() {
                const dict = {
                    campaign_page_types: 'campaign_type_ids',
                    campaign_payment_types: 'campaign_payment_type_id',
                    campaign_placements: 'campaign_placement_id',
                    campaign_statuses: 'campaign_status_ids',
                    campaign_types: 'campaign_type_ids',
                    strategy_statuses: 'campaign_strategy_statuse_id',
                    strategy_types: 'campaign_strategy_type_id',
                };
                return Object.entries(this.filtersValues).reduce((acc, val) => {
                    if (isSet(val[1] && dict[val[0]])) {
                        acc[dict[val[0]]] = val[1];
                    }
                    return acc;
                }, {});
            },
            filtersValues: {
                get() {
                    const picked = pick(this.$route.query, AVALIABLE_FILTERS);

                    return Object.entries(picked).reduce((acc, val) => {
                        if (!val[1]) {
                            return acc;
                        } else if (Array.isArray(val[1])) {
                            acc[val[0]] = val[1];
                        } else {
                            acc[val[0]] = [val[1]];
                        }
                        return acc;
                    }, {});
                },
                set(values) {
                    // console.log('🚀 ~ file: campaigns.vue ~ line 313 ~ set ~ values', values);
                    const picked = pick(values, AVALIABLE_FILTERS);
                    const reduced = Object.entries(picked).reduce((acc, val) => {
                        if (!val[1]) {
                            acc[val[0]] = undefined;
                        } else {
                            acc[val[0]] = val[1];
                        }
                        return acc;
                    }, {});
                    this.$router.push({
                        name: this.$route.name,
                        query: { ...this.$route.query, ...reduced },
                    });
                },
            },
            isMobile() {
                if (!this.width) {
                    return !this.$nuxt.$device.isDesktop;
                }
                return this.width <= 1024;
            },
            countersComponent() {
                return this.isMobile ? 'AdmCountersMobile' : 'AdmCountersDesktop';
            },
            showBlankPage() {
                const activeAccount = Object.values(this.user.accounts).find(
                    el => el.pivot.is_selected === 1
                );
                return (
                    this.selectedMarketplaceSlug === 'ozon' &&
                    (!activeAccount.platform_api_key?.length ||
                        !activeAccount.platform_client_id?.length)
                );
            },
        },
        watch: {
            filtersValues(val, oldVal) {
                if (isEqual(val, oldVal)) {
                    return;
                }
                return this.fetchDelayed();
            },
            search(val, oldVal) {
                if (val === oldVal) {
                    return;
                }
                if (!val) {
                    return this.searchEmpty();
                }
                this.filtersValues = initialFilters;
                return this.searchDelayed();
            },
        },
        mounted() {
            this.isMounted = true;
            this.resizeObserver = new ResizeObserver(this.handleSetScrollAreaHeight);
            if (this?.$refs?.page) {
                return this.resizeObserver.observe(this.$refs.page);
            } else {
                console.warn('NO REF PAGE');
            }
            // this?.$refs?.page && this.resizeObserver.observe(this.$refs.page);
        },
        beforeDestroy() {
            this.resizeObserver &&
                this?.$refs?.page &&
                this.resizeObserver.unobserve(this.$refs.page);
            return this.clearUrl();
        },
        methods: {
            ...mapActions('adm', {
                fetchCampaignsData: 'fetchCampaignsData',
                fetchCampaignsDataMore: 'fetchCampaignsDataMore',
                fetchFilters: 'fetchFilters',
                searchCampaings: 'searchCampaings',
            }),
            searchEmpty() {
                this.clearUrl();
                return this.$fetch();
            },
            clearUrl() {
                this.$router.push({
                    name: this.$route.name,
                    params: this.$route.params,
                    query: { ...this.$route.query, search: undefined },
                });
                return this.$fetch();
            },
            searchDelayed: debounce(function () {
                return this.searchAdm();
            }, 750),

            async searchAdm() {
                return Promise.all([
                    this.$router.push({
                        name: this.$route.name,
                        params: this.$route.params,
                        query: { ...this.$route.query, search: this.search },
                    }),
                    this.searchCampaings(this.search),
                ]);
            },
            async handleContextOpen(options, item) {
                this.contextMenuItemCached = item;
                await this.$contextMenu.open({
                    items: this.contextMenuItems,
                    heading: item.campaign_name,
                    options,
                    item,
                });
            },
            fetchDelayed: debounce(function () {
                this.$fetch();
            }, 750),
            handleItemClick(item) {
                return this.$router.push({
                    name: 'campaign-goods',
                    params: {
                        id: item.id,
                        marketplace: this.$route.params.marketplace,
                    },
                });
            },
            handleOpenModalRequest() {
                return this.$modal.open({
                    component: 'ModalAdmRequest',
                });
            },
            handleSetScrollAreaHeight() {
                this.width = document.body.clientWidth;
                this.$nextTick(() => {
                    if (this.$refs?.scrollableTable) {
                        return this.$refs.scrollableTable.setScrollAreaHeight();
                    } else {
                        console.warn('NO refs scrollableTable', this.$refs);
                    }
                });
            },
            handleSortChange(val) {
                // TODO make sort
                console.log('🚀 ~ file: brand.vue ~ line 180 ~ handleSortChange ~ val', val);
            },
            handleChangeFiltersDesktop({ field, item }) {
                this.filtersValues = { [field]: item };
            },
            handleChangeFilters(fieldName, value) {
                this.filtersValues = { [fieldName]: value };
            },
            async handleReachEnd($state) {
                try {
                    if (this.loading || this.isEnd) {
                        return false;
                    }
                    await this.fetchCampaignsDataMore(this.filtersValues);
                    if (this.isEnd) {
                        return $state.complete();
                    } else {
                        return $state.loaded();
                    }
                } catch (error) {
                    await this?.$sentry?.captureException(error);
                    console.log(
                        '🚀 ~ file: campaigns.vue ~ line 479 ~ handleReachEnd ~ error',
                        error
                    );

                    await $state.complete();
                    this.notification = await this.$notify.create({
                        message: 'Ошибка при загрузке данных',
                        timeout: 5000,
                        type: 'negative',
                    });
                }
            },
        },
    };
</script>
<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    $table-min-height: 600px;
    $filters-height: 80px;

    :global(.v-application) {
        .outlinedButton {
            border: 1px solid $base-800;
            border-color: $base-800 !important;
        }
    }

    .CampaignsPage {
        position: relative;
        display: flex;
        flex: 1;
        min-height: calc(100vh - var(--header-h));
        padding: 24px 32px;
        padding-top: 2.2rem;
        background-color: $base-100;
        flex-direction: column;

        @include respond-to(md) {
            padding: 0;
        }
    }

    .mobilePortalWrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        padding-left: 16px;

        @include sm {
            padding-left: 8px;
        }
    }

    .loadingWrapper {
        @include centerer;
    }

    .pageWrapper {
        display: flex;
        width: 100%;
        height: 100%;
        flex-direction: column;

        @include respond-to(md) {
            display: none;
        }
    }

    .pageWrapperMobile {
        overflow-x: hidden;
        width: 100%;
        height: 100%;
        flex-direction: column;
    }

    .pageHeadingWrapper {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 2.2rem;
        gap: 16px;
        user-select: none;

        @include respond-to(md) {
            flex-direction: column;
            align-items: flex-start;
            margin-bottom: 0;
            padding: 16px;
        }

        @include respond-to(sm) {
            padding: 16px;
        }
    }

    .pageHeading {
        @extend %text-h1;

        white-space: nowrap;

        @include md {
            font-size: 18px;
        }
    }

    .numbersBlockWrapper {
        margin-bottom: $page-gap;
    }

    .filtersBlockWrapper {
        margin-bottom: $page-gap;
    }

    .mainArea {
        $height: calc(var(--app-height, 100vh) - var(--header-h) - 32px - 72px);

        position: relative;
        flex: 1;
        height: $height;
        min-height: $height;
        max-height: $height;
        padding: 16px;
        border-radius: 24px;
        background-color: $white;
        box-shadow: 0 4px 32px rgba(0, 0, 0, 0.06);
    }

    .pageHeadingButtonsWrapper {
        display: flex;
        gap: 1.6rem;

        @include respond-to(md) {
            display: none;
        }
    }

    .controlsWrapperMobile {
        position: relative;
        display: flex;
        padding: 16px;
        padding-right: 4px;
        background-color: $white;
        gap: 10px;

        @include borderLine;
    }

    .ctaWrapper {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
        padding: 8px 16px;
    }

    .ctaHeader {
        font-size: 12px;
        line-height: 16px;
        color: $white;
        font-weight: bold;
    }

    .ctaBtn {
        height: 32px !important;
        font-size: 14px !important;
    }

    .chartDesktop {
        margin-bottom: 16px;
        padding: 16px;
        border-radius: 24px;
        background: $white;
        box-shadow: 0 4px 32px rgba(0, 0, 0, 0.06);
    }

    .chartMobile {
        margin-bottom: 8px;
        // padding: 12px 16px;
        // background-color: $white;
    }

    .selectedBlock {
        min-height: 56px;
        margin-bottom: 8px;
        padding: 9px 16px;
        background-color: $white;
    }

    .selectedTitle {
        font-weight: 500;
        font-size: 16px;
        line-height: 22px;
        color: $base-900;
    }

    .selectedSubtitle {
        font-size: 12px;
        line-height: 16px;
        color: $base-900;
    }
</style>
