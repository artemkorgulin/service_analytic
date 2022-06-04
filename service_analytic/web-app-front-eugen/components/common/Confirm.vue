<template>
    <BaseDialog v-model="isShow" content-class="modal-confirm-content" width="560">
        <VCard :class="$style.wrapper">
            <div v-if="action" key="content" :class="$style.inner">
                <h2 :class="$style.heading">{{ action.title }}</h2>
                <span v-if="action.text" class="base-txt">{{ action.text }}</span>

                <div :class="$style.btnWrapper">
                    <VBtn :class="action.btn.confirm.cls" @click="confirm">
                        {{ action.btn.confirm.text }}
                    </VBtn>
                    <VBtn :class="action.btn.cancel.cls" outlined @click="close">
                        {{ action.btn.cancel.text }}
                    </VBtn>
                </div>
            </div>
        </VCard>
    </BaseDialog>
</template>

<script>
    export default {
        name: 'Confirm',
        data() {
            return {
                isShow: false,
                action: null,
            };
        },
        methods: {
            show(action) {
                this.action = action;
                this.isShow = true;
            },
            confirm() {
                this.isShow = false;
                this.action.confirm();
            },
            close() {
                this.isShow = false;

                if (this.action.cancel) {
                    this.action.cancel();
                }
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

        text-align: center;
    }

    .btnWrapper {
        gap: 16px;
        display: flex;
        justify-content: center;
        width: 100%;
    }
</style>
