<template>
    <div :class="$style.SignUpPage" class="sign-up-page">
        <div v-if="flagModal" class="sign-up-page__modal">
            <div class="sign-up-page__modal-box">
                <div class="sign-up-page__modal-close" @click="handleCloseModal">
                    <SvgIcon name="outlined/close" />
                </div>
                <div class="sign-up-page__modal-img"></div>
                <p class="sign-up-page__modal-text">
                    На ваш Email было отправлено письмо со ссылкой для подтверждения регистрации!
                </p>
                <VBtn
                    class="sign-up-page__modal-button"
                    color="accent"
                    :class="$style.submitBtn"
                    block
                    large
                    @click="handleCloseModal"
                >
                    Отлично
                </VBtn>
            </div>
        </div>

        <a href="http://sellerexpert.ru" :class="$style.logo">
            <VImg src="/logoHorizontal-white_bg.svg" alt="Логотип" contain />
        </a>
        <div key="common" :class="$style.pageInner">
            <template v-if="awaitingConfirmCode">
                <h4 class="sign-up-page__text-big mb-6">Подтвердите номер</h4>
                <p class="sign-up-page__text-general mb-6">
                    Мы отправили SMS с кодом <br />на номер {{ phone }} ·
                    <a class="sign-up-page__text-link" @click.prevent="awaitingConfirmCode = false"
                        >Изменить</a
                    >
                </p>
                <VForm ref="formCode" v-model="formCodeValid" autocomplete="off">
                    <VTextField
                        v-model="code"
                        label="Код подтверждения"
                        outlined
                        dense
                        :rules="[rules.code]"
                    />
                </VForm>
                <p v-if="codeError" class="sign-up-page__text-red mb-4">Неверный код</p>
                <p v-if="buttonCountdown > 0" class="mb-4">
                    Повторить отправку через {{ buttonCountdown }}
                </p>
                <a v-else class="sign-up-page__text-link mb-4" @click.prevent="requestCode"
                    >Запросить новый код</a
                >
                <VBtn
                    color="accent"
                    :class="$style.submitBtn"
                    :disabled="isSubmitCodeDisabled"
                    block
                    large
                    :error="codeError"
                    @click="submitCode"
                >
                    Зарегистрироваться
                </VBtn>
            </template>
            <template v-else>
                <VForm ref="formBasic" v-model="formBasicValid" autocomplete="off">
                    <VTextField
                        v-model="name"
                        label="ФИО"
                        outlined
                        dense
                        :rules="[rules.nameEmpty, rules.nameLength]"
                    />
                    <VTextField
                        v-model="email"
                        type="email"
                        label="Email"
                        outlined
                        dense
                        :rules="[rules.emailEmpty, rules.emailValid]"
                    />
                    <VTextField
                        v-model="password"
                        label="Пароль"
                        hide-details
                        autocomplete="new-password"
                        :type="!isShowPass ? 'password' : 'text'"
                        :append-icon="!isShowPass ? '$eye' : '$eyeOff'"
                        outlined
                        dense
                        class="mb-5"
                        :rules="[
                            rules.passwordEmpty,
                            rules.passwordLength,
                            rules.passwordRule1,
                            rules.passwordRule2,
                        ]"
                        @click:append="handleToggleShowPass"
                    />
                    <ul class="pass-valid-list mb-5">
                        <li
                            v-for="item in validPasswordList"
                            :key="item.text"
                            class="pass-valid-list__item"
                            :class="{ active: item.validate, start: startInput }"
                        >
                            <SvgIcon
                                name="filled/check"
                                class="mr-2"
                                :color="item.validate ? '#710bff' : startInput ? '#ff3981' : ''"
                            ></SvgIcon>
                            {{ item.text }}
                        </li>
                    </ul>
                    <VTextField
                        v-model="password1"
                        label="Повторите пароль"
                        autocomplete="new-password"
                        :type="!isShowPass ? 'password' : 'text'"
                        :append-icon="!isShowPass ? '$eye' : '$eyeOff'"
                        outlined
                        dense
                        :rules="[
                            rules.passwordEmpty,
                            rules.passwordLength,
                            rules.passwordRule1,
                            rules.passwordRule2,
                            rules.sameAs,
                        ]"
                        @click:append="handleToggleShowPass"
                    />
                    <VTextField
                        v-if="!isProdOrDemo"
                        v-model="phone"
                        v-mask="'+7 (###) ###-##-##'"
                        label="Телефон"
                        dense
                        outlined
                        :rules="[rules.phoneLength, rules.phoneValid]"
                    />
                </VForm>
                <div :class="$style.checkboxWrapper">
                    <VBtn
                        :class="$style.checkboxIcon"
                        fab
                        small
                        plain
                        @click.native="handleToggleAccepted"
                    >
                        <BaseCheckbox :value="isAccepted" />
                    </VBtn>

                    <div :class="$style.checkboxText">
                        Я принимаю условия
                        <a href="/terms.pdf" target="_blank" :class="$style.link">
                            Лицензионного соглашения
                        </a>
                        и даю согласие на обработку Персональных данных
                    </div>
                </div>

                <VBtn
                    color="accent"
                    :class="$style.submitBtn"
                    :disabled="isRegistrationDisabled"
                    :loading="isLoading"
                    block
                    large
                    @click="handleSubmitForm"
                >
                    {{ isProdOrDemo ? 'Зарегистрироваться' : 'Отправить код' }}
                </VBtn>
            </template>

            <div :class="$style.text">Уже зарегистрированы?</div>
            <div :class="$style.btnWrapper">
                <VBtn :class="$style.signinBtn" to="/login" text nuxt color="accent"> Войти </VBtn>
            </div>
        </div>
    </div>
</template>
<router lang="js">
{
path: '/signup',
}
</router>
<script>
    /* eslint-disable no-empty-function */
    import { mask } from 'vue-the-mask';
    import { required, email, phone } from '~utils/patterns';
    import { errorHandler } from '~utils/response.utils';
    export default {
        name: 'SignUpPage',
        directives: { mask },
        layout: 'login',

        transition: {
            name: 'fade',
            mode: 'out-in',
        },
        data() {
            return {
                isSuccess: false,
                isAccepted: false,
                isShowPass: false,
                isLoading: false,
                flagModal: false,
                startInput: false,
                code: '',
                name: '',
                email: '',
                password: '',
                password1: '',
                phone: '',
                awaitingConfirmCode: false,
                buttonCountdown: 0,
                codeError: false,
                notification: () => {},
                formBasicValid: false,
                formCodeValid: false,
                rules: {
                    code: val => required(val) || 'Введите код',
                    phoneValid: val => phone(val) || 'Введите корректный номер',
                    phoneLength: val => val.length >= 10 || 'Слишком короткий номер',
                    emailEmpty: val => required(val) || 'Введите email',
                    emailValid: val => email(val) || 'Введите корректный email',
                    nameEmpty: val => required(val) || 'Введите имя',
                    nameLength: val => val.length >= 4 || 'Слишком короткое имя',
                    passwordEmpty: val => required(val) || 'Введите пароль',
                    passwordLength: val => val.length >= 8 || 'Минимальная длина пароля 8 символов',
                    passwordRule1: val =>
                        /^(?=.*[a-zA-Z])(?=.*[0-9]).{8,}/.test(val) ||
                        'Должен содержать цифры и латинские буквы',
                    passwordRule2: val =>
                        /(?=.*[A-Z])/.test(val) || 'Должен содержать заглавные и строчные буквы',
                    sameAs: () => this.password === this.password1 || 'Пароли должны совпадать',
                },
            };
        },

        head() {
            return {
                title: 'Регистрация',
                htmlAttrs: {
                    class: 'static-rem',
                },
            };
        },
        computed: {
            isRegistrationDisabled() {
                return !this.formBasicValid || !this.isAccepted;
            },
            validPasswordList() {
                const value = this.password;
                return [
                    { text: 'Минимум - 8 символов', validate: value.length > 7 },
                    {
                        text: 'Содержит цифры и латинские буквы',
                        validate: /^(?=.*[a-zA-Z])(?=.*[0-9]).{8,}/.test(value),
                    },
                    {
                        text: 'Содержит заглавные и строчные буквы',
                        validate: /(?=.*[A-Z])/.test(value),
                    },
                ];
            },
            isSubmitCodeDisabled() {
                return Boolean(!this.code.length);
            },
            phoneNumbersOnly() {
                return this.phone.replace(/\D/g, '');
            },
            isProdOrDemo() {
                return ['prod', 'demo'].includes(process.env.SERVER_TYPE);
            },
        },
        watch: {
            buttonCountdown(val) {
                if (val > 0) {
                    setTimeout(() => {
                        this.buttonCountdown--;
                    }, 1000);
                }
            },
        },
        methods: {
            handleToggleAccepted() {
                this.isAccepted = !this.isAccepted;
            },
            handleToggleShowPass() {
                this.isShowPass = !this.isShowPass;
            },
            async handleSubmitForm() {
                try {
                    if (this.isRegistrationDisabled) {
                        return;
                    }
                    this.notification();
                    this.isLoading = true;
                    const data = {
                        email: this.email,
                        name: this.name,
                        password: this.password,
                        phone: this.phoneNumbersOnly,
                    };
                    await this.$axios.$post('/api/v1/sign-up', data);
                    this.isLoading = false;

                    this.$sendGtm({ event: 'formSuccess', formName: 'registration' });

                    if (this.phone.length) {
                        this.awaitingConfirmCode = true;
                        this.requestCode();
                    } else {
                        this.flagModal = true;
                    }
                } catch (error) {
                    await this?.$sentry?.captureException(error);
                    this.isLoading = false;

                    errorHandler(error, this.$notify);
                    console.error(error);
                }
            },
            async submitCode() {
                try {
                    await this.$axios.$post('/api/v1/phone/confirm', {
                        phone: this.phoneNumbersOnly,
                        token: this.code,
                    });
                    this.buttonCountdown = 30;
                    this.flagModal = true;
                } catch (error) {
                    console.error(error);
                }
            },
            async requestCode() {
                try {
                    await this.$axios.$post('/api/v1/phone/send-code', {
                        phone: this.phoneNumbersOnly,
                    });
                } catch (error) {
                    console.error(error);
                }
            },
            handleCloseModal() {
                this.flagModal = false;
                this.$router.push('/login');
            },
        },
    };
</script>
<style lang="scss" scoped>
    /* stylelint-disable selector-pseudo-element-no-unknown */
    .pass-valid-list {
        margin: 0;
        padding: 0;
        text-align: left;

        &__item {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            font-size: 12px;
            color: $color-gray-dark-800;

            &.start {
                color: $error;
            }

            &.active {
                color: $primary-500;
            }
        }
    }

    .sign-up-page {
        &__modal {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 90;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            background: rgba(19, 19, 19, 0.4);
        }

        &__modal-close {
            position: absolute;
            top: 22px;
            right: 22px;
            cursor: pointer;
        }

        &__modal-box {
            position: relative;
            display: flex;
            justify-content: space-between;
            flex-flow: column nowrap;
            width: 488px;
            height: 644px;
            padding: 40px 60px 50px 60px;
            border-radius: 16px;
            background: #fff;
        }

        &__modal-img {
            width: 100%;
            height: 296px;
            background: url(/images/sendingemailimg.svg) no-repeat center/contain;
        }

        &__modal-text {
            text-align: center;
            font-size: 24px;
            font-style: normal;
            font-weight: 600;
            line-height: 33px;
            color: #2f3640;
        }

        &__modal-button {
            flex: 0 0 auto;
            margin-bottom: 0;
        }

        &__text {
            &-general {
                @extend %text-body-1;

                line-height: 27px;
            }

            &-big {
                @extend %text-body-1;

                font-size: 20px;
                line-height: 27px;
            }

            &-link {
                display: inline-block;
                color: $color-purple-primary;
            }

            &-red {
                color: $color-pink-dark;
            }
        }
    }
</style>
<style lang="scss" module>
    /* stylelint-disable declaration-no-important */

    $logo-width: 262px;
    $logo-height: 135px;
    $logo-margin: 52px;

    .SignUpPage {
        display: flex;
        justify-content: center;
        height: 100%;
        padding-right: 24px;
        padding-left: 24px;
        flex-direction: column;
    }

    .logo {
        display: block;
        width: $logo-width;
        max-height: $logo-height;
        margin-right: auto;
        margin-bottom: $logo-margin;
        margin-left: auto;
    }

    .successImg {
        width: 96px;
        height: 96px;
        margin-right: auto;
        margin-bottom: 24px;
        margin-left: auto;
    }

    .successText {
        @extend %text-body-1;

        margin: 24px 0;
        text-align: center;
    }

    .pageInner {
        width: 100%;
        max-width: 368px;
        margin-right: auto;
        // margin-bottom: $logo-margin * 2  + $logo-height;
        margin-left: auto;
        text-align: center;
    }

    .input {
        margin-bottom: 14px;
    }

    .checkboxWrapper {
        display: flex;
        margin-bottom: 24px;
    }

    .checkboxIcon {
        margin-top: -10px;
        margin-left: -10px;
    }

    .checkboxText {
        text-align: left;
        font-size: 12px;
        line-height: 135%;
        font-weight: 400;

        .link {
            text-decoration: none;
            color: $accent;
            transition: $primary-transition;
            font-weight: 700;

            &:hover {
                opacity: 0.7;
            }
        }
    }

    .submitBtn {
        margin-bottom: 24px;
    }

    .text {
        @extend %text-body-1;

        margin-bottom: 6px;
        text-align: center;
    }

    .btnWrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 56px;
    }

    .signinBtn {
        height: 32px !important;
        padding: 0 13px !important;
    }
</style>
