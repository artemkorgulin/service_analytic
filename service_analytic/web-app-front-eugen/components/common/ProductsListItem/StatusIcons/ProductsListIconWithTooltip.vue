<template>
    <div class="icon-tooltip">
        <VMenu offset-y open-on-hover top :nudge-bottom="-10">
            <template #activator="{ on, attrs }">
                <div class="icon-tooltip__status" :class="getColorClass" v-bind="attrs" v-on="on">
                    <SvgIcon class="icon-tooltip__icon" :name="getIcon" />
                </div>
            </template>
            <VCard class="custom-popover" max-width="270">
                <span class="custom-popover__text">{{ getMessage }}</span>
            </VCard>
        </VMenu>
    </div>
</template>

<script>
    import productsMixin from '~mixins/products.mixin';
    export default {
        name: 'ProductsListIconWithTooltip',
        mixins: [productsMixin],
        props: {
            status: {
                type: null,
                required: true,
            },
            icon: {
                type: String,
                required: true,
            },
            message: {
                type: String,
                required: true,
            },
        },
        computed: {
            getColorClass() {
                switch (this.status) {
                    case 'success':
                        return 'success';

                    case 'pending':
                        return 'pending';

                    default:
                        return 'fail';
                }
            },
            getIcon() {
                return this.icon || 'outlined/tick';
            },
            getMessage() {
                return this.message || '';
            },
        },
    };
</script>

<style lang="scss" scoped>
    .icon-tooltip {
        &__status {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border-radius: 0.5rem;
            font-size: 1rem;
            color: $color-gray-light-100;
            transition: $transition-fast;

            &.success {
                background-color: rgba(32, 194, 116, 0.08);
                color: $color-green-secondary;
            }

            &.pending {
                background-color: rgba(200, 207, 217, 0.08);
                color: $color-gray-dark;
            }

            &.fail {
                background-color: rgba(255, 11, 153, 0.08);
                color: $color-pink-dark;
            }
        }

        &__icon {
            width: 16px;
            height: 16px;
        }
    }
</style>
