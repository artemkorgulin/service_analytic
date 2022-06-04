<template>
    <div class="dashboard-card" :class="$style.DashboardAdvertising">
        <h3 v-show="isTariffPaid" class="dashboard-card__header" :class="$style.header">Реклама</h3>
        <div v-if="!data" :class="$style.errorWrapper">
            <ErrorBanner key="error" message="Произошла ошибка" img-size="100" small />
        </div>
        <div v-else-if="!data.total_statistic" :class="$style.errorWrapper">
            <div :class="$style.nodataText">
                Подключите Ozon Performance, чтобы получить доступ к рекламным кампаниям
            </div>
            <VBtn color="accent">Подключить</VBtn>
        </div>
        <div v-else :class="$style.content" class="custom-scrollbar">
            <div :class="$style.grid">
                <DashboardValueAndTrend
                    v-for="(item, index) in items"
                    :key="index"
                    :title="item.title"
                    :value="item.value"
                    :trend-value="item.trendValue"
                    :trending-up="item.trendUp"
                    :trending-down="item.trendDown"
                />
            </div>
            <div :class="[$style.cover, $style.coverComingSoon]">
                <div :class="$style.coverComingSoonText">СКОРО</div>
            </div>
            <div v-show="!isTariffPaid" :class="$style.cover">
                <VImg
                    :class="$style.coverImage"
                    src="/images/adm-request.svg"
                    contain
                    alt="Только на Платном тарифе"
                />
                <div :class="$style.coverInfo">
                    <div :class="$style.coverHeader">Реклама</div>
                    <div :class="$style.coverText">
                        Описать, чем реклама полезна для продавца и что она доступна только на
                        Платном тарифе
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    const dictionary = {
        popularity: 'Популярность',
        shows: 'Показы',
        purchased_shows: 'Вык. показов, %',
        clicks: 'Клики',
        ctr: 'CTR, %',
        avg_1000_shows_price: 'СРМ, ₽',
        avg_click_price: 'СРС, ₽',
        cost: 'Расход, ₽',
        orders: 'Заказы',
        profit: 'Выручка, ₽',
        drr: 'ДРР, %',
        cpo: 'СРО, ₽',
    };

    import { mapGetters } from 'vuex';
    export default {
        name: 'DashboardAdvertising',
        props: {
            data: {
                type: Object,
                default: () => ({}),
            },
        },
        dictionary,
        computed: {
            ...mapGetters({
                isTariffPaid: 'user/isTariffPaid',
            }),
            items() {
                if (!this?.data?.total_statistic) {
                    return [];
                }
                return Object.entries(this.data.total_statistic).reduce((acc, [key, val]) => {
                    const finded = this.$options.dictionary[key];
                    if (!finded) {
                        return acc;
                    }
                    acc.push({
                        title: finded,
                        value: val,
                    });
                    return acc;
                }, []);
            },
        },
    };
</script>

<style lang="scss" module>
    .DashboardAdvertising {
        display: flex;
        padding: 1rem;
        grid-column: span 2;
        flex-direction: column;
    }

    .nodataText {
        margin-bottom: 24px;
        font-size: 14px;
        line-height: 19px;
        color: $base-700;
    }

    .errorWrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 1;
        flex-direction: column;
    }

    .header {
        @include respond-to(md) {
            display: none;
        }
    }

    .content {
        position: relative;
        height: 100%;

        @include respond-to(md) {
            overflow-x: auto;
        }
    }

    .grid {
        display: grid;
        grid-gap: 1rem;
        grid-template-columns: repeat(auto-fit, minmax(9rem, 1fr));
        padding-top: 1rem;
        filter: blur(8px);

        @include respond-to(md) {
            grid-auto-flow: column;
            padding-top: 0;
        }
    }

    .cover {
        position: absolute;
        top: 0;
        left: 0;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        width: 100%;
        height: 100%;
        background: linear-gradient(
            90deg,
            $color-light-background 47.07%,
            rgba(255, 255, 255, 0) 100%
        );

        &ComingSoon {
            justify-content: center;
            background: rgba(255, 255, 255, 0.4);

            &Text {
                padding: 9px 42px;
                border-radius: 8px;
                background: rgba(0, 0, 0, 0.4);
                font-size: 32px;
                color: $white;
                font-weight: 500;
            }
        }
    }

    .coverImage {
        max-width: 16rem;
    }

    .coverInfo {
        max-width: 17.5rem;
        padding-left: 1.5rem;
    }

    .coverHeader {
        padding-bottom: 0.5rem;
        font-size: 1.5rem;
        line-height: 1.4;
        font-weight: bold;
        color: $color-main-font;
    }

    .coverText {
        font-size: 1rem;
        line-height: 1.5;
        font-weight: normal;
        color: $color-gray-dark-800;
    }
</style>
