<template>
    <VForm ref="formAction" :class="$style.WildberriesForm">
        <VTextField
            v-model="form.title.$model.value"
            :error-messages="form.title.$errorMessage.value"
            :class="$style.input"
            class="light-outline"
            label="Название аккаунта"
            outlined
            dense
            color="accent"
            @blur="form.title.$touch"
            @input="form.title.$resetExtError"
        />
        <VTextField
            v-model="form.clientId.$model.value"
            :error-messages="form.clientId.$errorMessage.value"
            :class="$style.input"
            label="Ключ для работы с API статистики x32 WB"
            class="light-outline"
            outlined
            dense
            color="accent"
            @blur="form.clientId.$touch"
            @input="form.clientId.$resetExtError"
        />
        <VTextField
            v-model="form.apiKey.$model.value"
            :error-messages="form.apiKey.$errorMessage.value"
            :class="$style.input"
            class="light-outline"
            label="API токен"
            outlined
            dense
            color="accent"
            @blur="form.apiKey.$touch"
            @input="form.apiKey.$resetExtError"
        />
    </VForm>
</template>

<script>
    /* eslint-disable no-empty-function*/
    import { defineComponent, reactive } from '@nuxtjs/composition-api';
    import { mapActions } from 'vuex';

    import { useForm } from '~use/form';
    import { useField } from '~use/field';
    import { errorParser } from '~utils/helpers';
    import { minLength, required, maxLength } from '~utils/patterns';
    import addMp from '~/assets/js/mixins/addMp';

    export default defineComponent({
        name: 'WildberriesForm',
        mixins: [addMp],
        props: {
            platformId: {
                type: [String, Number],
                required: true,
            },
        },
        setup() {
            const formFields = {
                title: {
                    validators: { required, maxLength: maxLength(30) },
                    errorMessages: {
                        required: 'Введите название аккаунта',
                        maxLength: 'Сократите название до 30 символов',
                    },
                },
                clientId: {
                    validators: { required },
                    errorMessages: {
                        required: 'Введите ключ для работы с API статистики x32 WB',
                    },
                },
                apiKey: {
                    validators: { required, minLength: minLength(8) },
                    errorMessages: {
                        required: 'Введите API токен',
                        minLength: 'Слишком короткий API токен',
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
                isLoading: false,
                notification: () => {},
            };
        },
        methods: {
            ...mapActions('companies', ['transferAccount', 'getAllAccounts']),

            async handleSubmit(platformId) {
                this.$touch();
                if (this.$invalid) {
                    return;
                }
                this.notification();
                this.isLoading = true;

                try {
                    const company = this.$auth.user.companies.filter(
                        item => item.pivot.is_selected == 1
                    );
                    const company_id = company?.[0]?.id || 0;

                    const response = await this.$axios.$post('/api/v1/set-access', {
                        title: this.form.title.$model.value,
                        client_id: this.form.clientId.$model.value,
                        client_api_key: this.form.apiKey.$model.value,
                        platform_id: 3,
                        company_id,
                    });

                    this.$sendGtm('api_wb');

                    this.notification = await this.$notify.create({
                        message: 'Аккаунт успешно привязан',
                        type: 'positive',
                    });
                    const { id } = await response?.data;
                    const data = {
                        platform_id: 3,
                        account_id: id,
                        client_id: this.form.clientId.$model.value,
                        client_api_key: this.form.apiKey.$model.value,
                    };

                    const responseVa = await this.$axios.$post(
                        '/api/vp/v2/notifications/new-account',
                        data
                    );
                    this.notification = await this.$notify.create({
                        message: responseVa?.data?.result,
                        type: 'positive',
                    });
                    this.$modal.close({
                        component: 'ModalTheMarketplaceSettings',
                    });
                    this.getAllAccounts();

                    this.isLoading = false;
                    this.$store.dispatch('reloadUserAccounts');
                } catch (error) {
                    const {
                        data: { error: serverError },
                    } = error.response;
                    console.error(serverError);
                    errorParser(this, serverError);
                    this.isLoading = false;
                }
            },
        },
    });
</script>

<style lang="scss" module>
    .WildberriesForm {
        position: relative;
        display: flex;
        width: 100%;
        will-change: transform;
        flex-direction: column;
    }
</style>
