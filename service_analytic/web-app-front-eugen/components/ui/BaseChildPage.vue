<template>
    <div :class="$style.PageWrapper">
        <BaseOverlay
            id="side-page-overlay"
            class="drawer-overlay"
            @click.self="handleClose"
            @contextmenu.stop.prevent
        />
        <div id="side-page-body" class="drawer-body" :class="$style.body" :style="{ width: width }">
            <VBtn
                id="side-page-close"
                class="drawer-close"
                :class="$style.closeButton"
                fab
                small
                @click="handleClose"
            >
                <VIcon color="base-900">$close</VIcon>
            </VBtn>
            <div :class="$style.wrapper" data-scroll-area>
                <VFadeTransition mode="out-in" appear>
                    <div v-if="isLoading" key="loading" :class="$style.loadingWrapper">
                        <VProgressCircular indeterminate size="100" color="accent" />
                    </div>
                    <ErrorBanner v-else-if="isError" key="error" :message="errorMessage" />
                    <div v-else key="content" :class="$style.content">
                        <slot />
                    </div>
                </VFadeTransition>
            </div>
        </div>
    </div>
</template>

<script>
    import preventScroll from '~mixins/prevent-scroll';

    export default {
        name: 'BaseChildPage',
        mixins: [preventScroll],
        props: {
            isLoading: {
                type: Boolean,
                default: false,
            },
            isError: {
                type: Boolean,
                default: false,
            },
            errorMessage: {
                type: String,
                default: '',
            },
            width: {
                type: String,
                default: '90%',
            },
        },
        mounted() {
            return this.__preventScroll(true);
        },
        beforeDestroy() {
            return this.__preventScroll(false);
        },
        methods: {
            handleClose() {
                this.$emit('close');
            },
        },
    };
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .PageWrapper {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        z-index: 400;
        width: 100%;
    }

    .content {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .loadingWrapper {
        text-align: center;

        @include centerer;
    }

    .closeButton {
        position: absolute;
        top: 24px;
        left: -24px;
        z-index: 1;
        background-color: $white !important;
        color: $base-500 !important;
        transform: translateX(-100%);
        will-change: opacity;

        @include respond-to(md) {
            top: 18px;
            right: 24px;
            left: unset;
            background-color: $base-100 !important;
            transform: none;
        }
    }

    .body {
        position: fixed;
        top: 0;
        right: 0;
        z-index: 400;
        display: flex;
        height: 100%;
        background-color: $white;
        color: $base-800;
        flex-direction: column;
        will-change: transform;

        @include respond-to(md) {
            width: 100% !important;
        }
    }

    .wrapper {
        display: flex;
        height: 100%;
        padding: 24px;
        flex-direction: column;

        @include respond-to(sm) {
            padding: 16px;
        }
    }
</style>
