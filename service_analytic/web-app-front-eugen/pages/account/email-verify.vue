<template>
    <div :class="$style.EmailVerifyPage">
        <div v-if="$fetchState.pending" key="loading" :class="$style.loadingWrapper">
            <VProgressCircular indeterminate size="100" color="accent" />
        </div>
        <ErrorBanner
            v-else-if="$fetchState.error"
            key="error"
            :message="$fetchState.error.message"
        />
        <div v-else key="content" :class="$style.pageWrapper">
            <VImg :class="$style.successImg" src="/images/auth-notify.svg" />
            <div :class="$style.successText">Почта была успешно подтверждена</div>
            <VBtn color="accent" block large to="/login">Войти</VBtn>
        </div>
    </div>
</template>
<router lang="js">
  {
    path: '/email-verify',
  }
</router>
<script>
    export default {
        name: 'EmailVerifyPage',
        layout: 'login',

        validate({ query }) {
            if (!query?.token) {
                return false;
            }
            return true;
        },
        transition: {
            name: 'fade',
            mode: 'out-in',
        },
        fetchOnServer: false,
        async fetch() {
            try {
                const response = await this.$axios.$post('/api/v1/sign-up-confirm', {
                    token: this.$route.query.token,
                });

                this.$sendGtm('success_reg');

                console.log(
                    '🚀 ~ file: email-confirm.vue ~ line 28 ~ asyncData ~ response',
                    response
                );
            } catch (error) {
                await this?.$sentry?.captureException(error);
                const message = error?.response?.data?.error?.message || 'Произошла ошибка';
                throw new Error(message);
            }
        },
        head() {
            return {
                htmlAttrs: {
                    class: 'static-rem',
                },
            };
        },
    };
</script>

<style lang="scss" module>
    .EmailVerifyPage {
        position: relative;
        display: flex;
        justify-content: center;
        height: 100%;
        padding-right: 24px;
        padding-left: 24px;
        flex-direction: column;
    }

    .loadingWrapper {
        @include centerer;
    }

    .pageWrapper {
        width: 100%;
        max-width: 368px;
        margin-right: auto;
        margin-left: auto;
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

    .pageInner {
        max-width: 368px;
        margin-right: auto;
        margin-left: auto;
    }

    .successImg {
        width: 96px;
        height: 96px;
        margin-right: auto;
        margin-bottom: 24px;
        margin-left: auto;
    }

    .successText {
        @extend %text-body-1;

        margin-bottom: 24px;
        text-align: center;
    }
</style>
