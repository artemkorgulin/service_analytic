<template>
    <VTextField
        :value="value"
        :class="[$style.SearchInput, { [$style.LightOutline]: outlineLight }]"
        :label="label"
        :color="color"
        :placeholder="placeholder"
        :background-color="backgroundColor"
        dense
        outlined
        hide-details
        @input="input"
    >
        <template #prepend-inner>
            <SvgIcon name="outlined/search" />
        </template>
    </VTextField>
</template>

<script>
    export default {
        name: 'SearchInput',
        props: {
            value: {
                type: String,
                default: '',
            },
            label: {
                type: String,
                default: '',
            },
            placeholder: {
                type: String,
                default: '',
            },
            color: {
                type: String,
                default: '',
            },
            outlineLight: {
                type: Boolean,
                default: false,
            },
            backgroundColor: {
                type: String,
                default: '',
            },
        },
        methods: {
            input(val) {
                this.$emit('input', val);
            },
        },
    };
</script>

<style lang="scss" module>
    .SearchInput {
        //
    }

    .LightOutline {
        &:global(.v-text-field--outlined) {
            &:global(:not(.v-input--is-focused)) {
                & :global(.v-input__slot:hover) {
                    fieldset {
                        border-color: #710bff;
                    }
                }

                fieldset {
                    border-color: #e9edf2;
                    transition: border-color 200ms ease-in-out;
                }
            }

            &:global(.v-input--is-focused) {
                fieldset {
                    box-shadow: 0 6px 16px 0 rgba(95, 69, 255, 0.16);
                }
            }
        }
    }
</style>
