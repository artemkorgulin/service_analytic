<template>
    <div class="dashboard-page">
        <VFadeTransition mode="out-in" appear>
            <div v-if="$fetchState.pending" key="loading" class="loading-wrapper">
                <VProgressCircular indeterminate size="100" color="accent" />
            </div>
            <ErrorBanner
                v-else-if="$fetchState.error"
                key="error"
                :message="
                    $fetchState.error && $fetchState.error.message
                        ? $fetchState.error.message
                        : 'Произошла ошибка'
                "
            />
            <div v-else class="dashboard-page-grid">
                <DashboardProductsTotal :number="productsStatistic.countProducts" class="dashboard-page-grid__total" />
                <DashboardAverageRating :rating="productsStatistic.avgRatingProducts" class="dashboard-page-grid__avg-rating" />
                <DashboardAverageOptimization :value="productsStatistic.avgOptimizeProducts" class="dashboard-page-grid__avg-optimization" />
                <DashboardAdvertising :data="admStatistic" class="dashboard-page-grid__advertising" />
                <DashboardRecommendations class="dashboard-page-grid__recommendations" />
                <DashboardTopByPositions  class="dashboard-page-grid__top-by-positions" :items="topProducts" />
                <DashboardTariff class="dashboard-page-grid__tariff" />
                <!-- <VBtn @click="$store.dispatch('reloadUserAccounts')">Обновить пользователя</VBtn> -->
            </div>
        </VFadeTransition>
    </div>
</template>
<script>
    import { mapActions, mapMutations, mapState } from 'vuex';
    import PageErrors from '~mixins/pageErrors.js';

    export default {
        name: 'DashboardPage',
        mixins: [PageErrors],
        // middleware: ['auth'],
        // middleware({ $auth, redirect }) {
        //     console.log('🚀 ~ file: brand.vue ~ line 36 ~ middleware ~ $auth', $auth);
        //     if (!$auth.loggedIn) {
        //         return redirect('/login');
        //     }
        // },
        middleware: [
            ({ store, redirect }) => {
                if (
                    store.$auth.user &&
                    store.$auth.user.first_login === 1 &&
                    store.getters['user/isWelcomeGuideShown'] === false
                ) {
                    redirect({ name: 'welcome' });
                }
            },
        ],
        data() {
            return {
                productsStatistic: {
                    countProducts: null,
                    avgRatingProducts: null,
                    avgOptimizeProducts: null,
                },
                topProducts: [],
                notifications: [],
                admStatistic: null,
            };
        },
        async fetch() {
            const requestsQueue = [
                this.reloadUserAccounts(),
                this.fetchProductStatistic(),
                this.fetchTopPositionProduct(),
                this.fetchCampaignsStatistic(),
            ];
            await Promise.all(requestsQueue);
            if (this.pageErrors.length === requestsQueue.length) {
                const errorMessage = 'Произошла ошибка при получении данных';
                if (process.server) {
                    this.$nuxt.context.res.statusCode = 404;
                }
                throw new Error(errorMessage);
            } else {
                await this.$_generateErrors();
            }
        },
        head() {
            return {
                title: 'Главная',
                htmlAttrs: {
                    class: 'static-rem',
                },
            };
        },
        computed: {
            ...mapState('user', ['needToShowPhoneModal']),
        },
        mounted() {
            if (this.needToShowPhoneModal) {
                this.$emitter.on(`modal-close-${this.$route.name}`, this.handlePhoneModalClose);
                this.$modal.open({
                    component: 'ModalPhoneNumber',
                    attrs: {
                        srcPage: this.$route.name,
                    },
                });
            }
        },
        beforeDestroy() {
            this.$emitter.off(`modal-close-${this.$route.name}`, this.handlePhoneModalClose);
        },
        methods: {
            ...mapActions(['reloadUserAccounts']),
            ...mapMutations('user', { setUserField: 'setField' }),

            // /api/vp/v1/product_statistic
            // Ранее используемый метод, выдавал неправильные данные
            async fetchProductStatistic() {
                try {
                    const response = await this.$axios.$get('/api/an/v1/common-user-statistic');

                    this.productsStatistic = response;
                } catch (error) {
                    this.$_addError(error, true);
                    await this?.$sentry?.captureException(error);
                }
            },

            async fetchTopPositionProduct() {
                try {
                    const response = await this.$axios.$get(
                        '/api/an/v1/top-position-product?limit=10'
                    );
                    this.topProducts = await response;
                } catch (error) {
                    this.$_addError(error, true);
                    await this?.$sentry?.captureException(error);
                }
            },
            async fetchCampaignsStatistic() {
                try {
                    const { data } = await this.$axios.$get('/api/adm/v2/campaigns-statistic');
                    this.admStatistic = data;
                } catch (error) {
                    this.$_addError(error, true);
                    await this?.$sentry?.captureException(error);
                }
            },
            async fetchNotifications() {
                try {
                    const response = await this.$axios.$get('/api/event/v1/notifications');

                    this.notifications = response;
                } catch (error) {
                    this.$_addError(error, true);
                    await this?.$sentry?.captureException(error);
                }
            },
            handlePhoneModalClose() {
                return this.setUserField({ field: 'needToShowPhoneModal', value: false });
            },
        },
    };
</script>

<style lang="scss" scoped>
    /* stylelint-disable selector-pseudo-element-no-unknown */
    .dashboard-page {
        position: relative;
        display: flex;
        min-height: 100%;

        &-grid {
            display: grid;
            width: 100%;
            min-height: 100%;
            padding: 2rem;
            grid-gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(28rem, 1fr));
            grid-template-rows: none;
            grid-auto-flow: row dense;

            @include respond-to(md) {
                padding: 0.5rem 0;
                grid-gap: 0.5rem;
                grid-template-columns: repeat(6, 1fr);
            }

            &__total,
            &__avg-rating {
                @include respond-to(md) {
                    grid-column: span 3;
                }

                @include respond-to(xs) {
                    grid-column: 1 / -1;
                }
            }

            &__avg-optimization {
                @include respond-to(md) {
                    grid-column: span 3;
                }

                @include respond-to(sm) {
                    grid-column: 1 / -1;
                }

                @include respond-to(xs) {
                    grid-column: 1 / -1;
                }
            }

            &__advertising {
                @include respond-to(lg) {
                    grid-column: 1 / -1;
                }
            }

            &__recommendations {
                @include respond-to(md) {
                    grid-column: span 3;
                }

                @include respond-to(sm) {
                    grid-column: 1 / -1;
                }
            }

            &__top-by-positions {
                grid-column: span 2;
            }

            &__tariff {
                @include respond-to(md) {
                    grid-column: 1 / -1;
                    grid-row: 4 / 5;
                }

                @include respond-to(sm) {
                    grid-row: 5 / 6;
                }

                @include respond-to(xs) {
                    grid-row: 6 / 7;
                }
            }
        }

        & .loading-wrapper {
            @include centerer;
        }

        &::v-deep .dashboard-card {
            padding: 1rem;
            border-radius: 16px;
            background: $white;

            @include cardShadow;

            @include respond-to(md) {
                border-radius: 0;
                border: none;
            }

            &__header {
                margin-bottom: 4px;
                font-size: 1rem;
                line-height: 1.4;
                font-weight: 600;
                color: $base-700;

                &.dashboard-card__header--big {
                    @include respond-to(md) {
                        padding-bottom: 1rem;
                        border-bottom: 1px $color-gray-light solid;
                        font-size: 1rem;
                        font-weight: 600;
                    }
                }

                @include respond-to(md) {
                    font-size: 0.75rem;
                    font-weight: 700;
                }
            }

            &__text {
                font-size: 0.75rem;
                line-height: 1.4;
                font-weight: 400;
            }

            &__big-text {
                font-size: 2.5rem;
                line-height: 1.4;
                color: $color-main-font;
                font-weight: bold;

                @include respond-to(md) {
                    font-size: 1.25rem;
                    font-weight: bold;
                }
            }
        }

        &::v-deep .card-header {
            color: red;
        }

        &::v-deep .custom-scrollbar::-webkit-scrollbar {
            background-color: transparent;
        }
    }
</style>
