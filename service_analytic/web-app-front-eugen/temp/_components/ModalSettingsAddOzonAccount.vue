<template>
    <BaseDialog v-model="isShow" max-width="464">
        <VCard :class="$style.wrapper">
            <h2 :class="$style.heading">Добавить аккаунт</h2>
            <div :class="$style.textWrapper">
                <div :class="$style.title">{{ title }}</div>
                <div :class="$style.subtitle">{{ subtitle }}</div>
                <div :class="$style.link">Подробнее в инструкции</div>
            </div>
            <VForm :class="$style.formWrapper">
                <VTextField
                    v-model="form.clientId.$model.value"
                    :error-messages="form.clientId.$errorMessage.value"
                    label="Client ID"
                    autocomplete="current-password"
                    @blur="form.clientId.$touch"
                    @input="form.clientId.$resetExtError"
                />
                <VTextField
                    v-model="form.apiKey.$model.value"
                    :error-messages="form.apiKey.$errorMessage.value"
                    label="API-key"
                    autocomplete="current-password"
                    @blur="form.apiKey.$touch"
                    @input="form.apiKey.$resetExtError"
                />
                <VBtn
                    color="accent"
                    :class="$style.submit"
                    block
                    :disabled="$invalid"
                    @click.prevent="handleSubmit"
                >
                    Добавить
                </VBtn>
            </VForm>
        </VCard>
    </BaseDialog>
</template>

<script>
    /* eslint-disable no-empty-function */
    import { defineComponent, reactive } from '@nuxtjs/composition-api';

    import { useForm } from '~use/form';
    import { useField } from '~use/field';
    import { minLength, required } from '~utils/patterns';
    // import { getErrorMessage } from '~utils/error';

    export default defineComponent({
        name: 'ModalSettingsAddOzonAccount',
        props: {
            title: {
                required: true,
                type: String,
            },
            subtitle: {
                required: true,
                type: String,
            },
            platformId: {
                required: true,
                type: String,
            },
        },
        setup() {
            const formFields = {
                clientId: {
                    validators: { required },
                    errorMessages: {
                        required: 'Введите clientId',
                    },
                },
                apiKey: {
                    validators: { required, minLength: minLength(8) },
                    errorMessages: {
                        required: 'Введите apiKey',
                        minLength: 'Слишком короткий apiKey',
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
                notification: () => {},
            };
        },
        methods: {
            async handleSubmit() {
                this.$touch();
                if (this.$invalid) {
                    return;
                }
                this.notification();
                this.isLoading = true;

                try {
                    const response = await this.$axios.$post('/api/v1/set-access', {
                        title: this.form.title.$model.value,
                        client_id: this.form.clientId.$model.value,
                        client_api_key: this.form.apiKey.$model.value,
                        platform_id: this.platformId,
                    });
                    console.log(
                        '🚀 ~ file: OzonForm.vue ~ line 131 ~ handleSubmit ~ response',
                        response
                    );
                    this.notification = await this.$notify.create({
                        message: 'Аккаунт успешно привязан',
                        type: 'positive',
                    });
                    const { id } = await response?.data;
                    const responseVa = await this.$axios.$post(
                        '/api/vp/v2/notifications/new-account',
                        {
                            account_id: id,
                            client_id: this.form.clientId.$model.value,
                            client_api_key: this.form.apiKey.$model.value,
                            platform_id: this.platformId,
                        }
                    );

                    this.notification = await this.$notify.create({
                        message: responseVa?.data?.result,
                        type: 'positive',
                    });
                    this.isShow = false;
                } catch (error) {
                    console.log(
                        '🚀 ~ file: ModalSettingsAddOzonAccount.vue ~ line 105 ~ addAccount ~ error',
                        error
                    );

                    this.notification = await this.$notify.create({
                        message:
                            error?.response?.data?.error?.message ||
                            error?.message ||
                            'Произошла ошибка',
                        type: 'negative',
                    });
                }
            },
        },
    });
</script>
<style lang="scss" module>
    .wrapper {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        gap: 24px;
        border-radius: inherit;
        flex-direction: column;
    }

    .submit {
        margin-top: 16px;
    }

    .formField {
        width: 100%;
    }

    .heading {
        @extend %text-h4;

        text-align: center;
    }

    .textWrapper {
        .title {
            @extend %text-body-2;

            margin-bottom: 16px;
        }

        .subtitle {
            @extend %text-body-3;

            color: $base-800;
        }

        .link {
            @extend %text-body-3;

            display: inline-block;
            color: $accent;
            transition: $primary-transition;
            transition-property: opacity;
            cursor: pointer;

            &:hover {
                opacity: 0.7;
            }
        }
    }

    .formWrapper {
        width: 100%;
    }
</style>
