<template>
    <div :class="$style.SetPasswordPage">
        <VImg :class="$style.logo" alt="Логотип" contain src="/images/logo.svg" />
        <div :class="$style.pageInner">
            <VForm>
                <VTextField
                    v-model="form.password.$model.value"
                    :error-messages="form.password.$errorMessage.value"
                    label="Пароль"
                    autocomplete="new-password"
                    :type="!isShowPass ? 'password' : 'text'"
                    :append-icon="!isShowPass ? '$eye' : '$eyeOff'"
                    outlined
                    dense
                    @blur="form.password.$touch"
                    @input="form.password.$resetExtError"
                    @click:append="handleToggleShowPass"
                />
                <VTextField
                    v-model="form.password1.$model.value"
                    :error-messages="form.password1.$errorMessage.value"
                    label="Пароль"
                    autocomplete="new-password"
                    :type="!isShowPass ? 'password' : 'text'"
                    :append-icon="!isShowPass ? '$eye' : '$eyeOff'"
                    outlined
                    dense
                    @blur="form.password1.$touch"
                    @input="form.password1.$resetExtError"
                    @click:append="handleToggleShowPass"
                />
                <VBtn
                    :class="$style.submit"
                    color="accent"
                    block
                    large
                    :disabled="$invalid"
                    @click.prevent="handleSubmit"
                >
                    Установить пароль
                </VBtn>
            </VForm>
        </div>
    </div>
</template>
<router lang="js">
  {
    path: '/set-password',
  }
</router>
<script>
    /* eslint-disable no-empty-function */
    import { defineComponent, reactive } from '@nuxtjs/composition-api';
    import { minLength, required } from '~utils/patterns';
    import { parseResponseError } from '~utils/response.utils';
    import { useForm } from '~use/form';
    import { useField } from '~use/field';

    export default defineComponent({
        name: 'SetPasswordPage',
        layout: 'login',

        validate({ query }) {
            if (!query?.token) {
                return false;
            }
            return true;
        },
        transition: {
            name: 'fade',
            mode: 'out-in',
        },
        setup() {
            const formFields = {
                password: {
                    validators: { required, minLength: minLength(8) },
                    errorMessages: {
                        required: 'Введите пароль',
                        minLength: 'Слишком короткий пароль',
                    },
                },
                password1: {
                    validators: {
                        required,
                        minLength: minLength(8),
                        sameAs(val, field, formInstance) {
                            const pass = formInstance.password.$model.value;
                            return pass === val;
                        },
                    },
                    errorMessages: {
                        required: 'Введите пароль',
                        minLength: 'Слишком короткий пароль',
                        sameAs: 'Пароли должны совпадать',
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
                isShowPass: false,
                isLoading: false,
                notification: () => {},
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
            handleToggleShowPass() {
                this.isShowPass = !this.isShowPass;
            },
            async handleSubmit() {
                this.$touch();
                if (this.$invalid) {
                    return;
                }
                try {
                    this.notification();
                    this.isLoading = true;
                    const response = await this.$axios.$post('/api/v1/reset-password', {
                        token: this.$route.query.token,
                        password: this.form.password.$model.value, // this.form.password,
                    });
                    this.isLoading = false;
                    console.log(
                        '🚀 ~ file: forgot.vue ~ line 226 ~ submitForm ~ response',
                        response
                    );
                    this.$notify.create({
                        message: 'Пароль успешно изменен.',
                        type: 'positive',
                    });
                    this.$router.push('/login');
                } catch (error) {
                    await this?.$sentry?.captureException(error);
                    console.log('🚀 ~ file: forgot.vue ~ line 259 ~ submitForm ~ error', error);
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
    .SetPasswordPage {
        display: flex;
        justify-content: center;
        height: 100%;
        padding-right: 24px;
        padding-left: 24px;
        flex-direction: column;
    }
    $logo-height: 135px;
    $logo-margin: 60px;

    .logo {
        width: 174px;
        height: $logo-height;
        max-height: $logo-height;
        margin-top: $logo-margin;
        margin-right: auto;
        margin-bottom: $logo-margin;
        margin-left: auto;
    }

    .pageInner {
        width: 100%;
        max-width: 368px;
        margin-right: auto;
        margin-left: auto;
    }

    .submit {
        margin-top: 32px;
    }
</style>
