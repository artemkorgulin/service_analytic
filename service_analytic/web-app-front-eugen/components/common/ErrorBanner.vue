<template>
    <div :class="[$style.ErrorBanner, small && $style.small]">
        <div :class="$style.ErrorBannerInner">
            <VImg
                :class="$style.image"
                src="/images/error.svg"
                :width="imgSize"
                :height="imgSize"
                :max-height="imgSize"
                max-width="100%"
                contain
            />
            <div :class="$style.statusCode">
                {{ statusCode }}
            </div>
            <div :class="$style.message">{{ displayMessage }}</div>
            <VBtn class="mt-16" color="accent" to="/">Главная</VBtn>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'ErrorBanner',
        props: {
            statusCode: {
                type: [String, Number],
                default: '',
            },
            message: {
                type: String,
                default: '',
            },
            actions: {
                type: Array,
                default: () => [],
            },
            small: Boolean,
            imgSize: {
                type: [String, Number],
                default: 'null',
            },
        },
        computed: {
            displayMessage() {
                if (this.message !== '') {
                    return this.message;
                }
                switch (this.statusCode) {
                    case 404:
                        return 'Этой страницы не существует';

                    case 403:
                        return 'Отказано в доступе';

                    default:
                        return this.message;
                }
            },
            isNuxtFetchError() {
                // TODO fix
                return !this.statusCode || String(this.statusCode) === '500';
            },
        },
    };
</script>

<style lang="scss" module>
    .ErrorBanner {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        background-color: $color-main-background;

        &.small {
            .statusCode {
                font-size: 36px;
            }

            .message {
                font-size: 18px;
            }
        }

        .ErrorBannerInner {
            display: flex;
            align-items: center;
            justify-content: center;
            max-width: 100%;
            margin: auto;
            flex-direction: column;
        }
    }

    .statusCode {
        text-align: center;
        font-size: 112px;
        line-height: 1.36;
        color: $base-900;
        font-weight: bold;
        user-select: none;
    }

    .message {
        text-align: center;
        font-size: 24px;
        line-height: 125%;
        color: $base-800;
        font-weight: 500;
        // user-select: none;

        @include respond-to(xs) {
            font-size: 18px;
        }
    }

    .btnWrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 72px;
        gap: 16px;
    }

    .image {
        width: 286px;
        margin-right: auto;
        margin-left: auto;
    }
</style>
