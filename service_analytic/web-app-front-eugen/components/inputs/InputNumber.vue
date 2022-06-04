<template>
    <VTextField
        v-if="!persistentLabel"
        :class="$style.input"
        v-bind="$attrs"
        :value="value"
        @input="handleInput"
    >
        <template #prepend-inner>
            <VBtn v-touch-repeat:0:100.mouse.enter.space="decrement" icon small>
                <SvgIcon name="outlined/minus" />
            </VBtn>
        </template>
        <template #append>
            <VBtn v-touch-repeat:0:100.mouse.enter.space="increment" icon small>
                <SvgIcon name="outlined/plus" />
            </VBtn>
        </template>
    </VTextField>
    <div v-else :class="$style.InputNumber">
        <div v-if="!$slots.label" class="v-label">
            {{ label }}
        </div>
        <slot v-else name="label"></slot>
        <VTextField :class="$style.input" :value="value" v-bind="$attrs" @input="handleInput">
            <template #prepend-inner>
                <VBtn v-touch-repeat:0:100.mouse.enter.space="decrement" icon small>
                    <SvgIcon name="outlined/minus" />
                </VBtn>
            </template>
            <template #append>
                <VBtn v-touch-repeat:0:100.mouse.enter.space="increment" icon small>
                    <SvgIcon name="outlined/plus" />
                </VBtn>
            </template>
        </VTextField>
    </div>
</template>

<script>
    /* eslint-disable vue/require-default-prop*/
    export default {
        name: 'InputNumber',
        inheritAttrs: false,
        props: {
            value: {
                type: [String, Number],
                default: 0,
            },
            persistentLabel: {
                type: Boolean,
                default: false,
            },
        },
        methods: {
            decrement() {
                return this.$emit('input', Number(this.value) - 1);
            },
            increment() {
                return this.$emit('input', Number(this.value) + 1);
            },
            handleInput(val) {
                return this.$emit('input', val);
            },
        },
    };
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .InputNumber {
        :global(.v-label) {
            margin-bottom: 4px;
        }

        :global(.v-text-field__details) {
            padding-left: 0 !important;
        }
    }

    .input {
        @extend %text-body-1;

        input {
            text-align: center;
        }
    }
</style>
