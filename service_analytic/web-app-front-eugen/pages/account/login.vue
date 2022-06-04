<template>
    <div :class="$style.LoginPage">
        <a href="http://sellerexpert.ru" :class="$style.logo">
            <VImg src="/logoHorizontal-white_bg.svg" alt="Логотип" contain />
        </a>
        <div class="login-switch">
            <span :class="{ active: !loginEmail }" @click="switchLogin(false)">Телефон</span>
            <v-switch
                v-model="loginEmail"
                class="ml-4"
                inset
                flat
                dense
                :disabled="isProdOrDemo"
            ></v-switch>
            <span :class="{ active: loginEmail }" @click="switchLogin(true)">Email</span>
        </div>
        <div :class="$style.pageInner">
            <VForm ref="form" v-model="isValid">
                <VTextField
                    v-if="loginEmail"
                    ref="email"
                    v-model="email"
                    type="email"
                    autocomplete="username"
                    label="Email"
                    outlined
                    dense
                    :rules="[rules.emailEmpty, rules.email]"
                />
                <VTextField
                    v-else
                    ref="phone"
                    v-model="phone"
                    type="tel"
                    label="Номер телефона"
                    dense
                    outlined
                    :rules="[rules.phoneEmpty, rules.phone]"
                />
                <VTextField
                    v-model="password"
                    label="Пароль"
                    autocomplete="current-password"
                    :type="!isShowPass ? 'password' : 'text'"
                    :append-icon="!isShowPass ? '$eye' : '$eyeOff'"
                    outlined
                    dense
                    :rules="[rules.passRequired]"
                    @click:append="handleToggleShowPass"
                />
                <div :class="$style.controlsWrapper">
                    <VCheckbox
                        v-model="remember"
                        :class="$style.rememberCheck"
                        hide-details
                        color="accent"
                        label="Запомнить меня"
                    />
                    <VBtn text nuxt small color="accent" to="/forgot">Забыли пароль?</VBtn>
                </div>
                <VBtn
                    :class="$style.submit"
                    :disabled="!isValid"
                    :loading="isLoading"
                    block
                    large
                    color="accent"
                    @click="handleSubmit"
                >
                    Войти
                </VBtn>
                <div :class="$style.singupWrapper">
                    <h3 :class="$style.signupText">Нет аккаунта?</h3>
                    <div class="signin-btn">
                        <VBtn :class="$style.signupBtn" to="/signup" text nuxt color="accent">
                            Зарегистрироваться
                        </VBtn>
                    </div>
                </div>
            </VForm>
        </div>
    </div>
</template>
<router lang="js">
{
path: '/login',
}
</router>
<script>
    /* eslint-disable no-unused-vars,no-empty-function */
    import { mask } from 'vue-the-mask';
    import { email, required, phone } from '~utils/patterns';
    import { errorHandler } from '~utils/response.utils';
    import { mapMutations } from 'vuex';

    function parseResponseErrorObject(error) {
        if (error?.response?.data?.error?.advanced?.length) {
            return Object.fromEntries(
                error.response.data.error.advanced.map(current => Object.entries(current)).flat()
            );
        }
        return null;
    }

    export default {
        name: 'LoginPage',
        directives: { mask },
        layout: 'login',
        transition: {
            name: 'fade',
            mode: 'out-in',
        },
        data() {
            return {
                remember: false,
                isLoading: false,
                isShowPass: false,
                loginEmail: false,
                email: '',
                phone: '',
                password: '',
                isValid: false,
                notification: () => {},
                rules: {
                    email: val => email(val) || 'Введите корректный email',
                    emailEmpty: val => Boolean(val) || 'Введите email',
                    phone: val => phone(val) || 'Введите корректный номер',
                    phoneEmpty: val => Boolean(val) || 'Введите номер',
                    passRequired: val => required(val) || 'Введите пароль',
                    /* eslint-disable */
                    passMinLength: val => (val && val.length > 8) || 'Слишком короткий пароль',
                },
            };
        },
        head() {
            return {
                title: 'Авторизация',
                htmlAttrs: {
                    class: 'static-rem',
                },
            };
        },
        computed: {
            isProdOrDemo() {
                return ['prod', 'demo'].includes(process.env.SERVER_TYPE);
            },
        },
        watch: {
            loginEmail() {
                this.$refs.form.reset();
            },
        },
        mounted() {
            const field = this.loginEmail ? this?.$refs?.email : this?.$refs?.phone;
            field?.focus();
            if (this.isProdOrDemo) {
                this.loginEmail = true;
            }
        },
        beforeDestroy() {
            this.notification();
        },
        methods: {
            ...mapMutations('user', ['setField']),
            handleToggleShowPass() {
                this.isShowPass = !this.isShowPass;
            },
            async handleSubmit() {
                this.notification();
                this.isLoading = true;
                try {
                    const data = {
                        password: this.password,
                    };
                    if (this.loginEmail) {
                        data.email = this.email;
                    } else {
                        data.phone = this.phone;
                    }

                    await this.$auth.login({ data });

                    if (!this.$auth.user.phone && !this.isProdOrDemo) {
                        this.setField({ field: 'needToShowPhoneModal', value: true });
                    }

                    this.isLoading = false;
                } catch (error) {
                    const errorObject = parseResponseErrorObject(error);
                    if (errorObject) {
                        this.$setExtErrors(errorObject);
                    }
                    errorHandler(error, this.$notify);
                    console.error(error);
                    await this?.$sentry?.captureException(error);
                    this.isLoading = false;
                }
            },
            validate() {
                this.isValid = this.$refs.form.validate();
            },
            switchLogin(email) {
                if (this.isProdOrDemo) return false;
                this.loginEmail = email;
            },
        },
    };
</script>
<style lang="scss" scoped>
    /* stylelint-disable selector-pseudo-element-no-unknown */
    .login-switch {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        color: $color-gray-dark;
        cursor: pointer;

        & .active {
            color: $black;
        }

        &::v-deep .v-input--selection-controls__input {
            &:hover {
                .v-input--selection-controls__ripple {
                    &:before {
                        background: $primary-500;
                    }
                }
            }

            & .v-input--switch__thumb {
                background-color: white;
            }

            & .v-input--switch__track {
                color: $primary-500;
                opacity: 1;
            }
        }
    }
</style>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .rememberCheck {
        user-select: none;
        margin-top: 0;
        padding-top: 0;
    }

    .LoginPage {
        display: flex;
        justify-content: center;
        height: 100%;
        padding-right: 24px;
        padding-left: 24px;
        flex-direction: column;
    }

    .controlsWrapper {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .pageInner {
        width: 100%;
        max-width: 368px;
        margin-right: auto;
        margin-left: auto;
    }

    .logo {
        $logo-height: 85px;
        $logo-margin: 80px;

        display: block;
        width: 262px;
        height: $logo-height;
        max-height: $logo-height;
        //margin-top: $logo-margin;
        margin-right: auto;
        margin-bottom: 64px;
        margin-left: auto;

        @include respond-to(md) {
            margin-bottom: 48px;
        }
    }

    .input {
        margin-bottom: 14px;
    }

    .submit {
        margin-top: 32px;
    }

    .singupWrapper {
        display: flex;
        align-items: center;
        flex-direction: column;
        margin-top: 24px;
        margin-bottom: 40px;
    }

    .signupText {
        margin-bottom: 6px;
        text-align: center;
        font-size: 16px;
        user-select: none;
        font-weight: 400;
    }

    .signupBtn {
        height: 32px !important;
        padding: 0 13px !important;
    }
</style>
