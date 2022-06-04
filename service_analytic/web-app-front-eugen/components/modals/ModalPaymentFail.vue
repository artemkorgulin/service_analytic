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
                    <VBtn :class="$style.closeButton" fab small outlined @click="handleClose">
                        <SvgIcon name="outlined/close" />
                    </VBtn>
                    <VImg :class="$style.StatusImage" src="/images/fail.svg" contain alt="ошибка" />
                    <h2 :class="$style.heading">Ошибка оплаты</h2>
                </div>
            </VFadeTransition>
        </VCard>
    </BaseDialog>
</template>

<script>
    export default {
        name: 'ModalPaymentFail',
        props: {
            text: {
                required: false,
                type: String,
                default: null,
            },
        },
        data() {
            return {
                isShow: true,
                isLoading: false,
            };
        },
        methods: {
            handleClose() {
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

    .closeButton {
        position: absolute;
        top: var(--gap);
        right: var(--gap);
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

    .StatusImage {
        max-width: size(124);
    }

    .heading {
        @extend %text-h4;

        text-align: center;
    }

    .text {
        @extend %text-body-1;

        text-align: center;
    }

    .btnWrapper {
        gap: 16px;
        display: flex;
        justify-content: center;
        width: 100%;
    }

    .textarea {
        width: 100%;
    }

    .loadingWrapper {
        @include centerer;
    }
</style>
