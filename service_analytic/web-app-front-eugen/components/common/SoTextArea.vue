<template>
    <div
        class="se-form__item"
        :class="{ 'se-form__item_active': activeField }"
        @click="$emit('selectedActive', field)"
    >
        <div :class="[$style.heading, $style.headingIndent]"></div>
        <VTextarea
            ref="purpose"
            v-model="baseValue"
            :label="field"
            :row="1"
            :rules="[v => v.length <= 50 || 'Максимум 50 символов']"
            counter="50"
            class="light-inline"
            @blur="setValuesGen"
        ></VTextarea>
    </div>
</template>

<script>
    /* eslint-disable  */
    import { mapMutations, mapGetters } from 'vuex';
    export default {
        props: {
            field: String,
            activeField: Boolean,
            value: {
                type: String,
                default: '',
            },
        },
        data() {
            return {
                startVale: undefined,
                baseValue: '',
            };
        },
        computed: {
            ...mapGetters('product', ['getProduct']),
        },
        created() {
            try {
                const {
                    data: { addin },
                } = this.getProduct;

                const addinItem = addin
                    .find(({ type }) => type === this.field)
                    .params.map(({ value }) => value);

                this.startValue = addinItem.join(', ');
                this.baseValue = this.startValue;
            } catch (error) {
                console.error(error);
            }
        },
        methods: {
            ...mapMutations('product', ['setWbAddin']),
            pasteText(value) {
                if (this.startValue === this.baseValue) {
                    this.baseValue = `${this.baseValue}, ${value}`;
                } else {
                    this.baseValue = `${this.baseValue} ${value}`;
                }
            },
            setValuesGen() {
                const params = this.baseValue.split(',').map(word => ({ value: word.trim() }));
                this.setWbAddin({ field: this.field, value: params });
            },
        },
    };
</script>

<style lang="scss" module>
    .KeyWordsForm {
        & .field {
            &.fieldActive {
                & :global(fieldset) {
                    border: 2px $primary-500 solid;
                }
            }
        }
    }
</style>
