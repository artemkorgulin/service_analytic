<template>
    <component
        :is="tag"
        v-ripple="(isRadio && !value) || isEvents"
        :class="classes"
        :style="styles"
        v-bind="propsToComponent"
        v-on="eventsComputed"
    >
        <BaseTableCell v-if="isSelect" :class="$style.checkboxWrapper">
            <div :class="$style.checkboxInner">
                <BaseCheckbox :class="$style.checkbox" :value="value" />
            </div>
        </BaseTableCell>
        <BaseRadio v-if="isRadio" :class="$style.radio" color="accent" :value="value" />
        <slot name="prepend"></slot>
        <slot></slot>
        <BaseTableCell
            v-if="!$slots.append && isAction"
            v-ripple
            :class="$style.deleteWrapper"
            @click.native="$emit('appendClick')"
        >
            <SvgIcon :class="$style.action" :name="actionIcon" />
        </BaseTableCell>
        <slot v-else name="append"></slot>
    </component>
</template>

<script>
    import { isSet } from '~utils/helpers';

    export default {
        name: 'BaseTableRow',
        reactiveProvide: {
            name: 'row',
            include: ['isRadio', 'isSelect', 'isFlex', 'value'],
        },
        props: {
            value: {
                type: [Boolean, String],
                default: '',
            },
            isRadio: {
                type: Boolean,
                default: false,
            },
            isSelect: {
                type: Boolean,
                default: false,
            },
            isFlex: {
                type: Boolean,
                default: false,
            },
            isAction: {
                type: Boolean,
                default: false,
            },
            actionIcon: {
                type: String,
                default: '',
            },
            tag: {
                type: String,
                default: 'div',
            },
            propsToComponent: {
                type: Object,
                default: () => ({}),
            },
        },
        computed: {
            eventsComputed() {
                const events = {};
                if (this.isSelect) {
                    events.click = this.handleSelect;
                }
                return {
                    ...events,
                };
            },
            isEvents() {
                return Boolean(Object.keys(this.eventsComputed).length);
            },
            classes() {
                return [
                    this.$style.BaseTableRow,
                    {
                        [this.$style.isRadio]: this.isRadio,
                        [this.$style.isSelect]: this.isSelect,
                        [this.$style.isFlex]: this.isFlex,
                        [this.$style.isActive]: this.value,
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
            isActive() {
                // Boolean(this.isRadio) &&
                return Boolean(this.value);
            },
        },
        methods: {
            // handleClick() {
            //     this.$emit('change');
            // },
            handleSelect(payload) {
                this.$emit('select', payload);
            },
        },
    };
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .BaseTableRow {
        position: relative;
        display: table-row;
        transition: $primary-transition;
        transition-property: background-color;

        &.isFlex {
            display: flex;
            width: 100%;
        }

        &.isActive {
            background-color: $base-100;
        }

        &.isRadio {
            overflow: hidden;

            &.isActive {
                background-color: rgba($accent, 0.1);
            }

            .radio {
                + div {
                    padding-left: 0 !important;
                }
            }
        }

        &.isSelect {
            &.isActive {
                background-color: $base-100;
            }

            .checkboxWrapper {
                + div {
                    padding-left: 0 !important;
                }
            }
        }
    }

    .checkbox {
        width: 16px;
        height: 16px;
    }

    .checkboxInner {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
    }

    .radio {
        min-width: 5.6rem;

        @include respond-to(md) {
            min-width: 40px;
        }
    }
</style>
