<template>
    <div
        :disabled="disabled"
        :class="[$style.SettingsMarketplace, selected ? $style.active : $style.notActive]"
    >
        <div
            v-ripple="!selected"
            :class="$style.marketplaceActiveArea"
            @click="handleChangeMarketplace"
        >
            <div :class="$style.marketplaceItemTop">
                <VImg contain :src="item.image" />
                <span>{{ item.name }}</span>
            </div>
            <!-- <span :class="$style.marketplaceSubheading">
                С другой стороны постоянный количественный рост и сфера нашей активности в
                значительной степени обуславливает создание направлений прогрессивного развития.
            </span> -->
        </div>
        <VExpandTransition mode="out-in">
            <component :is="componentIs" v-if="selected" />
        </VExpandTransition>
    </div>
</template>

<script>
    import { defineComponent } from '@nuxtjs/composition-api';

    export default defineComponent({
        name: 'SettingsMarketplace',
        props: {
            item: {
                type: Object,
                default: () => ({}),
            },
            disabled: {
                type: Boolean,
                default: false,
            },
            selected: {
                type: Boolean,
                default: false,
            },
        },
        data() {
            return {
                selectedPlatform: 0,
                isLoading: false,
            };
        },
        computed: {
            componentIs() {
                let value;
                switch (this.item.key) {
                    case 'ozon':
                        value = 'OzonSettings';
                        break;
                    case 'wildberries':
                        value = 'WildberriesSettings';
                        break;
                    default:
                        break;
                }
                return value;
            },
        },
        methods: {
            handleChangeMarketplace() {
                return this.$emit('change', this.item);
            },
        },
    });
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */

    .SettingsMarketplace {
        margin: 12px 0;
        border-radius: 8px;
        border: 1px solid $base-400;

        &:global([disabled]) {
            opacity: 0.65;
        }

        .marketplaceActiveArea {
            padding: 16px;
            transition: $primary-transition;
            transition-property: background-color;
            cursor: pointer;
            border-top-left-radius: inherit;
            border-top-right-radius: inherit;
            border-bottom-left-radius: inherit;
            border-bottom-right-radius: inherit;

            &:hover {
                background-color: $base-100;
            }
        }

        &.active {
            .marketplaceActiveArea {
                border-top-left-radius: inherit;
                border-top-right-radius: inherit;
                border-bottom-left-radius: unset;
                border-bottom-right-radius: unset;
                cursor: unset;

                &:hover {
                    background-color: unset;
                }
            }
        }
    }

    .marketplaceSubheading {
        margin-top: 8px;
        margin-bottom: 8px;
        font-size: 14px;
        line-height: 1.35;
        color: $base-900;
    }

    .marketplaceItemTop {
        display: flex;
        align-items: center;
        // margin-bottom: 8px;

        & :global(.v-image) {
            flex: none;
            width: 32px;
            height: 32px;
            margin-right: 14px;
        }

        span {
            font-weight: 600;
            font-size: 20px;
        }
    }
</style>
