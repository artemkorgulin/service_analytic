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
                    <h2 :class="$style.heading">Выйти из аккаунта?</h2>
                    <div :class="$style.btnWrapper">
                        <VBtn
                            :class="[$style.btn, $style.confirm]"
                            color="#FF3981"
                            dark
                            @click="handleConfirm"
                        >
                            Выйти
                        </VBtn>
                        <VBtn :class="[$style.btn, $style.cansel]" outlined @click="handleClose">
                            Отмена
                        </VBtn>
                    </div>
                </div>
            </VFadeTransition>
        </VCard>
    </BaseDialog>
</template>

<script>
    export default {
        name: 'ModalConfirmExit',
        data() {
            return {
                isShow: true,
                isLoading: false,
            };
        },
        methods: {
            async handleConfirm() {
                this.isLoading = true;
                try {
                    await this.$auth.logout();
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
        },
    };
</script>

<style lang="scss" module>
    // :global(.modal-confirm-content) {
    //     border-radius: 24px;
    // }

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

        text-align: center;
    }

    .btnWrapper {
        gap: 16px;
        display: flex;
        width: 100%;
    }

    .btn {
        flex: 1;

        &.confirm {
            background-color: $error;
            box-shadow: 0 4px 16px rgba(255, 69, 181, 0.16);
        }
    }

    .loadingWrapper {
        @include centerer;
    }
</style>
