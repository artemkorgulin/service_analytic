<template>
    <div class="product-content__panel product-content__panel--full-width mt-0 pt-0">
        <div class="product-options__decs">
            <SeAlert v-if="showError" type="alert" class="mb-3">
                <template v-if="isSelectedMp.id === 2">
                    Расскажите покупателям о товаре подробнее, используйте 1000 символов. Опишите
                    действие или функционал, обратите внимание покупателей на потребности, которые
                    закроют приобретение вашего продукта. Подробно расскажите о составе, материалах
                    или технологии производства. Можно указать сопутствующие товары или дать
                    рекомендации по способу использования продукта.
                </template>
                <template v-else>
                    Расскажите покупателям о товаре подробнее, рекомендуем использовать
                    <b>не менее 800</b>
                    символов. Опишите действие или функционал, обратите внимание покупателей на
                    потребности, которые закроют приобретение вашего продукта. Подробно расскажите о
                    составе, материалах или технологии производства. Можно указать сопутствующие
                    товары или дать рекомендации по способу использования продукта
                </template>
            </SeAlert>
            <VForm ref="formAction" class="custom-form">
                <VTextarea
                    v-model="descr"
                    class="light-outline mt-2 pt-0 product-options__decs-input"
                    outlined
                    dense
                    rows="12"
                    no-resize
                    hide-details
                    color="#710bff"
                    @change="onChange('descriptions', 'Обновлено описание товара')"
                />
            </VForm>

            <span class="product-options__decs-valid">
                <span :class="{ 'product-options__decs--invalid': !!invalid }">
                    {{ descriptionLength }}
                </span>
                <span v-if="isSelectedMp.id === 2">/ {{ max }}</span> {{ charactersSrting }}
            </span>
        </div>
    </div>
</template>

<script>
    import { mapMutations, mapGetters, mapState } from 'vuex';
    import productMixin from '~mixins/product.mixin';
    import formMixin from '~mixins/form.mixin';
    import { declOfNum } from '~utils/helper.utils';

    export default {
        name: 'DescProduct',
        mixins: [formMixin, productMixin],
        props: {
            values: {
                require: true,
                type: Object,
                default: null,
            },
            recommendation: {
                require: true,
                type: Boolean,
            },
        },
        data() {
            return {
                min: 100,
                max: 1000,
                form: {
                    fields: {
                        descriptions: '',
                    },
                },
            };
        },

        computed: {
            ...mapState('product', ['commonData']),
            ...mapState('product', { wb: 'dataWildberries', ozon: 'data' }),
            ...mapGetters(['isSelectedMp']),
            ...mapGetters('product', ['getDescrProduct', 'showRecom']),

            maxCountForDescr() {
                return this.isSelectedMp && this.isSelectedMp.id === 2 ? 1000 : 10000;
            },
            showError() {
                try {
                    const { descriptions } = this.form.fields;
                    return 800 > descriptions.length;
                } catch {
                    return false;
                }
                /* eslint-disable */
            },

            descr: {
                get() {
                    return this.commonData.descr;
                },
                set(value) {
                    this.form.fields.descriptions = value;
                    this.setCommonField({ field: 'descr', value });

                    this.$store.commit('product/setSignalAlert', {
                        field: 'descr',
                        value: this.showError,
                    });
                },
            },
            invalid() {
                if (this.isSelectedMp.id !== 2) {
                    return false;
                }

                const value = this.form.fields.descriptions || '';
                const length = String(value).length;

                if (length < this.min) {
                    this.$emit('checkRecommendation', true);
                    return `В описании должно быть хотя бы ${this.min} ${declOfNum(this.min, [
                        'знак',
                        'знака',
                        'знаков',
                    ])}.`;
                }
                if (length > this.max) {
                    this.$emit('checkRecommendation', true);

                    const delta = length - this.max;
                    return `Слишком длинный текст, уберите ${delta} ${declOfNum(delta, [
                        'знак',
                        'знака',
                        'знаков',
                    ])}.`;
                }

                this.$emit('checkRecommendation', false);
                return false;
            },
            descriptionLength() {
                return String(this.form.fields.descriptions || '').length;
            },
            charactersSrting() {
                return this.$options.filters.plural(this.descriptionLength, [
                    'знак',
                    'знака',
                    'знаков',
                ]);
            },
        },
        mounted() {
            this.$store.commit('product/setSignalAlert', {
                field: 'descr',
                value: this.showError,
            });
        },
        methods: {
            ...mapMutations('product', ['setCommonField']),
            setFields() {
                try {
                    this.form.fields.descriptions = this.getDescrProduct;
                } catch (error) {
                    console.error(error);
                }
            },
        },
    };
</script>

<style lang="scss">
    .v-application
        .product-options__decs-input.v-text-field--outlined.v-input--dense
        > .v-input__control
        .v-input__slot {
        max-height: unset;
    }
</style>
