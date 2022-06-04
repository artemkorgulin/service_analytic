<template>
    <div class="prod-na-acc">
        <div v-if="noAddedAcc" class="prod-na-acc__inner-text">
            <img src="/images/monitorMarket.png" alt="" />
            <p class="mt-7">
                Пожалуйста, подключите маркетплейс и добавьте API-ключ, чтобы вы смогли добавить
                товары
            </p>
            <div class="ob-add-product-01 mt-7">
                <VBtn
                    color="primary"
                    @click="
                        $modal.open({
                            component: 'ModalTheMarketplaceSettings',
                        })
                    "
                >
                    Подключить маркетплейс
                </VBtn>
            </div>
        </div>
        <div v-else class="prod-na-acc__inner-text">
            <img src="/images/cartMarket.png" alt="" />
            <p class="mt-7">
                Добавьте товары с маркетплейса, чтобы оптимизировать карточки и отслеживать
                видимость
            </p>
            <div class="ob-add-product-01 mt-7">
                <VBtn color="accent" @click="handleAddProduct">
                    <SvgIcon name="outlined/plus" data-left />
                    Добавить товар
                </VBtn>
            </div>
        </div>
    </div>
</template>

<script>
    import onboarding from '~mixins/onboarding.mixin';
    export default {
        mixins: [onboarding],
        props: {
            noAddedAcc: Boolean,
        },
        data() {
            return {};
        },
        mounted() {
            this.createOnBoarding();
        },
        methods: {
            handleAddProduct() {
                return this.$modal.open({
                    component: 'AddProductsFromMarketplace',
                });
            },
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

                if (this.noAddedAcc) {
                    elements[0].intro = 'Нажмите, чтобы подключить маркетплейс';
                }

                const isDisplayOnboard = !this.checkRouteForOnboarding();

                const createOnBoardingParams = {
                    elements,
                    routeNameFirstStep: this.$route.name,
                    isDisplayOnboard,
                };

                this.createOnBoardingByParams(createOnBoardingParams);
            },
        },
    };
</script>

<style lang="scss" scoped>
    .prod-na-acc {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;

        &__inner-text {
            flex: auto;
            max-width: 305px;
            text-align: center;
            font-size: 14px;
        }
    }
</style>
