<template>
    <div class="dashboard-card" :class="$style.DashboardRecommendations">
        <h3 :class="$style.heading" class="dashboard-card__header dashboard-card__header--big">
            Рекомендации
        </h3>
        <div :class="$style.carouselWrapper">
            <VCarousel v-model="model" hide-delimiters height="100%">
                <!--                <template #prev="{ on, attrs }">-->
                <!--                    <VBtn icon light v-bind="attrs" v-on="on">-->
                <!--                        <SvgIcon name="outlined/chevronback" />-->
                <!--                    </VBtn>-->
                <!--                </template>-->
                <!--                <template #next="{ on, attrs }">-->
                <!--                    <VBtn icon light v-bind="attrs" v-on="on">-->
                <!--                        <SvgIcon name="outlined/chevronnext" />-->
                <!--                    </VBtn>-->
                <!--                </template>-->
                <!--                <VCarouselItem>-->
                <!--                    <div :class="$style.carouselItem">-->
                <!--                        <div :class="$style.title">Ведение рекламной кампании 📢</div>-->
                <!--                        <div :class="$style.subtitle">-->
                <!--                            Мы поможем настроить рекламу-->
                <!--                            <br />-->
                <!--                            и повысить ваши продажи!-->
                <!--                        </div>-->
                <!--                        <VBtn color="accent" @click="handleActionAdm">Оставить заявку</VBtn>-->
                <!--                    </div>-->
                <!--                </VCarouselItem>-->
                <VCarouselItem>
                    <div :class="$style.carouselItem">
                        <div :class="$style.title">Оптимизация товаров 📈</div>
                        <div :class="$style.subtitle">
                            Мы правильно заполним все характеристики
                            <br />
                            товара и поднимем ваши продажи
                        </div>
                        <VBtn
                            class="default-btn default-btn--size-middle"
                            outlined
                            @click="handleActionProducts"
                        >
                            Оставить заявку
                        </VBtn>
                    </div>
                </VCarouselItem>
            </VCarousel>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'DashboardRecommendations',
        data() {
            return {
                model: 0,
                colors: ['primary', 'secondary', 'yellow darken-2', 'red', 'orange'],
            };
        },
        methods: {
            handleActionProducts() {
                return this.$modal.open({
                    component: 'ModalProductsRequest',
                });
            },
            handleActionAdm() {
                return this.$modal.open({
                    component: 'ModalAdmRequest',
                });
            },
        },
    };
</script>

<style lang="scss" module>
    .DashboardRecommendations {
        display: flex;
        flex-direction: column;
        min-height: 19.5rem;

        @include respond-to(md) {
            //grid-column: span 3;
            background: none;
        }

        @include respond-to(sm) {
            //grid-column: 1 / -1;
            //grid-row: 3 / 4;
            min-height: 15.5rem;
        }

        //@include respond-to(sm) {
        //    grid-row: 4 / 5;
        //}

        :global(.v-window__prev),
        :global(.v-window__next) {
            margin: 0 !important;
            background: none !important;
        }
    }

    .carouselWrapper {
        flex: 1;
    }

    .heading {
        margin-bottom: 16px !important;
    }

    .carouselItem {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        height: 100%;
    }

    .title {
        margin-bottom: 16px;
        text-align: center;
        font-size: 24px;
        line-height: 1.25;
        color: $black;
        font-weight: bold;
    }

    .subtitle {
        margin-bottom: 32px;
        text-align: center;
        font-size: 16px;
        line-height: 1.25;
        color: $base-900;
    }
</style>
