<template>
    <div :class="$style.ProductListIcon">
        <VMenu offset-y open-on-hover top :nudge-bottom="-10">
            <template #activator="{ on, attrs }">
                <div :class="[$style.Status, getColorClass]" v-bind="attrs" v-on="on">
                    <SvgIcon :name="getIcon" />
                </div>
            </template>
            <VCard class="custom-popover" max-width="270">
                <span class="custom-popover__text">{{ getMessage }}</span>
                <!-- $options.filters.formatDateTime(change.timestamp,'$y.$m.$d - $H:$I') -->
                <!-- {{ time | dateFormat }} -->
                <span class="custom-popover__text custom-popover__text--gray">
                    {{ $options.filters.formatDateTime(time, '$y.$m.$d') }}
                </span>
            </VCard>
        </VMenu>
    </div>
</template>

<script>
    import productsMixin from '~mixins/products.mixin';
    export default {
        name: 'ProductsListIconCheck',
        mixins: [productsMixin],
        props: {
            time: {
                type: null,
                default: null,
            },
            status: {
                type: null,
                required: true,
            },
        },
        data() {
            return {
                messages: {
                    success: 'Товар прошел проверку!',
                    fail: 'Товар не прошел проверку',
                    pending: 'Товар на модерации',
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
            getIcon() {
                switch (this.status) {
                    case 1:
                        return 'filled/check';

                    case 2:
                        return 'filled/time';

                    default:
                        return 'filled/close';
                }
            },
        },
    };
</script>

<style lang="scss" module>
    .ProductListIcon {
        //
    }

    .Status {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        border-radius: 0.5rem;
        font-size: 1rem;
        color: $color-gray-light-100;
        transition: $transition-fast;
    }

    .Success {
        background-color: rgba(32, 194, 116, 0.08);
        color: $color-green-secondary;
    }

    .Pending {
        background-color: rgba(200, 207, 217, 0.08);
        color: $color-gray-dark;
    }

    .Fail {
        background-color: rgba(255, 11, 153, 0.08);
        color: $color-pink-dark;
    }
</style>
