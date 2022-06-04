<template>
    <BaseDialog v-model="isShow" max-width="560" content-class="modal-products-request">
        <VCard :class="$style.wrapper">
            <div :class="$style.closeBtnWrapper">
                <VBtn :class="$style.closeBtn" icon outlined color="base-900" @click="handleClose">
                    <SvgIcon name="outlined/close" />
                </VBtn>
            </div>
            <VFadeTransition mode="out-in" appear>
                <VProgressCircular
                    v-if="isLoading"
                    key="loading"
                    :class="$style.loading"
                    indeterminate
                    size="75"
                    color="accent"
                />
                <div v-else-if="!isSuccess" key="common" :class="$style.inner">
                    <VImg :class="$style.img" src="/images/products-request.svg" contain></VImg>
                    <div :class="$style.textWrapper">
                        <h2 :class="$style.heading">Оптимизировать товары?</h2>
                        <div :class="$style.subheading">
                            Мы правильно заполним все характеристики товара
                            <br />
                            и поднимем ваши продажи.
                        </div>
                    </div>
                    <div class="full-width-wrap">
                        <VBtn color="accent" large block @click="handleConfirm">
                            Оставить заявку
                        </VBtn>
                    </div>
                </div>
                <div v-else key="success" :class="$style.inner">
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
        name: 'ModalProductsRequest',
        data() {
            return {
                isShow: true,
                isLoading: false,
                isSuccess: false,
            };
        },
        methods: {
            async handleConfirm() {
                this.isLoading = true;
                try {
                    const { success, errors } = await this.$axios.$get(
                        '/api/v1/send-mail?type=optimization'
                    );
                    if (!success) {
                        throw new Error(errors || 'err');
                    }
                    this.isLoading = false;
                    this.isSuccess = true;
                    this.$sendGtm('help_button');
                } catch (error) {
                    this.isLoading = false;
                    this.isSuccess = false;
                }
            },
            handleClose() {
                this.isShow = false;
            },
        },
    };
</script>
<style lang="scss" module>
    .wrapper {
        position: relative;
        min-width: 560px;
        min-height: 414px;
    }

    .loading {
        @include centerer;
    }

    .inner {
        display: flex;
        align-items: center;
        width: 100%;
        height: 100%;
        min-height: inherit;
        padding: 24px;
        flex-direction: column;
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

    .img {
        height: 156px;
        max-height: 156px;
        margin-top: 4px;
        margin-bottom: 16px;
    }

    .heading {
        @extend %text-h4;

        margin-bottom: 16px;
        text-align: center;
    }

    .subheading {
        max-width: 418px;
        margin-right: auto;
        margin-left: auto;
        text-align: center;
        font-size: 16px;
        line-height: 150%;
    }

    .textWrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 1;
        width: 100%;
        flex-direction: column;
    }
</style>
