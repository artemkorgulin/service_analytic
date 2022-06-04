<template>
    <div class="welcome-page">
        <VSheet ref="welcome" class="welcome-page__sheet">
            <template v-if="!showlastPage">
                <v-carousel
                    ref="myCarousel"
                    v-model="activeSlide"
                    :show-arrows="false"
                    :continuous="false"
                    height="600"
                >
                    <v-carousel-item v-for="(slide, i) in slides" :key="i" class="welcome-page__slide">
                        <img class="welcome-page__image" alt="" :src="slide.image" />
                        <div class="welcome-page__header-container mt-8">
                            <h4 class="welcome-page__header" v-html="slide.header" />
                            <span v-if="slide.soon" class="welcome-page__soon">скоро</span>
                        </div>
                        <p class="welcome-page__text pr-2 pl-2" v-html="slide.text" />
                    </v-carousel-item>
                </v-carousel>
                <div class="welcome-page__button-wrapper welcome-page__button-wrapper_left">
                    <VBtn
                        class="welcome-page__arrow"
                        icon
                        outlined
                        @click="slideMinus"
                    >
                        <SvgIcon name="outlined/chevronBack" />
                    </VBtn>
                </div>
                <div class="welcome-page__button-wrapper welcome-page__button-wrapper_right">
                    <VBtn
                        class="welcome-page__arrow"
                        icon
                        outlined
                        @click="slidePlus"
                    >
                        <SvgIcon name="outlined/chevronNext" />
                    </VBtn>
                </div>
            </template>
            <template v-else>
                <div class="last-page">
                    <img  class="welcome-page__image" alt="" :src="lastPage.image" />
                    <h4  class="welcome-page__header mt-8" v-html="lastPage.header" />
                    <p class="welcome-page__text" v-html="lastPage.text" />
                    <VBtn class="last-page__button" color="accent" large @click="handleAddMarketplace">
                        Добавить маркетплейс
                    </VBtn>
                </div>
            </template>
        </VSheet>
    </div>
</template>
<router lang="js">
{
path: '/welcome',
}
</router>
<script>
    import { mapActions } from 'vuex';

    export default {
        name: 'Welcome',
        data() {
            return {
                activeSlide: 0,
                showlastPage: false,
                move: [],
                drag: false,
                touch: false,
                slides: [
                    {
                        header: 'Добро пожаловать <br />в SellerExpert!',
                        text: 'Улучшайте карточки товаров, создавайте продающие дизайны, анализируйте рынок и управляйте<br /> рекламными кампаниями в одном личном кабинете',
                        image: '/images/man-offering-CN.svg',
                    },
                    {
                        header: 'Поисковая и контентная <br />оптимизация',
                        text: 'Повышайте видимость товара в каталоге и по <br />поисковым запросам с помощью наших рекомендаций, <br /> чтобы вас увидело больше покупателей',
                        image: '/images/welcome/products-table.png',
                    },
                    {
                        header: 'Управление рекламой',
                        text: 'Используйте автоматические стратегии, чтобы <br />достичь максимальной эффективности в короткие <br />сроки. Автоматические уведомления и рекомендации <br />помогут увеличить эффективность рекламы',
                        image: '/images/welcome/adm.png',
                        soon: true,
                    },
                    {
                        header: 'Аналитика брендов <br>и категорий',
                        text: 'Принимайте решения по ассортименту и ценам на <br />основе удобных детальных графиков и сводных таблиц',
                        image: '/images/welcome/analytics.png',
                    },
                ],
                lastPage: {
                    header: 'Начинаем!',
                    text: 'Чтобы увидеть текущую степень оптимизации и <br />повысить ее, добавьте товары с маркетплейса',
                    image: '/images/man-offering-CN.svg',
                    button: true,
                },
            };
        },
        methods: {
            ...mapActions('user', {
                setMenuState: 'setMenuState',
                setAccountSettingsMenuState: 'setAccountSettingsMenuState',
                setMarketplaceSettingsMenuState: 'setMarketplaceSettingsMenuState',
                setWelcomeGuideShown: 'setWelcomeGuideShown',
            }),
            slideMinus() {
                if (this.activeSlide < 1) {
                    return false;
                } else {
                    this.activeSlide--;
                }
            },
            slidePlus() {
                if (this.activeSlide < this.slides.length - 1) {
                    this.activeSlide++;
                } else {
                    this.showlastPage = true;
                }
            },
            handleAddMarketplace() {
                this.$router.push({
                    name: 'marketplaces',
                    params: { modalAddMpForceOpen: true },
                    query: { onboard: 1 },
                });
            },
            handleSwipe(e) {
                const currentMove = this.touch ? e.touches[0].clientX : e.clientX;
                if (this.move.length == 0) {
                    this.move.push(currentMove);
                }
                if (this.move[this.move.length - 1] - currentMove < -100) {
                    this.slideMinus();
                    this.drag = false;
                    this.touch = false;
                }
                if (this.move[this.move.length - 1] - currentMove > 100) {
                    this.slidePlus();
                    this.drag = false;
                    this.touch = false;
                }
            },
        },
        mounted() {
            // For touch devices
            this.$refs.myCarousel.$el.addEventListener('touchmove', (e) => {
                this.drag = false;
                this.touch = true;
                this.handleSwipe(e);
            });
            window.addEventListener('touchend', (e) => {
                this.move = [];
            });

            // For non-touch devices
            this.$refs.myCarousel.$el.addEventListener('mousedown', (e) => {
                this.drag = true;
                this.touch = false;
                this.handleSwipe(e);
            });
            this.$refs.myCarousel.$el.addEventListener('mousemove', (e) => this.drag ? this.handleSwipe(e) : null);
            window.addEventListener('mouseup', (e) => {
                this.drag = false;
                this.touch = false;
                this.move = [];
            });
        }
    };
</script>

<style lang="scss" scoped>
    /* stylelint-disable selector-pseudo-element-no-unknown */
    .welcome-page {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 100%;
        padding: 16px;

        &__sheet {
            @extend %sheet;

            position: relative;
            width: 488px;
            max-width: 488px;
            height: 620px;
            margin-bottom: 16px;
            padding: 0;
            gap: 16px;
            box-shadow: 0 8px 24px rgb(0 0 0 / 4%) !important;

            @media screen and (max-height: 720px) {
                flex-grow: 1;
                height: 100%;
            }
        }

        &__slide {
            padding-bottom: 50px;

            &::v-deep .v-responsive__content {
                overflow: hidden;
                display: flex;
                border-radius: 24px;
                flex-direction: column;
                gap: 16px;
                text-align: center;
            }
        }

        &__image {
            width: 100%;
            pointer-events: none;
        }

        &__header-container {
            position: relative;
            display: flex;
            margin: 0 auto;
        }

        &__header {
            display: flex;
            font-weight: 600;
            font-size: 24px;
            line-height: 1.375;
        }

        &__soon {
            position: absolute;
            top: -20px;
            right: -40px;
            padding: 4px 16px;
            border-radius: 50px;
            background-color: $color-yellow-bright;
            font-weight: 700;
            font-size: 12px;
            line-height: 16px;
            transform: rotate(27.68deg);
        }

        &__text {
            font-weight: normal;
            font-size: 16px;
            line-height: 1.5;
        }

        &__button-wrapper {
            position: absolute;
            bottom: 20px;
            z-index: 1;

            &.welcome-page__button-wrapper_left {
                right: calc(100% + 40px);
            }

            &.welcome-page__button-wrapper_right {
                left: calc(100% + 40px);
            }
        }

        &__arrow {
            width: 60px;
            height: 60px;
            background: $white;
            color: $black;

            &.welcome-page__arrow_background {
                &:after {
                    content: '';
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    z-index: -1;
                    width: 64px;
                    height: 64px;
                    border-radius: 50%;
                    background-color: rgba(113, 11, 255, 0.16);
                    transform: translate(-50%, -50%);
                }
            }

            &::v-deep .icon.sprite-outlined {
                width: 36px;
                height: 36px;
            }

            &::v-deep.v-btn.v-btn--outlined {
                border: none;
            }
        }

        .last-page {
            display: flex;
            align-items: center;
            flex-direction: column;
            gap: 16px;
            padding-bottom: 50px;
            text-align: center;

            &__button {
                margin-top: 32px;
            }
        }

        &::v-deep .v-carousel__controls {
            background: transparent;
        }

        &::v-deep .theme--dark.v-btn.v-btn--icon {
            color: $color-gray-light;
        }

        &::v-deep .theme--dark.v-btn.v-btn--active.v-btn--icon {
            color: $primary-500;

            &:before {
                opacity: 0;
            }
        }

        &::v-deep .v-carousel__controls__item {
            margin: 0 2px;

            .v-icon {
                opacity: 1;
            }
        }
    }
</style>
