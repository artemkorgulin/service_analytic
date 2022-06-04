<template>
    <div :class="$style.OzonSettings">
        <VCard flat :class="$style.card" min-height="210px">
            <h4 :class="$style.header" class="add-mp__title">Ozon управление товарами</h4>
            <div :class="$style.hintWrapper" class="add-mp__hint">
                <div :class="$style.hint">
                    Введите Client ID и API-key из раздела
                    <a
                        class="text--link"
                        target="_blank"
                        href="https://seller.ozon.ru/app/settings/api-keys"
                    >
                        Настройки
                    </a>
                    личного кабинета Ozon Seller
                </div>
            </div>

            <OzonForm platform-id="1" />
        </VCard>
    </div>
</template>

<script>
    import { mapMutations } from 'vuex';
    import onboarding from '~mixins/onboarding.mixin';
    export default {
        name: 'OzonSettings',
        mixins: [onboarding],
        data() {
            return {
                selectedPlatform: 0,
            };
        },
        mounted() {
            this.createOnBoarding();
        },
        methods: {
            ...mapMutations('modal', ['setModal']),

            createOnBoarding() {
                const inputs = this.$el.querySelectorAll('.v-input__slot');
                const elements = [
                    {
                        el: inputs[0],
                        intro: 'Придумайте название аккаунта',
                        pos: 'bottom',
                    },
                    {
                        el: inputs[1],
                        intro: 'Скопируйте сюда Client ID из раздела API ключи в <a href="https://seller.ozon.ru/app/settings/api-keys" target="_blank">настройках личного кабинета</a> OZON <a href="https://skr.sh/s/270122/Deh1WMHc.jpg?download=1&name=%D0%A1%D0%BA%D1%80%D0%B8%D0%BD%D1%88%D0%BE%D1%82%2002-02-2022%2014:46:16.jpg"><img src="https://skr.sh/s/270122/Deh1WMHc.jpg?download=1&name=%D0%A1%D0%BA%D1%80%D0%B8%D0%BD%D1%88%D0%BE%D1%82%2002-02-2022%2014:46:16.jpg" /></a>',
                        pos: 'bottom',
                    },
                    {
                        el: inputs[2],
                        intro: 'Скопируйте сюда токен API Key из раздела API ключи в <a href="https://seller.ozon.ru/app/settings/api-keys" target="_blank">настройках личного кабинета</a> OZON <a href="https://skr.sh/s/270122/pYGIgyxk.jpg?download=1&name=%D0%A1%D0%BA%D1%80%D0%B8%D0%BD%D1%88%D0%BE%D1%82%2002-02-2022%2014:47:02.jpg"><img src="https://skr.sh/s/270122/pYGIgyxk.jpg?download=1&name=%D0%A1%D0%BA%D1%80%D0%B8%D0%BD%D1%88%D0%BE%D1%82%2002-02-2022%2014:47:02.jpg"></a>',
                        pos: 'bottom',
                    },
                    {
                        el: document.querySelector('.form-actions__action'),
                        intro: 'Нажмите, чтобы подключить аккаунт маркетплейса',
                        pos: 'top',
                        clickToNext: true,
                    },
                ];

                const createOnBoardingParams = {
                    elements,
                    isDisplayOnboard: true,
                };

                this.createOnBoardingByParams(createOnBoardingParams);
            },

            handleOpenModalRequest() {
                this.setModal(true);
            },
        },
    };
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .tabs {
        position: relative;
        width: unset;
        margin-right: 16px;
        margin-bottom: 16px;
        margin-left: 16px;
        border-bottom: thin solid $base-400;
    }

    .tab {
        padding-right: 12px;
        padding-left: 12px;
        text-decoration: none !important;
        text-transform: none !important;
        font-size: 16px !important;
        letter-spacing: unset !important;
        font-weight: 500 !important;
    }

    .card {
        display: flex;
        flex-direction: column;
        gap: 16px;
        padding: 16px;
        padding-top: 0;
        border-bottom-left-radius: 8px !important;
        border-bottom-right-radius: 8px !important;
    }

    .link {
        display: inline-block;
        color: $accent;
        transition: $primary-transition;

        &:hover {
            opacity: 0.7;
        }
    }

    .btnSettings {
        width: 40px;
        min-width: 0 !important;
    }
</style>
