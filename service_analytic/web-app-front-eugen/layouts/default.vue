<template>
    <VApp ref="app" :class="$style.app" :style="{ minHeight }">
        <on-boarding ref="onBoarding" :elements="testElements"></on-boarding>

        <VOverlay :value="isAccountSettingsMenuExpanded" :z-index="4"></VOverlay>
        <VFadeTransition>
            <BaseOverlay
                v-if="isOverlay"
                is-prevent-scroll
                @click.self="handleClickOutside"
                @contextmenu.stop.prevent
            />
        </VFadeTransition>
        <user-helper></user-helper>

        <TheMenu
            ref="menu"
            :is-mobile="isMobile"
            :class="[$style.menu, menuExpanded && $style.expanded]"
            @change="handleMenuChange"
        />
        <LazyTheAccountSettings v-if="isMounted && isAccountSettingsMenuExpanded" />
        <LazyTheMarketplaceSettingsMenu
            v-if="isMounted && isAccountSettingsMenuExpanded && isMarketplaceSettingsMenuExpanded"
        />
        <VNavigationDrawer v-model="notifDrawerState" width="600" temporary fixed right>
            <Notification></Notification>
        </VNavigationDrawer>

        <TheHeader :class="$style.header" :value="menuExpanded" @openMenu="handleOpenMenu" />
        <VMain ref="main" :class="$style.main" :style="vMainSpecialStyle">
            <Nuxt />
        </VMain>
        <TheModal />
        <ModalTheMarketplaceAdd />
        <TheContextMenu />
    </VApp>
</template>

<script>
    /* eslint-disable no-unused-expressions*/
    import { mapActions, mapState } from 'vuex';
    import OnBoarding from '~/components/common/OnBoarding.vue';

    export default {
        name: 'DefaultLayout',
        component: [OnBoarding],
        async middleware({ $auth, redirect, $cookies }) {
            const token = await $cookies.get('auth._token.custom');
            console.log('🚀 ~ file: default.vue ~ line 35 ~ middleware ~ token', Boolean(token));
            console.log('🚀 ~ file: brand.vue ~ line 36 ~ middleware ~ $auth', $auth.loggedIn);
            if (!token) {
                await $auth.reset();
                return redirect('/login');
            }
        },
        data() {
            return {
                isMounted: false,
                testElements: undefined,
            };
        },
        computed: {
            ...mapState('notification', ['notifWindow']),
            ...mapState('user', {
                menuExpanded: 'isMenuExpanded',
                isAccountSettingsMenuExpanded: 'isAccountSettingsMenuExpanded',
                isMarketplaceSettingsMenuExpanded: 'isMarketplaceSettingsMenuExpanded',
            }),
            notifDrawerState: {
                get() {
                    return this.notifWindow;
                },
                set(value) {
                    this.$store.commit('notification/SET_FIELD', { field: 'notifWindow', value });

                    if (!value) {
                        this.$store.commit('notification/SET_FIELD', {
                            field: 'notifList',
                            value: [],
                        });
                    }
                },
            },
            minHeight() {
                return process.server ? '100vh' : `${this.$nuxt.$vuetify.breakpoint.height}px`;
            },
            isOverlay() {
                return Boolean(this.menuExpanded) && Boolean(this.isMobile);
            },
            isMobile() {
                if (!this.$nuxt.$vuetify.breakpoint.width) {
                    return !this.$nuxt.$device.isDesktop;
                }
                return this.$nuxt.$vuetify.breakpoint.mobile;
            },
            vMainSpecialStyle() {
                if (this.$route.path === '/welcome') {
                    return {
                        backgroundColor: '#e5e5e5 !important',
                    };
                }
                return false;
            },
        },
        watch: {
            minHeight: {
                handler(val) {
                    this.$refs?.app?.$el && (this.$refs.app.$el.style.minHeight = val);
                    if (process.client && document?.documentElement) {
                        document.documentElement.style.setProperty('--app-height', val);
                    }
                },
                immediate: true,
            },
            '$route.name'() {
                this.$notify.closeAll();
            },
        },

        created() {
            const {
                userPlan: { sku, typePayment },
            } = this.$store.getters;

            const factPaymentFixed = JSON.parse(localStorage.getItem('factPaymentFixed'));

            if (sku > 3 && !factPaymentFixed) {
                this.$sendGtm(typePayment === 'По карте' ? 's_card_pay' : 's_cashless_pay');
                localStorage.setItem('factPaymentFixed', true);
            }

            this.$store.dispatch('initMenuState');
        },
        mounted() {
            if (window.innerWidth < 1024) {
                this.$store.dispatch('setMenuState', { val: false });
            }
            this.isMounted = true;
            this.handleUserIdGTM(this.$auth.loggedIn);
        },
        methods: {
            ...mapActions('user', {
                setMenuState: 'setMenuState',
            }),
            startTestOnBoarding() {
                let dbBlocks = Array.from(document.getElementsByClassName('dashboard-card'));

                dbBlocks = dbBlocks.slice(0, 4);

                const positions = ['left', 'right', 'left', 'bottom'];
                this.testElements = dbBlocks.reduce((acc, item, index) => {
                    acc.push({
                        el: item,
                        pos: positions[index],
                        intro: 'Hello World',
                    });
                    return acc;
                }, []);

                // console.log(this.testElements);
                this.$refs.onBoarding.start(this.testElements);
            },
            handleClickOutside() {
                return this.setMenuState({ val: false });
            },
            handleOpenMenu() {
                return this.setMenuState({ val: true });
            },
            handleMenuChange(val) {
                return this.setMenuState({ val });
            },
            handleUserIdGTM(loggedIn) {
                if (loggedIn) {
                    const userId = this.$auth.user.id;
                    this.$sendGtm({ event: 'userId', userId });
                }
            },
        },
    };
</script>
<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .menu {
        &.expanded {
            ~ .main {
                padding-left: var(--menu-expanded-width) !important;

                @include respond-to(md) {
                    padding-left: var(--menu-width) !important;
                }
            }

            ~ .header {
                left: var(--menu-expanded-width);

                @include respond-to(md) {
                    left: var(--menu-width) !important;
                }
            }
        }
    }

    .main {
        width: 100%;
        height: 100%;
        min-height: inherit;
        padding-top: var(--header-h) !important;
        padding-bottom: 126px !important;
        padding-left: var(--menu-width) !important;
        transition: $primary-transition;
        transition-duration: 0.35s;
        transition-property: padding-left;
        will-change: padding-left;

        @include respond-to(md) {
            padding-left: var(--menu-width) !important;
        }
    }

    .header {
        position: fixed;
        top: 0;
        right: 0;
        left: var(--menu-width);
        z-index: 3;
        transition: $primary-transition;
        transition-duration: 0.35s;
        transition-property: left;
        will-change: left;
    }
</style>
