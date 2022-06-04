<template>
    <VSelect
        v-model="internalValue"
        :items="internalOptions"
        :label="label"
        :item-text="labelField"
        :item-value="valueField"
        :item-disabled="disabledField"
        :menu-props="{ 'offset-y': true, 'content-class': 'VFilter menuResponsive' }"
        outlined
        dense
        multiple
        clearable
        hide-details
    >
        <template #selection="{ item, index }">
            <div v-if="value && index < maxItems">
                <span :class="$style.ellipsis">{{ item[labelField] }}</span>
            </div>
            <span
                v-if="value && value.length > maxItems && index === value.length - 1"
                :class="$style.append"
            >
                &nbsp;и еще
                <span :class="$style.count">{{ value.length - maxItems }}</span>
            </span>
        </template>
    </VSelect>
</template>

<script>
    /* eslint-disable no-extra-parens,vue/require-prop-types,vue/require-default-prop */
    export default {
        name: 'VFilter',
        props: {
            options: {
                type: Array,
                default: () => [],
            },
            label: {
                type: String,
                default: '',
            },
            value: {},
            labelField: {
                type: String,
                default: 'label',
            },
            valueField: {
                type: String,
                default: 'value',
            },
            disabledField: {
                type: String,
                default: 'disabled',
            },
            maxWidth: {
                type: [Number, String],
                default: null,
            },
            maxItems: {
                type: Number,
                default: 1,
            },
        },
        computed: {
            styles() {
                return {
                    'max-width': this.maxWidth ? `${this.maxWidth}px` : false,
                };
            },
            internalValue: {
                get() {
                    return this.value;
                },
                set(value) {
                    this.$emit('change', value);
                },
            },
            internalOptions() {
                return this.options.map(item => ({
                    ...item,
                    [this.valueField]: String(item[this.valueField]),
                    ...(typeof item.is_active !== 'undefined'
                        ? {
                              disabled: !item.is_active,
                          }
                        : null),
                }));
            },
        },
    };
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */

    :global(.VFilter) {
        z-index: 15 !important;

        :global(.v-simple-checkbox) {
            height: 24px;
        }

        :global(.v-icon__svg) {
            width: 20px;
            height: 20px;
        }
    }

    :global(.v-input__icon) {
        width: 2.4rem !important;
        min-width: 2.4rem !important;
        height: 2.4rem !important;
    }

    .chip {
        max-width: 120px !important;
        height: 18px !important;
        margin-top: 4.5px;
        margin-left: 0 !important;
        padding: 0 6px !important;
        font-size: 12px !important;
    }

    .append {
        margin-top: 4.5px;
        font-size: 1.2rem;
    }

    .count {
        color: $accent;
        font-weight: bold;
    }

    .ellipsis {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>
