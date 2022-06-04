<template>
    <VApp ref="app" :style="{ minHeight: height }">
        <VMain :class="$style.main">
            <user-helper></user-helper>
            <on-boarding ref="onBoarding" :elements="testElements" ></on-boarding>
            <div :class="$style.wrapper">
                <div :class="$style.wrapperLeft">
                    <Nuxt />
                    <se-counter></se-counter>
                    <SeFeetbackInLogin></SeFeetbackInLogin>
                </div>
                <div :class="$style.wrapperRight">
                    <transition name="fade">
                        <img
                            :key="$route.name"
                            :class="$style.loginBcg"
                            alt="login-bcg"
                            class="login-bcg"
                            :src="getImage"
                        />
                    </transition>
                </div>
            </div>
        </VMain>
    </VApp>
</template>

<script>
    import UserHelper from '~/components/common/UserHelper.vue';
    import SeFeetbackInLogin from '~/components/common/SeFeetbackInLogin.vue';
    import OnBoarding from '~/components/common/OnBoarding.vue';

    export default {
        name: 'LoginLayout',
        components: { UserHelper, SeFeetbackInLogin, OnBoarding },
        transition: {
            name: 'fade',
            mode: 'out-in',
        },
        computed: {
            height() {
                return process.server ? '100vh' : `${this.$nuxt.$vuetify.breakpoint.height}px`;
            },
            getImage() {
                switch (this.$route.name) {
                    case 'account-signup':
                    case 'account-email-verify':
                        return '/images/signup-img.svg';
                    case 'account-forgot':
                    case 'account-password-recover':
                        return '/images/forgot-img.svg';
                    default:
                        return '/images/login-img.svg';
                }
            },
        },
    };
</script>
<style lang="scss" module>
    .wrapper {
        display: flex;
        height: 100%;
    }

    .main {
        height: 100%;
    }

    .wrapperLeft {
        flex-grow: 1;
        flex-shrink: 0;
        // display: flex;
        // align-items: center;
        // justify-content: center;
        max-width: 34%;
        background: $color-light-background;
        flex-basis: 34%;

        @include respond-to(md) {
            max-width: 100%;
            background: $color-light-background;
            flex-basis: 100%;
        }
    }

    .innerLeft {
        width: 100%;
        // height: 100%;
    }

    .wrapperRight {
        position: relative;
        display: flex;
        flex: 1;

        @include respond-to(md) {
            display: none;
        }
    }

    .logo {
        width: 174px;
        height: 135px;
        margin-right: auto;
        margin-bottom: 56px;
        margin-left: auto;

        @include respond-to(md) {
            margin-bottom: 24px;
        }
    }

    .loginBcg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
</style>
