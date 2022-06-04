<template>
    <VDialog v-model="isShow" persistent content-class="se-confirm" @click:outside="closeModal">
        <div class="se-confirm-close-wrapper">
            <div class="se-confirm-close" @click="closeModal">
                <SvgIcon class="se-confirm-close-img" name="outlined/close" />
            </div>
        </div>
        <div class="se-confirm-content">{{ text }}</div>
        <div class="se-confirm-btns-wrapper">
            <VBtn class="se-confirm-btn" color="accent" depressed @click="btnApply">
                Применить
            </VBtn>
            <VBtn class="se-confirm-btn" outlined @click="btnCancel"> Отмена </VBtn>
        </div>
    </VDialog>
</template>

<script>
    export default {
        name: 'SeConfirm',
        props: {
            text: {
                type: String,
                default: 'Дефолтный текст',
            },
            apply: {
                type: Function,
                default: () => ({}),
            },
            isCloseAfterApply: {
                type: Boolean,
                default: true,
            },
            cancel: {
                type: Function,
                default: () => ({}),
            },
            isCloseAfterCancel: {
                type: Boolean,
                default: true,
            },
        },
        data() {
            return {
                isShow: true,
            };
        },
        methods: {
            btnApply() {
                this.apply();
                if (this.isCloseAfterApply) {
                    this.closeModal({ isOnly: true });
                }
            },
            btnCancel() {
                this.cancel();
                if (this.isCloseAfterCancel) {
                    this.closeModal({ isOnly: true });
                }
            },
            closeModal({ isOnly = false }) {
                if (!isOnly) {
                    this.cancel();
                }

                this.isShow = false;
            },
        },
    };
</script>

<style lang="scss" scoped>
    .se-confirm-close-wrapper {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 16px;
    }

    .se-confirm-close {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background-color: #f9f9f9;
        cursor: pointer;
        user-select: none;

        &-img {
            width: 18px;
            height: 18px;
        }
    }

    .se-confirm-content {
        text-align: center;
    }

    .se-confirm-btns-wrapper {
        display: flex;
        justify-content: space-between;
        gap: 24px;
        margin-top: 24px;
    }

    .se-confirm-btn {
        width: calc(50% - 12px);
    }
</style>

<style lang="scss">
    .se-confirm {
        position: relative;
        box-sizing: border-box;
        width: 560px;
        min-height: 140px;
        padding: 25px 24px;
        border-radius: 24px;
        background-color: #fff;
        box-shadow: 0 4px 32px rgba(0, 0, 0, 0.06);
    }
</style>
