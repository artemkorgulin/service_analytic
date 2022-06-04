<template>
    <div :class="$style.NotificationBlock">
        <SvgIcon name="filled/check" :class="$style.IconCheck" />
        <VBtn :class="$style.buttonClose" icon>
            <SvgIcon name="outlined/close" />
        </VBtn>
        <VImg :class="$style.image" :src="img" contain alt="" />
        <div :class="$style.Information">
            <ProductsListUrl :url="link" :sku="link" />
            <div :class="$style.text">
                {{ text }}
            </div>
            <div :class="[$style.Status, getColorClass]">
                {{ getMessage }}
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'NotificationBlock',
        props: {
            text: {
                type: String,
                default: '',
            },
            img: {
                type: String,
                default: '',
            },
            link: {
                type: [String, Number],
                default: '',
            },
            checked: {
                type: Boolean,
                default: false,
            },
            status: {
                type: Number,
                required: true,
            },
        },
        data() {
            return {
                messages: {
                    success: 'Прошел модерацию',
                    fail: 'Не прошел модерацию',
                    pending: 'На модерации',
                },
            };
        },
        computed: {
            getMessage() {
                switch (this.status) {
                    case 1:
                        return this.messages.success;

                    case 2:
                        return this.messages.pending;

                    default:
                        return this.messages.fail;
                }
            },
            getColorClass() {
                switch (this.status) {
                    case 1:
                        return this.$style.Success;

                    case 2:
                        return this.$style.Pending;

                    default:
                        return this.$style.Fail;
                }
            },
        },
    };
</script>

<style lang="scss" module>
    .NotificationBlock {
        position: relative;
        display: flex;
        padding: 0.5rem;
        background-color: $color-main-background;
    }

    .IconCheck {
        position: absolute;
        top: 2px;
        left: 2px;
        z-index: 1;
        color: $color-green-secondary;

        &:global(.icon.sprite-filled) {
            width: 1.5rem;
            height: 1.5rem;
        }
    }

    .buttonClose {
        position: absolute;
        top: 2px;
        right: 2px;

        &:global(.v-btn--icon.v-size--default) {
            width: 1.5rem;
            height: 1.5rem;
        }
    }

    .image {
        max-width: 2.5rem;
        max-height: 2.5rem;
        margin-right: 0.8rem;
    }

    .Information {
        padding-right: 1rem;
        font-size: 0.875rem;
        line-height: 1.2;
        font-weight: 500;
        color: $color-gray-dark-800;
    }

    .Status {
        padding-top: 2px;
    }

    .Success {
        color: $color-green-secondary;
    }

    .Pending {
        color: $color-gray-dark;
    }

    .Fail {
        color: $color-pink-dark;
    }
</style>
