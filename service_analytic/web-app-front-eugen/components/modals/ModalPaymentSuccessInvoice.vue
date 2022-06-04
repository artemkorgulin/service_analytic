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
                    <VImg
                        :class="$style.StatusImage"
                        src="/images/check.svg"
                        contain
                        alt="успешно"
                    />
                    <h2 :class="$style.heading">Счёт сгенерирован</h2>
                    <!--                   <p :class="$style.text">Скачать счёт</p>-->
                    <div :class="$style.btnWrapper">
                        <VBtn :class="$style.btn" outlined @click="getInvoice">Скачать счёт</VBtn>
                    </div>
                </div>
            </VFadeTransition>
        </VCard>
    </BaseDialog>
</template>

<script>
    import { errorHandler } from '~utils/response.utils';
    export default {
        name: 'ModalPaymentSuccessInvoice',
        props: {
            requestData: {
                required: false,
                type: Object,
                default: () => ({}),
            },
            tariffCalculation: {
                required: false,
                type: Object,
                default: () => ({}),
            },
        },
        data() {
            return {
                isShow: true,
                isLoading: false,
            };
        },
        computed: {
            requestDataForPdf() {
                const obj = this.requestData;
                const tariffId = obj.tariff_id;
                obj.tariff_id = [tariffId];
                obj.period = obj.duration;
                return obj;
            },
        },
        methods: {
            async handleConfirm() {
                this.isLoading = true;
                try {
                    this.isLoading = false;
                    this.isShow = false;
                } catch (error) {
                    this.isLoading = false;
                    this.isShow = false;
                    return this?.$sentry?.captureException(error);
                }
            },
            handleClose() {
                this.isShow = false;
            },
            getInvoice() {
                this.$axios({
                    method: 'POST',
                    url: '/api/v1/pdf/download',
                    responseType: 'blob',
                    params: { ...this.requestDataForPdf, ...this.tariffCalculation },
                })
                    .then(response => {
                        const url = window.URL.createObjectURL(new Blob([response.data]));
                        const link = document.createElement('a');
                        link.href = url;
                        link.setAttribute('download', 'invoice.pdf');
                        document.body.appendChild(link);
                        link.click();
                    })
                    .catch(error => {
                        errorHandler(error, this.$notify);
                    })
                    .finally(() => {
                        this.handleClose();
                    });
            },
            handlePayCard() {
                this.handleClose();
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
