<template>
    <VFadeTransition :class="$style.CampaignEditPage" mode="out-in" group appear tag="div">
        <div v-if="$fetchState.pending" key="loading" :class="$style.loadingWrapper">
            <VProgressCircular indeterminate size="100" color="accent" />
        </div>
        <ErrorBanner
            v-else-if="$fetchState.error"
            key="err"
            :message="$fetchState.error.message || 'Произошла ошибка'"
        />
        <template v-else>
            <div key="header" :class="$style.headerWrapper">
                <GoBackBtn
                    :class="$style.goBack"
                    :route="{ name: 'adm-campaigns' }"
                    text="Рекламные кампании"
                />
                <h1 :class="$style.pageHeading">
                    {{ pageTitle }}
                </h1>
            </div>
            <div
                key="content"
                :class="[$style.mainWrapper, growHeightOnMobile && $style.growHeightOnMobile]"
            >
                <div :class="$style.tabs">
                    <NuxtLink
                        v-for="tab in tabs"
                        :key="`tab-${tab.name}`"
                        :class="$style.tab"
                        :disabled="tab.disabled"
                        :to="tab.to"
                    >
                        {{ tab.text }}
                    </NuxtLink>
                    <NuxtLink
                        :to="{
                            name: 'campaign-settings',
                            params: $route.params,
                        }"
                        :class="$style.settingsButtons"
                    >
                        <SvgIcon name="outlined/settings" />
                    </NuxtLink>
                </div>

                <!-- <VTabs :class="$style.tabs" show-arrows>
                    <VTab
                        v-for="tab in tabs"
                        :key="`tab-${tab.name}`"
                        :disabled="tab.disabled"
                        :to="tab.to"
                    >
                        {{ tab.text }}
                    </VTab>
                </VTabs> -->
                <VDivider :class="$style.divider" />
                <NuxtChild />
            </div>
        </template>
    </VFadeTransition>
</template>
<router>
{
  name: 'adm-campaigns-edit-id',
  path: '/:marketplace/campaign/:id',
  meta:{
    pageGroup: "perfomance",
    redirectOnChangeMarketplace: true,
    isEnableGoBackOnMobile:true,
    fallbackRoute: {
      name: "adm-campaigns"
    },
  }
}
</router>
<script>
    import { mapGetters, mapActions } from 'vuex';
    export default {
        name: 'CampaignEditPage',
        middleware: [
            ({ route, redirect }) => {
                if (route.name === 'adm-campaigns-edit-id') {
                    redirect({ name: 'campaign-settings', params: route.params });
                }
            },
        ],
        data() {
            return {
                currentStep: 1,
                headers: [
                    { text: 'Артикул', value: 'id', width: '10rem' },
                    { text: 'Название товара', value: 'name' },
                    { text: 'Категория', value: 'group_name', width: '14rem' },
                    {
                        text: 'Дата добавления',
                        value: 'created_at',
                        width: '15rem',
                        valueTransfromFunction: val =>
                            this.$options.filters.formatDateTime(val, '$d.$m.$y - $G:$I'),
                    },
                    {
                        text: 'Ключевые слова',
                        value: 'keywords_count',
                        width: '10rem',
                    },
                    {
                        text: 'Минус слова',
                        value: 'stop_words_count',
                        width: '10rem',
                    },
                ],
                selected: [],
            };
        },
        async fetch() {
            try {
                const id = this.$route.params.id;
                await Promise.all([this.fetchDataAndGoods(id), this.fetchData(id)]);
            } catch (error) {
                await this?.$sentry?.captureException(error);
                if (process.server) {
                    this.$nuxt.context.res.statusCode = 404;
                }
                throw new Error(error);
            }
        },
        head() {
            return {
                title: this.pageTitle,
            };
        },
        computed: {
            ...mapGetters({
                isSubscribed: 'getIsPayedSubscription',
            }),
            ...mapGetters('campaign', {
                campaign: 'getCampaignData',
                products: 'getCampaignGoods',
                isNoGoods: 'getIsCampaignGoodsEmpty',
            }),
            pageTitle() {
                return this.campaign?.name;
            },
            growHeightOnMobile() {
                return Boolean(this.$route?.meta?.growHeightOnMobile);
            },
            tabs() {
                return [
                    {
                        name: 'goods',
                        text: 'Товары',
                        to: {
                            name: 'campaign-goods',
                            params: this.$route.params,
                        },
                    },
                    {
                        name: 'keywords',
                        text: 'Ключевые слова и ставки',
                        disabled: this.isNoGoods,
                        to: {
                            name: 'campaign-keywords',
                            params: this.$route.params,
                        },
                    },
                    // {
                    //     name: 'statistic',
                    //     text: 'Статистика',
                    //     to: {
                    //         name: 'campaign-statistic',
                    //         params: this.$route.params,
                    //     },
                    // },
                    // {
                    //     name: 'settings',
                    //     text: 'Настройки',
                    //     to: {
                    //         name: 'campaign-settings',
                    //         params: this.$route.params,
                    //     },
                    // },
                ];
            },
        },
        beforeDestroy() {
            return this.unsetData();
        },
        methods: {
            ...mapActions('campaign', {
                fetchDataAndGoods: 'fetchCampaignDataAndGoods',
                fetchData: 'fetchCampaignData',
                unsetData: 'unsetData',
            }),
        },
    };
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .CampaignEditPage {
        overflow: hidden;
        display: flex;
        flex: 1;

        // @include respond-to(md) {
        height: auto;
        min-height: 100%;
        max-height: calc(var(--app-height, 100vh) - var(--header-h));
        // height: calc(100vh - var(--header-h));
        padding-right: 24px;
        padding-bottom: 0;
        padding-left: 24px;
        background-color: $base-100;
        flex-direction: column;
        // }
        @include respond-to(md) {
            max-height: unset;
        }
    }

    .chartWrapper {
        margin-bottom: 16px;
        padding: 16px;
        border-radius: 24px;
        background: $white;
        box-shadow: 0 4px 32px rgba(0, 0, 0, 0.06);
    }

    .chartHeading {
        @extend %text-h5;
    }

    .loadingWrapper {
        @include centerer;
    }

    // .goBack {
    //     @include md {
    //         display: none;
    //     }
    // }

    .headerWrapper {
        display: flex;
        align-items: center;
        padding-top: 16px;
        padding-bottom: 16px;
        gap: 12px;
    }

    .pageHeading {
        @extend %text-h1;
        @extend %ellipsis;

        font-size: 24px;
        color: $base-900;
        user-select: none;
        font-weight: normal;

        @include md {
            font-size: 22px;
        }
    }

    .mainWrapper {
        overflow: hidden;
        display: flex;
        flex: 1;
        margin-bottom: 16px;
        padding: $page-gap;
        flex-direction: column;

        @extend %sheet;

        &.growHeightOnMobile {
            @include respond-to(md) {
                max-height: unset;
            }
        }
    }

    .divider {
        margin-bottom: 16px;
    }

    .tabs {
        display: flex;
        align-items: center;
        min-height: 40px;
        max-height: 48px;
        margin-bottom: 16px;
        gap: 24px;
    }

    .tab {
        font-weight: 500;
        font-size: 24px;
        line-height: 33px;
        color: rgba($base-900, 0.4);
        transition: $primary-transition;

        &:global(.nuxt-link-active) {
            color: $base-900;
        }
    }

    .settingsButtons {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        margin-left: auto;
        border-radius: 8px;
        border: 1px solid $base-600;
        transition: $primary-transition;

        &:global(.nuxt-link-active) {
            border: none;
            background-color: $base-900;
            color: $base-900;
            color: $white;
        }
    }
</style>
