<template>
    <div :class="$style.ForgotPage">
        <VImg src="/logoHorizontal-white_bg.svg" :class="$style.logo" alt="Логотип" contain />
        <VFadeTransition mode="out-in">
            <div v-if="!isSuccess" key="common" :class="$style.pageInner">
                <VForm>
                    <VTextField
                        v-model="form.email.$model.value"
                        :error-messages="form.email.$errorMessage.value"
                        type="email"
                        label="Email"
                        outlined
                        dense
                        @blur="form.email.$touch"
                        @input="form.email.$resetExtError"
                    />
                    <div :class="$style.buttonsWrapper">
                        <VBtn
                            color="accent"
                            :loading="isLoading"
                            :disabled="$invalid"
                            block
                            large
                            @click.prevent="handleSubmit"
                        >
                            Восстановить
                        </VBtn>
                        <VBtn
                            :class="$style.forgotSkipBtn"
                            to="/login"
                            text
                            nuxt
                            large
                            block
                            color="accent"
                        >
                            Отмена
                        </VBtn>
                    </div>
                </VForm>
            </div>
            <div v-else key="success" :class="$style.pageInner">
                <span class="title-h4 title-h4--bold">Готово</span>
                <div :class="$style.successText">
                    Ссылка для сброса пароля
                    <br />
                    отправлена на ваш Email
                </div>

                <VBtn color="accent" block large nuxt @click="isSuccess = false">Понятно</VBtn>
            </div>
        </VFadeTransition>
    </div>
</template>
<router lang="js">
  {
    path: '/forgot',
  }
</router>
<script>
    /* eslint-disable no-empty-function */
    import { defineComponent, reactive } from '@nuxtjs/composition-api';
    import { email, required } from '~utils/patterns';
    import { parseResponseError } from '~utils/response.utils';
    import { useForm } from '~use/form';
    import { useField } from '~use/field';

    export default defineComponent({
        name: 'ForgotPage',
        layout: 'login',

        transition: {
            name: 'fade',
            mode: 'out-in',
        },
        setup() {
            const formFields = {
                email: {
                    validators: { required, email },
                    errorMessages: {
                        required: 'Введите email',
                        email: 'Введите корректный email',
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
                notification: () => {},
                isLoading: false,
                isSuccess: false,
            };
        },
        head() {
            return {
                title: 'Восстановление пароля',
                htmlAttrs: {
                    class: 'static-rem',
                },
            };
        },
        methods: {
            async handleSubmit() {
                this.$touch();
                console.log(
                    '🚀 ~ file: forgot.vue ~ line 129 ~ handleSubmit ~ this.$invalid',
                    this.$invalid
                );

                if (this.$invalid) {
                    return;
                }
                try {
                    this.notification();
                    this.isLoading = true;
                    const response = await this.$axios.$post('/api/v1/reset-password-request', {
                        email: this.form.email.$model.value,
                    });
                    this.isLoading = false;
                    this.isSuccess = true;
                    console.log(
                        '🚀 ~ file: forgot.vue ~ line 226 ~ submitForm ~ response',
                        response
                    );
                } catch (error) {
                    console.log('🚀 ~ file: forgot.vue ~ line 259 ~ submitForm ~ error', error);

                    await this?.$sentry?.captureException(error);
                    this.isLoading = false;
                    const data = parseResponseError(error?.response);
                    if (!data?.messages?.length) {
                        return;
                    }
                    this.$notify.create({
                        message: data.messages[0],
                        type: 'negative',
                    });
                }
            },
        },
    });
</script>
<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .ForgotPage {
        display: flex;
        justify-content: center;
        height: 100%;
        padding-right: 24px;
        padding-left: 24px;
        flex-direction: column;
    }

    .buttonsWrapper {
        display: flex;
        gap: 16px;
        flex-direction: column;
        align-items: center;
        margin-top: 40px;
    }

    .pageHeading {
        margin-bottom: 32px;
        text-align: center;
        // @extend %text-h5;
        font-size: 22px;
        line-height: 1.25;
        font-weight: 700;
    }

    .logo {
        $logo-height: 135px;
        $logo-margin: 80px;

        width: 174px;
        height: $logo-height;
        max-height: $logo-height;
        margin-top: $logo-margin;
        margin-right: auto;
        margin-bottom: $logo-margin;
        margin-left: auto;

        @include respond-to(md) {
            margin-bottom: 24px;
        }
    }

    .pageInner {
        width: 100%;
        max-width: 368px;
        margin-right: auto;
        margin-left: auto;
        text-align: center;
    }

    .input {
        margin-bottom: 14px;
    }

    .successText {
        @extend %text-body-1;

        margin: 24px 0;
        text-align: center;
    }
</style>
