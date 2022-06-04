<template>
    <div class="product-table-status__wrapper" :class="$style.MarketplaceTooltip">
        <VMenu offset-y open-on-hover top :nudge-bottom="-10">
            <template #activator="{ on, attrs }">
                <div slot="reference" v-bind="attrs" v-on="on">
                    <div :class="$style.IconWrapper">
                        <SvgIcon :class="$style.Icon" name="filled/question" />
                    </div>
                </div>
            </template>
            <VCard class="custom-popover">
                <span class="custom-popover__text" v-html="message"></span>
            </VCard>
        </VMenu>
    </div>
</template>

<script>
    export default {
        name: 'MarketplaceTooltip',
        props: {
            message: {
                require: false,
                type: [Object, String],
                default: '',
            },
        },
    };
</script>

<style lang="scss" module>
    .MarketplaceTooltip {
        width: 40px;
        height: 40px;

        .IconWrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            border: 1px solid $color-gray-light-100;
        }

        .Icon {
            display: block;
            width: 19px;
            height: 19px;
        }
    }
</style>
