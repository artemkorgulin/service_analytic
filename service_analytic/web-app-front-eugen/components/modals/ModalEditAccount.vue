<template>
    <BaseDialog v-model="isShow" content-class="modal-confirm-content" width="560">
        <VCard :class="$style.wrapper">
            <VFadeTransition mode="out-in" appear>
                <VProgressCircular
                    v-if="isLoading"
                    key="loading"
                    indeterminate
                    size="50"
                    color="accent"
                />
                <div v-else key="content" :class="$style.inner">
                    <VForm :class="$style.AccountForm">
                        <!-- временно отключено редактирование аккаунта-->
                        <!--                        <h2 :class="$style.heading">Изменение аккаунта</h2>-->
                        <h2 :class="$style.heading">Аккаунт</h2>
                        <VTextField
                            v-model="accountTitle"
                            :class="$style.input"
                            label="Название аккаунта"
                            outlined
                            hide-details
                            dense
                            disabled
                            color="accent"
                        />
                        <VTextField
                            v-model="clientId"
                            :class="$style.input"
                            label="Client ID"
                            outlined
                            hide-details
                            dense
                            disabled
                            color="accent"
                        />
                        <VTextField
                            v-model="apiKey"
                            :class="$style.input"
                            label="API-ключ"
                            outlined
                            hide-details
                            dense
                            disabled
                            color="accent"
                        />
                        <!-- временно отключено редактирование аккаунта-->
                        <!--                        <div :class="$style.btnWrapper">-->
                        <!--                            <VBtn-->
                        <!--                                color="accent"-->
                        <!--                                :class="[$style.btn]"-->
                        <!--                                :loading="isLoading"-->
                        <!--                                @click="handleSend"-->
                        <!--                            >-->
                        <!--                                Изменить-->
                        <!--                            </VBtn>-->
                        <!--                            <VBtn-->
                        <!--                                :class="[$style.btn, $style.cansel]"-->
                        <!--                                outlined-->
                        <!--                                @click="handleClose"-->
                        <!--                            >-->
                        <!--                                Отмена-->
                        <!--                            </VBtn>-->
                        <!--                        </div>-->
                    </VForm>
                </div>
            </VFadeTransition>
        </VCard>
    </BaseDialog>
</template>

<script>
    import { mapActions } from 'vuex';

    import { defineComponent, reactive } from '@nuxtjs/composition-api';
    import { useForm } from '~use/form';
    import { useField } from '~use/field';
    import { minLength, required } from '~utils/patterns';
    import { getErrorMessage } from '~utils/error';

    export default defineComponent({
        name: 'ModalEditAccount',
        setup() {
            const formFields = {
                accountTitle: {
                    validators: { required },
                    errorMessages: {
                        required: 'Введите название аккаунта',
                    },
                },
                clientId: {
                    validators: { required },
                    errorMessages: {
                        required: 'Введите Client ID',
                    },
                },
                apiKey: {
                    validators: { required, minLength: minLength(8) },
                    errorMessages: {
                        required: 'Введите API-ключ',
                        minLength: 'Слишком короткий API-ключ',
                    },
                },
            };
            const formObject = reactive({});
            for (const field in formFields) {
                formObject[field] = useField(formFields[field], formObject);
            }
            const $validation = useForm(formObject);
            return {
                ...$validation,
            };
        },
        data() {
            return {
                isShow: true,
                isLoading: false,
                // eslint-disable-next-line no-empty-function
                notification: () => {},
                accountTitle: '',
                clientId: '',
                apiKey: '',
            };
        },
        mounted() {
            this.accountTitle = this.$store.state.user.currentEditAccountData.title;
            this.clientId = this.$store.state.user.currentEditAccountData.platform_client_id;
            this.apiKey = this.$store.state.user.currentEditAccountData.platform_api_key;
        },
        methods: {
            ...mapActions('user', {
                fetchCompanies: 'fetchCompanies',
            }),
            async handleSend() {
                this.isLoading = true;
                try {
                    const response = await this.$axios.post('/api/v1/edit-access', {
                        title: this.accountTitle,
                        client_id: this.clientId,
                        client_api_key: this.apiKey,
                        platform_id: this.$store.state.user.currentEditAccountData.platform_id,
                        account_id: this.$store.state.user.currentEditAccountData.id,
                    });
                    const responseVa = await this.$axios.$post(
                        '/api/vp/v2/notifications/new-account',
                        {
                            account_id: this.$store.state.user.currentEditAccountData.id,
                            client_id: this.clientId,
                            client_api_key: this.apiKey,
                            platform_id: this.$store.state.user.currentEditAccountData.platform_id,
                        }
                    );
                    console.log(
                        '🚀 ~ file: ModalEditAccount.vue ~ line 148 ~ handleSend ~ responseVa',
                        responseVa
                    );
                    this.$store.commit('SET_ACCOUNT', response.data.data);
                    this.isLoading = false;
                    this.isShow = false;
                    this.notification = await this.$notify.create({
                        message: 'Аккаунт успешно изменён',
                        type: 'positive',
                    });
                } catch (error) {
                    this.isLoading = false;
                    this.isShow = false;
                    console.log(
                        '🚀 ~ file: ModalEditAccount.vue ~ line 148 ~ handleSend ~ err',
                        error
                    );
                    this.notification = await this.$notify.create({
                        message: getErrorMessage(error),
                        type: 'negative',
                    });
                }
            },
            handleClose() {
                this.isShow = false;
            },
        },
    });
</script>

<style lang="scss" module>
    .AccountForm {
        position: relative;
        display: flex;
        width: 100%;
        will-change: transform;
        flex-direction: column;
        gap: 16px;
    }

    .wrapper {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 150px;
        border-radius: inherit;
    }

    .inner {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        padding: 24px;
        gap: 24px;
        flex-direction: column;
    }

    .heading {
        @extend %text-h4;

        text-align: center;
    }

    .btnWrapper {
        gap: 16px;
        display: flex;
        width: 100%;
    }

    .btn {
        flex: 1;
    }

    .loadingWrapper {
        @include centerer;
    }
</style>
