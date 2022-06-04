<template>
    <BaseDialog v-model="isShow" max-width="560">
        <VCard :class="$style.wrapper">
            <div class="loader">
                <svg
                    class="loader__svg"
                    width="200px"
                    height="200px"
                    viewBox="0 0 100 100"
                    preserveAspectRatio="xMidYMid"
                >
                    <circle cx="26" cy="58" r="4.5" fill="#ea5e7f">
                        <animate
                            attributeName="cy"
                            calcMode="spline"
                            keySplines="0 0.5 0.5 1;0.5 0 1 0.5;0.5 0.5 0.5 0.5"
                            repeatCount="indefinite"
                            values="58;42;58;58"
                            keyTimes="0;0.3;0.6;1"
                            dur="1s"
                            begin="-0.6s"
                        />
                    </circle>
                    <circle cx="42" cy="58" r="4.5" fill="#f5e75c">
                        <animate
                            attributeName="cy"
                            calcMode="spline"
                            keySplines="0 0.5 0.5 1;0.5 0 1 0.5;0.5 0.5 0.5 0.5"
                            repeatCount="indefinite"
                            values="58;42;58;58"
                            keyTimes="0;0.3;0.6;1"
                            dur="1s"
                            begin="-0.44999999999999996s"
                        />
                    </circle>
                    <circle cx="58" cy="58" r="4.5" fill="#a46fe8">
                        <animate
                            attributeName="cy"
                            calcMode="spline"
                            keySplines="0 0.5 0.5 1;0.5 0 1 0.5;0.5 0.5 0.5 0.5"
                            repeatCount="indefinite"
                            values="58;42;58;58"
                            keyTimes="0;0.3;0.6;1"
                            dur="1s"
                            begin="-0.3s"
                        />
                    </circle>
                    <circle cx="74" cy="58" r="4.5" fill="#4ccb86">
                        <animate
                            attributeName="cy"
                            calcMode="spline"
                            keySplines="0 0.5 0.5 1;0.5 0 1 0.5;0.5 0.5 0.5 0.5"
                            repeatCount="indefinite"
                            values="58;42;58;58"
                            keyTimes="0;0.3;0.6;1"
                            dur="1s"
                            begin="-0.15s"
                        />
                    </circle>
                </svg>
            </div>
            <span class="custom-dialog__text">Загружаем список товаров. Пожалуйста, подождите</span>
        </VCard>
    </BaseDialog>
</template>

<script>
    export default {
        name: 'ModalLoading',
        data() {
            return {
                isShow: true,
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
    .ModalLoading {
        //
    }

    .wrapper {
        position: relative;
        display: flex;
        align-items: center;
        padding: 24px;
        border-radius: inherit;
        flex-direction: column;
    }
</style>
