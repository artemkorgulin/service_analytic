<template>
    <div
        class="product-table-status__wrapper"
        :class="[
            $style.Wrapper,
            { [$style.WrapperMarginLeft]: marginLeft },
            { [$style.WrapperMarginRight]: marginRight },
        ]"
    >
        <v-menu offset-y open-on-hover top :nudge-bottom="-10">
            <template #activator="{ on, attrs }">
                <div slot="reference" v-bind="attrs" v-on="on">
                    <div :class="$style.IconWrapper">
                        <SvgIcon
                            :class="$style.Icon"
                            name="filled/info"
                            :style="{ color: '#C8CFD9', width: '13px' }"
                        />
                    </div>
                </div>
            </template>
            <VCard class="custom-popover">
                <span class="custom-popover__text" v-html="message"></span>
            </VCard>
        </v-menu>
    </div>
</template>

<script>
    export default {
        name: 'IconTooltip',
        props: {
            message: {
                require: false,
                type: [Object, String],
                default: '',
            },
            marginLeft: {
                type: Boolean,
                default: false,
            },
            marginRight: {
                type: Boolean,
                default: false,
            },
            iconColor: {
                type: String,
                default: '',
            },
        },
    };
</script>

<style lang="scss" module>
    .Wrapper {
        width: 1rem;
        height: 1rem;

        &.WrapperMarginLeft {
            margin-left: 0.5rem;
        }

        &.WrapperMarginRight {
            margin-right: 0.5rem;
        }
    }

    .IconWrapper {
        display: block;
    }

    .Icon {
        display: block;
    }
</style>
