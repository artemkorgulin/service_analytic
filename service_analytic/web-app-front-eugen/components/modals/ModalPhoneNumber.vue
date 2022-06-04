<template>
    <BaseDialog v-model="isShow" content-class="modal-confirm-content" width="560">
        <VCard class="card">
            <VFadeTransition mode="out-in" appear>
                <VProgressCircular
                    v-if="isLoading"
                    key="loading"
                    indeterminate
                    size="50"
                    color="accent"
                />
                <div v-else key="content" class="card-inner">
                    <VBtn class="close-button" depressed @click="handleClose">
                        <SvgIcon class="close-button__icon" name="outlined/close" />
                    </VBtn>
                    <h2 class="heading">Подтвердите свою учетную запись</h2>
                    <p class="text">Укажите номер телефона. В дальнейшем вы сможете <br>авторизоваться в личном кабинете, указав ваш телефон и пароль <br>при входе</p>
                    <VForm ref="form" v-model="isValid" class="w-full text-center">
                        <VTextField
                            v-model="phone"
                            v-mask="'+7 (###) ###-##-##'"
                            label="Введите номер телефона"
                            class="w-full light-outline"
                            persistent-placeholder
                            dense
                            outlined
                            :rules="[rules.phoneEmpty, rules.phone]"
                            @keydown="handlePhoneInputChange"
                        />
                        <template v-if="numberSent">
                            <VTextField
                                v-model="code"
                                label="Код подтверждения"
                                class="w-full"
                                dense
                                outlined
                                :rules="[rules.code]"
                            />
                            <a v-if="!buttonCountdown" class="link" @click.prevent="requestCode">Запросить новый код</a>
                        </template>
                        <p v-if="buttonCountdown > 0" class="text">Запросить новый код через {{ buttonCountdownMinutes }} </p>
                    </VForm>
                    <div class="card__button-container">
                        <VBtn class="button"
                              color="primary"
                              :disabled="sendButtonDisabled"
                              depressed
                              @click="sendButtonMethod"
                        >
                            {{ sendButtonText }}
                        </VBtn>
                        <VBtn class="button" outlined  @click="handleClose">
                            Отмена
                        </VBtn>
                    </div>
                </div>
            </VFadeTransition>
        </VCard>
    </BaseDialog>
</template>

<script>
    // import { errorHandler } from '~utils/response.utils';
    import { intervalToDuration } from 'date-fns';
    import { mask } from 'vue-the-mask';
    import { required, phone } from '~utils/patterns';

    export default {
        name: 'ModalPhoneNumber',
        directives: { mask },
        props: {
            srcPage: {
                required: false,
                default: null,
            },
        },
        data() {
            return {
                isShow: true,
                isLoading: false,
                numberSent: false,
                buttonCountdown: 0,
                isValid: false,
                phone: '',
                code: '',
                rules: {
                    phone: val => phone(val) || 'Введите корректный номер',
                    phoneEmpty: val => required(val) || 'Введите номер',
                    code: val => required(val) || 'Введите код',
                },
            };
        },
        computed: {
            sendButtonText() {
                return this.numberSent ? 'Подтвердить' : 'Получить код';
            },
            sendButtonDisabled() {
                return this.numberSent && this.code.length < 4 || !this.numberSent && this.buttonCountdown > 0;
            },
            buttonCountdownMinutes() {
                const { minutes, seconds } = intervalToDuration({ start: 0, end: this.buttonCountdown * 1000 });
                return `${minutes}:${seconds}`;
            },
            phoneNumbersOnly() {
                return this.phone.replace(/\D/g, '');
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
            handleClose() {
                if (this.srcPage) {
                    this.$emitter.emit(`modal-close-${this.srcPage}`);
                }

                this.isShow = false;
            },
            sendButtonMethod() {
                this.validate();

                if (!this.isValid) {
                    return;
                }

                if (this.numberSent) {
                    this.submitCode();
                } else {
                    this.savePhone();
                }
            },
            async savePhone() {
                try {
                    await this.$axios.$post('/api/v1/set-settings', {
                        phone: this.phoneNumbersOnly,
                    }).then(() => {
                        this.requestCode();
                    });
                } catch (error) {
                    console.error(error);
                }
            },
            async requestCode() {
                try {
                    await this.$axios.$post('/api/v1/phone/send-code', {
                        phone: this.phoneNumbersOnly,
                    });
                    this.numberSent = true;
                    this.buttonCountdown = 119;
                } catch (error) {
                    console.error(error);
                }
            },
            async submitCode() {
                try {
                    await this.$axios.$post('/api/v1/phone/confirm', {
                        phone: this.phoneNumbersOnly,
                        token: this.code,
                    });
                    this.notification = await this.$notify.create({
                        message: 'Номер телефона подтверждён',
                        type: 'positive',
                    });
                    this.handleClose();
                } catch (error) {
                    console.error(error);
                    this.notification = await this.$notify.create({
                        message: error.response.data.errors?.token?.[0] || 'Не удалось подтвердить номер телефона',
                        type: 'negative',
                    });
                }
            },
            validate() {
                this.isValid = this.$refs.form.validate();
            },
            handlePhoneInputChange() {
                if (!this.numberSent) {
                    return;
                }

                this.phone = '';
                this.numberSent = false;
            },
        }
    };
</script>

<style lang="scss" scoped>
    .card {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 150px;
        border-radius: inherit;

        &-inner {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            padding: 73px 24px 24px;
            gap: 16px;
            flex-direction: column;
        }

        &__button-container {
            gap: 16px;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            width: 100%;
        }
    }

    .close-button {
        position: absolute;
        top: 24px;
        right: 24px;
        width: 32px;
        min-width: 32px !important;
        height: 32px !important;
        border: none;
        background-color: $base-100;

        &__icon {
            width: 16px;
            height: 16px;
        }
    }

    .heading {
        @extend %text-h4;

        text-align: center;
    }

    .text {
        @extend %text-body-1;

        text-align: center;

        @include respond-to(md) {
           & > br {
               display: none;
           }
        }
    }

    .button {
        flex-basis: 0;
        flex-grow: 1;
        min-width: 170px !important;
    }

    .w-full {
        width: 100%;
    }

    .link {
        @extend %text-body-1;

        color: $primary-500;
    }

</style>
