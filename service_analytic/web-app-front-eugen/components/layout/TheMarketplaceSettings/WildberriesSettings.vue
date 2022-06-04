<template>
    <div :class="$style.WildberriesSettings">
        <h4 :class="$style.header" class="add-mp__title">WB Управление товарами</h4>
        <div :class="$style.hintWrapper" class="add-mp__hint">
            <div :class="$style.hint">
                В настройках личного кабинета Wildberries:
                <ol class="pl-0 ml-0 mt-3">
                    <li>
                        1. Скопируйте Ключ для работы с API статистики X32 в разделе
                        <a
                            href="https://seller.wildberries.ru/supplier-settings/access-to-api"
                            target="_blank"
                            class="text--link"
                        >
                            Доступ к API
                        </a>
                        и вставьте в соответствующее поле.
                    </li>
                    <li class="mt-3">
                        2. Сгенерируйте и вставьте в соответствующее поле API токен в разделе
                        <a
                            href="https://seller.wildberries.ru/supplier-settings/access-to-new-api"
                            target="_blank"
                            class="text--link"
                        >
                            Доступ к новому API
                        </a>
                    </li>
                </ol>
            </div>
        </div>
        <WildberriesForm platform-id="3" />
    </div>
</template>

<script>
    import { mapState, mapMutations } from 'vuex';
    import onboarding from '~mixins/onboarding.mixin';

    export default {
        name: 'WildberriesSettings',
        mixins: [onboarding],
        data() {
            return {
                selectedPlatform: 0,
            };
        },
        computed: {
            ...mapState({
                isModalShow: state => state.modal.isModalShow,
            }),
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
                        intro: 'Скопируйте сюда Ключ для работы с API статистики X32 в разделе <a target="_blank" href="https://seller.wildberries.ru/supplier-settings/access-to-api">Доступ к API</a> в настройках личного кабинета WB <br /><a href="https://skr.sh/s/270122/xzf8kwa0.jpg?download=1&name=%D0%A1%D0%BA%D1%80%D0%B8%D0%BD%D1%88%D0%BE%D1%82%2002-02-2022%2014:33:39.jpg" target="_blank"><img src="https://skr.sh/s/270122/xzf8kwa0.jpg?download=1&name=%D0%A1%D0%BA%D1%80%D0%B8%D0%BD%D1%88%D0%BE%D1%82%2002-02-2022%2014:33:39.jpg" /></a>',
                        pos: 'bottom',
                    },
                    {
                        el: inputs[2],
                        intro: 'Сгенерируйте и скопируйте сюда API токен в разделе <a target="_blank" href="https://seller.wildberries.ru/supplier-settings/access-to-new-api">Доступ к новому API</a> в настройках личного кабинета WB <a href="https://skr.sh/s/270122/q6AzFM04.jpg?download=1&name=%D0%A1%D0%BA%D1%80%D0%B8%D0%BD%D1%88%D0%BE%D1%82%2002-02-2022%2014:38:55.jpg" target="_blank"><img src="https://skr.sh/s/270122/q6AzFM04.jpg?download=1&name=%D0%A1%D0%BA%D1%80%D0%B8%D0%BD%D1%88%D0%BE%D1%82%2002-02-2022%2014:38:55.jpg"></a>',
                        pos: 'bottom',
                    },
                    {
                        el: document.querySelector('.form-actions__action'),
                        intro: 'Нажмите, чтобы подключить аккаунт маркетплейса',
                        pos: 'top',
                        callback: () => {
                            this.$router.push({ query: {} });
                        },
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
    .WildberriesSettings {
        display: flex;
        flex-direction: column;
        gap: 16px;
        padding: 16px;
        padding-top: 0;
    }

    .btnSettings {
        width: 40px;
        min-width: 0 !important;
    }
</style>
