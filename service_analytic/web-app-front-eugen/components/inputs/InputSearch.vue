<template>
    <VTextField
        v-model="lazyValue"
        v-bind="$attrs"
        :class="[$style.InputSearch, responsive && 'responsiveInput']"
        :label="label"
        dense
        outlined
        clearable
        hide-details
        v-on="listeners"
    >
        <!-- @input="input" -->
        <template #prepend-inner>
            <SvgIcon name="outlined/search" />
        </template>
    </VTextField>
</template>

<script>
    /* eslint-disable no-unused-vars*/
    export default {
        name: 'InputSearch',
        inheritAttrs: false,
        props: {
            value: {
                type: String,
                default: '',
            },
            label: {
                type: String,
                default: '',
            },
            responsive: {
                type: Boolean,
                default: false,
            },
        },
        data() {
            return {
                innerValue: this.value,
            };
        },
        computed: {
            listeners() {
                const { input, ...listeners } = this.$listeners;
                return listeners;
            },
            lazyValue: {
                get() {
                    return this.innerValue;
                },
                set(val) {
                    // console.log('🚀 ~ file: InputSearch.vue ~ line 54 ~ set ~ val', val);
                    this.innerValue = val;
                },
            },
        },
        watch: {
            innerValue: {
                // immediate: true,
                handler(val) {
                    this.$emit('input', val);
                },
            },
        },
        // mounted() {
        //     console.log('🚀 ~ file: InputSearch.vue ~ line 45 ~ mounted ~ $attrs', this.$attrs);
        // },
        // methods: {
        //     input(val) {
        //         return this.$emit('input', val);
        //     },
        // },
    };
</script>

<style lang="scss" module>
    .InputSearch {
        // :global(.v-input__prepend-inner) {
        //     padding-left: 8px;
        // }
    }
</style>
