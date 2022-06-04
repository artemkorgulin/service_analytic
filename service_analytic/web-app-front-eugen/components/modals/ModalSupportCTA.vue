<template>
    <BaseDialog v-model="isShow" content-class="modal-confirm-content" width="560">
        <VCard :class="$style.wrapper">
            <div :class="$style.closeBtnWrapper">
                <VBtn
                    icon
                    outlined
                    color="base-900"
                    class="rounded"
                    :class="$style.closeBtn"
                    @click="handleClose"
                >
                    <SvgIcon name="outlined/close" :class="$style.closeIcon" />
                </VBtn>
            </div>
            <VFadeTransition mode="out-in" appear>
                <VProgressCircular
                    v-if="isLoading"
                    key="loading"
                    indeterminate
                    size="50"
                    color="accent"
                />
                <div v-else-if="!isSuccess" key="content" :class="$style.inner">
                    <VImg :class="$style.img" src="/images/marketplace-add.png" contain></VImg>
                    <div :class="$style.textWrapper">
                        <h2 :class="$style.heading">Остались вопросы?</h2>
                        <div :class="$style.subheading">
                            Напишите нам, мы ответим в течение 24 часов
                        </div>
                    </div>

                    <VTextarea
                        v-model="support"
                        :class="$style.textarea"
                        outlined
                        hide-details
                        name="message"
                        label="Сообщение..."
                    />
                    <div :class="$style.btnWrapper">
                        <VBtn
                            :class="$style.btn"
                            color="accent"
                            large
                            block
                            :disabled="support ? isValid : !isValid"
                            @click="handleConfirm"
                        >
                            Отправить
                        </VBtn>
                    </div>
                </div>
                <div v-if="isSuccess" :class="$style.inner">
                    <VImg :class="$style.img" src="/images/check.svg" contain></VImg>
                    <div :class="$style.textWrapper">
                        <h2 :class="$style.heading">Заявка отправлена!</h2>
                        <div :class="$style.subheading">
                            Мы постараемся связаться с вами
                            <br />
                            в течение 24 часов.
                        </div>
                    </div>
                    <div class="full-width-wrap">
                        <VBtn outlined large block @click="handleClose">Отлично</VBtn>
                    </div>
                </div>
            </VFadeTransition>
        </VCard>
    </BaseDialog>
</template>

<script>
    export default {
        name: 'ModalSupportCTA',
        data() {
            return {
                isShow: true,
                isLoading: false,
                support: null,
                isValid: false,
                isSuccess: false,
            };
        },
        methods: {
            async handleConfirm() {
                this.isLoading = true;
                try {
                    const response = await this.$axios.post('/api/v1/send-mail?type=need_help', {
                        data: this.support,
                    });
                    if (response) {
                        this.isLoading = false;
                        this.isSuccess = true;
                    }
                } catch (error) {
                    this.isLoading = false;
                    this.isSuccess = false;
                    return this?.$sentry?.captureException(error);
                }
            },
            handleClose() {
                this.isSuccess = false;
                this.isShow = false;
            },
        },
    };
</script>

<style lang="scss" module>
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

        margin-bottom: 16px;
        text-align: center;
    }

    .btnWrapper {
        gap: 16px;
        display: flex;
        width: 100%;
    }

    .closeBtnWrapper {
        position: absolute;
        top: 24px;
        right: 24px;

        .closeBtn {
            width: 32px;
            height: 32px;
        }
    }

    .closeBtn {
        width: 40px !important;
        height: 40px !important;
        border-color: $base-400 !important;

        &:before {
            background-color: transparent !important;
        }

        &:hover {
            color: $primary-500 !important;

            &:before,
            &:focus {
                background-color: transparent !important;
            }
        }
    }

    .closeIcon {
        cursor: pointer;
    }

    .subheading {
        margin-right: auto;
        margin-left: auto;
        text-align: center;
        font-size: 16px;
        line-height: 150%;
    }

    .textWrapper {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        flex: 1;
        width: 100%;
        flex-direction: column;
    }

    .img {
        height: 156px;
        max-height: 156px;
        margin-top: 4px;
    }

    .textarea {
        width: 100%;
        resize: none;
    }

    .loadingWrapper {
        @include centerer;
    }
</style>
