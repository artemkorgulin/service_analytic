<template>
    <div :class="classes" :style="styles">
        <template v-if="!$slots.default">
            {{ innerText }}
        </template>
        <slot v-else></slot>
    </div>
</template>

<script>
    import { isSet } from '~utils/helpers';
    export default {
        name: 'BaseTableHeadingCell',
        props: {
            innerText: {
                type: String,
                default: '',
            },
            width: {
                type: [String, Number],
                default: undefined,
            },
            isFlex: {
                type: Boolean,
                default: false,
            },
            isSticky: {
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
            isBorderLeft: {
                type: Boolean,
                default: false,
            },
        },
        computed: {
            classes() {
                return [
                    this.$style.BaseTableHeadingCell,
                    {
                        [this.$style.isSticky]: this.isSticky,
                        [this.$style.isFlex]: this.isFlex,
                        [this.$style.isTable]: !this.isFlex,
                        [this.$style.isBorderBottom]: this.isBorderBottom,
                        [this.$style.isBorderRight]: this.isBorderRight,
                        [this.$style.isBorderLeft]: this.isBorderLeft,
                    },
                ];
            },
            styles() {
                if (!isSet(this.width)) {
                    return {
                        flex: '0 1 auto',
                    };
                }
                return {
                    maxWidth: this.width,
                    flexBasis: this.width,
                };
            },
        },
    };
</script>

<style lang="scss" module>
    .BaseTableHeadingCell {
        @extend %text-body-3;

        position: relative;
        padding: 1.5rem 1.6rem;
        text-align: left;
        font-size: 1.4rem;
        color: $base-800;

        &:last-child {
            &:before {
                display: none;
            }
        }

        &.isSticky {
            position: sticky;
            top: 0;
            z-index: 1;
            background-color: $white;
        }

        &.isFlex {
            display: flex;
            align-items: center;
        }

        &.isTable {
            display: table-cell;
            vertical-align: middle;
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

        &.isBorderLeft {
            // &:not(&:last-child) {
            @include borderLine(false, false, false);

            position: relative;
            // }
        }

        @include respond-to(md) {
            min-height: 40px;
            padding: 11px 8px;
        }
    }
</style>
