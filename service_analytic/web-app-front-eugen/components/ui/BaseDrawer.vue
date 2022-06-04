<template>
    <transition
        appear
        :duration="500"
        name="drawer"
        @afterLeave="handleAfterLeave"
        @afterEnter="handleAfterEnter"
    >
        <div v-if="show" :class="[$style.Drawer, show && 'show']">
            <BaseOverlay
                id="drawer-overlay"
                key="overlay"
                ref="overlay"
                class="drawer-overlay"
                :class="$style.overlay"
                @click.self="handleClose"
                @contextmenu.stop.prevent
            />
            <div
                id="drawer-body"
                key="body"
                class="drawer-body"
                :style="{ width: width }"
                :class="$style.body"
            >
                <VBtn
                    id="drawer-close"
                    class="drawer-close"
                    :class="$style.closeButton"
                    fab
                    @click="handleClose"
                >
                    <VIcon color="base-900">$close</VIcon>
                </VBtn>
                <div id="drawer-body-inner" :class="$style.bodyInner">
                    <VFadeTransition mode="out-in" appear>
                        <div v-if="isLoading" key="loading" :class="$style.loadingWrapper">
                            <VProgressCircular indeterminate size="100" color="accent" />
                        </div>
                        <ErrorBanner
                            v-else-if="isError"
                            key="error"
                            :class="$style.errorBanner"
                            :message="errorMessage"
                        />
                        <div v-else key="content" class="drawer-content" :class="$style.content">
                            <slot />
                        </div>
                    </VFadeTransition>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
    import { mapState, mapMutations } from 'vuex';

    export default {
        name: 'BaseDrawer',
        props: {
            preventScroll: {
                type: Boolean,
                default: false,
            },
            fadeContent: {
                type: Boolean,
                default: true,
            },
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
        data() {
            return {
                show: true,
            };
        },
        computed: {
            ...mapState('modal', ['drawerAfterEnter']),
        },
        methods: {
            ...mapMutations('modal', ['setDrawerAfterEnter']),
            handleClose() {
                this.$store.commit('onBoarding/setOnboardActive', false);
                this.setDrawerAfterEnter(() => ({}));
                this.show = false;
            },
            handleAfterEnter() {
                setTimeout(() => this.drawerAfterEnter(), 300);
            },
            handleAfterLeave() {
                return this.$modal.close();
            },
        },
    };
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .Drawer {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        z-index: 400;
        width: 100%;
    }

    .errorBanner {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
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
        width: 48px !important;
        height: 48px !important;
        background-color: $white !important;
        color: $base-500 !important;
        transform: translateX(-100%);
        will-change: opacity;

        @include respond-to(md) {
            top: 18px;
            right: 24px;
            left: unset;
            width: 40px !important;
            height: 40px !important;
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
        transform: translate3d(0, 0, 0);
        transition: transform 1s $popup-in-bezier;

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

    .bodyInner {
        height: 100%;
    }
</style>
