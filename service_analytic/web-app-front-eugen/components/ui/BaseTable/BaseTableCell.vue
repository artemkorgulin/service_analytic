<template>
    <div :class="classes" :style="styles">
        <div v-if="isMobileVariant && heading" :class="$style.key">
            {{ heading }}
        </div>
        <div v-if="!$slots.default" :class="$style.val">
            {{ valueText }}
        </div>
        <slot v-else></slot>
    </div>
</template>

<script>
    import { isSet } from '~utils/helpers';

    export default {
        name: 'BaseTableCell',
        props: {
            value: {
                type: [Boolean, String],
                default: '',
            },
            heading: {
                type: String,
                default: '',
            },
            innerText: {
                type: [String, Number],
                default: '',
            },
            valueTransfromFunction: {
                type: Function,
                default: undefined,
            },
            width: {
                type: [String, Number],
                default: undefined,
            },
            isItem: {
                type: Boolean,
                default: false,
            },
            isFlex: {
                type: Boolean,
                default: false,
            },
            isMobileVariant: {
                type: Boolean,
                default: false,
            },
            isActive: {
                type: Boolean,
                default: false,
            },
            isDisabled: {
                type: Boolean,
                default: false,
            },
            isBorderBottom: {
                type: Boolean,
                default: false,
            },
            isBorderRight: {
                type: Boolean,
                default: false,
            },
        },
        computed: {
            classes() {
                return [
                    this.$style.BaseTableCell,
                    {
                        [this.$style.isActive]: this.isActive && !this.isDisabled,
                        [this.$style.isDisabled]: this.isDisabled,
                        [this.$style.isFlex]: this.isFlex,
                        [this.$style.isTable]: !this.isFlex,
                        [this.$style.isSelected]: Boolean(this.value),
                        [this.$style.isMobileVariant]: this.isMobileVariant,
                        [this.$style.isBorderBottom]: this.isBorderBottom,
                        [this.$style.isBorderRight]: this.isBorderRight,
                    },
                ];
            },
            styles() {
                if (!isSet(this.width)) {
                    return {
                        flex: '1 1 auto',
                    };
                }
                return {
                    maxWidth: this.width,
                    flexBasis: this.width,
                };
            },
            valueText() {
                if (
                    this.valueTransfromFunction &&
                    typeof this.valueTransfromFunction === 'function'
                ) {
                    return this.valueTransfromFunction(this.innerText);
                }
                return this.innerText;
            },
        },
    };
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .BaseTableCell {
        overflow: hidden;
        padding-top: 1.5rem;
        padding-right: 1.6rem;
        padding-bottom: 1.5rem;
        padding-left: 1.6rem;
        font-size: 1.4rem;

        @include respond-to(md) {
            padding-top: 8px;
            padding-right: 8px;
            padding-bottom: 8px;
            padding-left: 8px;
        }

        &.isSelected {
            background-color: rgba($accent, 0.12);
            transition: $primary-transition;
        }

        .val {
            @extend %text-body-1;
            @extend %ellipsis;

            font-size: 1.6rem;

            @include respond-to(md) {
                font-size: 14px;
            }
        }

        &.isMobileVariant {
            @include respond-to(md) {
                display: flex !important;
                justify-content: space-between;
                width: 100% !important;
                max-width: 100% !important;
                padding-top: 16px;
                padding-bottom: 16px;
                flex-basis: 100% !important;

                .val {
                    padding-left: 16px;
                }
            }

            .key {
                display: none;

                @include respond-to(md) {
                    display: block;
                }
            }
        }

        &.isFlex {
            display: flex;
            align-items: center;
        }

        &.isTable {
            display: table-cell;
            vertical-align: middle;
        }

        &.isActive {
            transition: $primary-transition;
            transition-property: background-color;
            cursor: pointer;

            &:hover {
                background-color: $base-400;
            }
        }

        &.isDisabled {
            user-select: none;
            pointer-events: none;
            opacity: 0.7;
            transition: $primary-transition;
        }

        &.isBorderBottom {
            @include borderLine();

            position: relative;
        }

        &.isBorderRight {
            &:not(&:last-child) {
                @include borderLine(false, true, false);

                position: relative;
            }
        }
    }
</style>
