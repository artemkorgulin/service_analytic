<template>
    <div>
        <v-dialog v-model="transferFlag" scrollable max-width="450">
            <!-- persistent -->
            <div class="page__transfer">
                <div class="page__transfer-close">
                    <v-btn text icon @click="transferFlag = false">
                        <VIcon dense color="base-900">$close</VIcon>
                    </v-btn>
                </div>
                <div class="page__transfer-title">
                    Выберите компанию в которую хотите перенести маркетплейс
                </div>
                <VSelect
                    v-model="selectCompanyId"
                    label="Выбрать компанию"
                    :items="companies"
                    item-text="name"
                    item-value="id"
                    class="page__transfer-select per-page__select light-outline"
                    outlined
                    dense
                    hide-details
                    style="margin-bottom: 25px"
                />
                <div class="page__transfer-buttons">
                    <v-btn :disabled="selectCompanyId === null" color="accent" @click="transfer"
                        >Перенести</v-btn
                    >
                    <v-btn text style="margin-left: 10px" @click="transferFlag = false"
                        >Отмена
                    </v-btn>
                </div>
            </div>
        </v-dialog>
        <Page
            :title="title"
            :is-display-main-tpl="userActiveAccounts.length > 0"
            :btn-request="btnRequest"
            :btn-request-click="handleOpenModalRequest"
            :btn-add="btnAdd"
            :btn-add-click="handleAdd"
        >
            <div class="page__content">
                <SeTableAG
                    ref="table"
                    :columns="columnDefs"
                    :rows="marketplaces"
                    :row-height="50"
                    nostat
                    nobar
                />
            </div>
        </Page>
    </div>
</template>

<script>
    import { mapGetters, mapActions, mapState } from 'vuex';
    import onboarding from '~mixins/onboarding.mixin';
    import Page from '~/components/ui/SeInnerPage';
    import SeTableAG from '~/components/ui/SeTableAG.vue';
    import actionCellAccount from '~/components/pages/companies/actionCellAccount';

    export default {
        name: 'Marketplaces',
        components: {
            Page,
            SeTableAG,
            // eslint-disable-next-line vue/no-unused-components
            actionCellAccount,
        },
        mixins: [onboarding],
        data() {
            return {
                title: {
                    isActive: true,
                    text: 'Мои маркетплейсы',
                },
                user: null,
                account: null,
                maxCharApiKey: 60,
                transferFlag: false,
                selectCompanyId: null,
            };
        },
        computed: {
            ...mapGetters(['isSelectedMp', 'userActiveAccounts']),
            ...mapGetters('companies', ['companiesSelect']),
            ...mapState('companies', ['marketplaces']),
            btnRequest() {
                return {
                    isActive: true,
                    text: 'Оставить заявку',
                };
            },
            btnAdd() {
                return {
                    isActive: true,
                    isImg: true,
                    text: 'Добавить маркетплейс',
                    class: 'add-mp-btn',
                };
            },
            companies() {
                if (!this.user || !this.account) {
                    return [];
                }
                const company = this.user.companies.find(
                    item => item.id == this.account.data.company_id
                );
                const id = company ? company.id : 0;
                return this.companiesSelect.filter(item => item.id != id);
            },

            columnDefs() {
                return [
                    {
                        field: 'img',
                        headerName: '',
                        width: 64,
                        cellRenderer: function (params) {
                            if (!params.data) {
                                return '';
                            }

                            return `<img src="${params.data.img}" alt="Marketplace logo" class="mp-min-logo" />`;
                        },
                    },
                    {
                        field: 'title',
                        headerName: 'Название',
                        sortable: true,
                        filter: 'agTextColumnFilter',
                        flex: 2,
                    },
                    {
                        field: 'platform_client_id',
                        headerName: 'Client ID',
                        sortable: true,
                        filter: 'agTextColumnFilter',
                        flex: 2,
                    },
                    {
                        field: 'platform_api_key',
                        headerName: 'Api key',
                        sortable: true,
                        filter: 'agTextColumnFilter',
                        flex: 4,
                    },
                    {
                        field: 'company_id',
                        headerName: '',
                        sortable: true,
                        filter: 'agTextColumnFilter',
                        flex: 2,
                        cellRenderer: params => {
                            if (!params.data) {
                                return '';
                            }

                            return `${
                                this.companiesSelect.find(item => item.id == params.data.company_id)
                                    .name
                            }`;
                        },
                        filterParams: {
                            /* eslint-disable */
                            valueGetter: params =>
                                this.companiesSelect.find(item => item.id == params.data.company_id)
                                    .name,
                        },
                    },
                    {
                        headerName: 'Действия',
                        cellRenderer: 'actionCellAccount',
                        flex: 3,
                        cellRendererParams: {
                            clicked: value => {
                                this.transferFlag = true;
                                this.account = value;
                            },
                        },
                        flex: 1,
                        hide: !this.user.companies.length,
                    },
                ];
            },
        },
        watch: {
            '$route.params.modalAddMpForceOpen': {
                immediate: true,
                handler(val) {
                    if (val) {
                        return this.handleAdd();
                    }
                },
            },
            '$auth.$state.user': {
                immediate: true,
                handler(val) {
                    this.user = val;
                },
            },
        },
        async mounted() {
            this.createOnBoarding();
            await this.getAllAccounts();
        },
        methods: {
            ...mapActions('companies', ['transferAccount', 'getAllAccounts']),

            async transfer() {
                await this.transferAccount({
                    accountId: this.account.data.id,
                    companyToId: this.selectCompanyId,
                    companyFromId: this.account.data.company_id,
                });
                this.transferFlag = false;
                this.selectCompanyId = null;
                this.getAllAccounts();
            },

            createOnBoarding() {
                const elements = [
                    {
                        el: document.querySelectorAll('.add-mp-btn')[0],
                        intro: 'Нажмите, чтобы подключить маркетплейс',
                        pos: 'left',
                        callback: () => {
                            this.$store.commit('onBoarding/setOnboardActive', true);
                        },
                        clickToNext: true,
                    },
                ];

                const isDisplayOnboard =
                    !this.checkRouteForOnboarding() && this.userActiveAccounts.length;

                const createOnBoardingParams = {
                    elements,
                    routeNameFirstStep: this.$route.name,
                    isDisplayOnboard,
                };

                if (elements[0].el) {
                    this.createOnBoardingByParams(createOnBoardingParams);
                }
            },
            handleAdd() {
                this.$modal.open({
                    component: 'ModalTheMarketplaceSettings',
                });
            },
            handleOpenModalRequest() {
                return this.$modal.open({
                    component: 'ModalProductsRequest',
                });
            },
        },
    };
</script>

<style lang="scss">
    .mp-min-logo {
        position: relative;
        top: 8px;
        width: 32px;
        height: 32px;
    }
</style>

<style lang="scss" scoped>
    .page {
        &__transfer {
            padding: 21px 24px;
            background: #fff;

            &-close {
                display: flex;
                justify-content: flex-end;
            }

            &-title {
                padding: 16px 30px;
                text-align: center;
                font-size: 16px;
                line-height: 24px;
            }

            &-buttons {
                display: flex;
                width: 100%;

                & .v-btn {
                    width: 49%;
                }
            }
        }

        &__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            margin-top: 8px;

            &-actions {
                gap: 8px;
                flex-wrap: wrap;
            }

            h1 {
                font-size: 24px;
            }
        }

        &__content {
            margin-top: 25px;
        }
    }
</style>
