<template>
    <div class="control">
        <v-switch
            v-model="thisValue"
            class="control__switch"
            inset
            :label="label"
            @change="handleChange"
            @click="handleClick"
            @focus="handleFocus"
            @blur="handleBlur"
        ></v-switch>
    </div>
</template>
<script>
    export default {
        model: {
            prop: 'value',
            event: 'input',
        },
        props: {
            value: { type: Boolean, default: false },
            label: { type: String, default: '' },
        },
        computed: {
            thisValue: {
                get: function () {
                    return this.value;
                },
                set: function (newValue) {
                    this.$emit('input', newValue);
                },
            },
        },
        methods: {
            handleClick() {
                this.$emit('click');
            },
            handleFocus() {
                this.$emit('focus');
            },
            handleBlur() {
                this.$emit('blur');
            },
            handleChange() {
                this.$emit('change');
            },
        },
    };
</script>
<style lang="scss">
    .control {
        .control__switch {
            margin: 0;
            padding: 0;

            .v-input--selection-controls__ripple {
                display: none;
            }

            .v-messages {
                display: none;
            }

            .v-input__slot {
                margin: 0;
                padding: 0;
            }

            .v-input--switch__track {
                top: calc(50% - 11px);
                left: -2px;
                width: 36px;
                height: 20px;
            }

            .v-input--switch__thumb {
                width: 18px;
                height: 18px;
            }

            .v-input--switch__thumb.theme--light.primary--text {
                transform: translate(15px, 0) !important;
            }
        }
    }
</style>
