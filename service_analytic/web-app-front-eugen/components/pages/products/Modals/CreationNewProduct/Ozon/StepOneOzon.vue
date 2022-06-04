<template>
    <VForm ref="formAction" class="custom-form">
        <VContainer>
            <VRow>
                <VCol>
                    <VTextField
                        v-model="form.fields.name"
                        :rules="form.rules.name"
                        label="Название товара"
                        outlined
                        dense
                        color="#710bff"
                    />
                </VCol>
            </VRow>
            <VRow class="mt-0">
                <VCol cols="12" md="6" xs="12">
                    <v-autocomplete
                        v-model="form.fields.category_id"
                        :items="categories"
                        :search-input.sync="queryCategory"
                        :rules="form.rules.category_id"
                        label="Категория"
                        :loading="pendingCategory"
                        outlined
                        dense
                        color="#710bff"
                        item-text="name"
                        item-value="id"
                        @change="setCategory"
                    />
                </VCol>
                <VCol cols="12" md="6" xs="12">
                    <VTextField
                        v-model="form.fields.barcode"
                        :rules="form.rules.barcode"
                        :disabled="true"
                        label="Артикул"
                        outlined
                        dense
                        color="#710bff"
                    />
                </VCol>
            </VRow>
            <VRow class="mt-0">
                <VCol cols="12" md="4" xs="12">
                    <VTextField
                        v-model.number="form.fields.price"
                        :rules="form.rules.price"
                        label="Цена"
                        outlined
                        dense
                        color="#710bff"
                    />
                </VCol>
                <VCol cols="12" md="4" xs="12">
                    <VTextField
                        ref="oldPriceField"
                        v-model.number="form.fields.old_price"
                        :rules="form.rules.old_price"
                        label="Цена до скидки"
                        outlined
                        dense
                        color="#710bff"
                    />
                </VCol>
                <VCol cols="12" md="4" xs="12">
                    <VTextField
                        v-model.number="form.fields.ozon_price"
                        :rules="form.rules.ozon_price"
                        label="Цена с Ozon Premium"
                        outlined
                        dense
                        color="#710bff"
                    />
                </VCol>
            </VRow>
            <VRow class="mt-0">
                <VCol>
                    <span class="base-txt base-txt--bold">НДС (налог на добавочную стоимость)</span>

                    <VRadio-group v-model="form.fields.vat" class="custom-radio" row hide-details>
                        <VRadio label="10%" :value="0.1" color="#710bff" />
                        <VRadio label="20%" :value="0.2" color="#710bff" />
                        <VRadio label="Не облагается" :value="0" color="#710bff" />
                    </VRadio-group>
                </VCol>
            </VRow>
            <VRow class="mt-6">
                <VCol
                    v-for="(param, key) in baseParams"
                    :key="key"
                    cols="12"
                    md="4"
                    xs="12"
                    class="py-0"
                >
                    <template v-if="param.type === 'Integer' || param.type === 'Decimal'">
                        <VTextField
                            v-model.number="form.fields[key]"
                            :rules="form.rules[key]"
                            :label="param.description"
                            outlined
                            dense
                            color="#710bff"
                        />
                    </template>
                    <template v-else>
                        <VTextField
                            v-model="form.fields[key]"
                            :rules="form.rules[key]"
                            :label="param.description"
                            outlined
                            dense
                            color="#710bff"
                        />
                    </template>
                </VCol>
            </VRow>
        </VContainer>
    </VForm>
</template>

<script>
    import { mapGetters } from 'vuex';
    import formMixin from '~mixins/form.mixin';
    import { debounce } from '~utils/helper.utils';
    import { errorHandler } from '~utils/response.utils';

    export default {
        name: 'StepOneOzon',
        mixins: [formMixin],
        data() {
            return {
                radio: 0,
                queryCategory: '',
                form: {
                    fields: {},
                    rules: {},
                    base: {
                        fields: {
                            name: '',
                            category_id: null,
                            barcode: null,
                            price: null,
                            old_price: null,
                            ozon_price: null,
                            vat: 0,
                        },
                        rules: {
                            name: [val => Boolean(val) || 'пожалуйста, укажите название товара'],
                            category_id: [
                                val => Boolean(val) || 'пожалуйста, укажите категорию товара',
                            ],
                            barcode: [val => Boolean(val) || 'пожалуйста, укажите артикул товара'],
                            price: [
                                val => Boolean(val) || 'пожалуйста, укажите цену товара',
                                val => typeof val === 'number' || 'значение должно быть числом',
                                val =>
                                    val <= this.$refs.oldPriceField.value ||
                                    'цена не может быть больше цены до скидки',
                            ],
                            old_price: [
                                val => Boolean(val) || 'пожалуйста, укажите цену товара',
                                val => typeof val === 'number' || 'значение должно быть числом',
                            ],
                            ozon_price: [
                                val => Boolean(val) || 'пожалуйста, укажите цену товара',
                                val => typeof val === 'number' || 'значение должно быть числом',
                                val =>
                                    val <= this.$refs.oldPriceField.value ||
                                    'цена-премимум не может быть больше цены до скидки',
                            ],
                        },
                    },
                },
                params: {
                    ignore: [
                        'name',
                        'images',
                        'barcode',
                        'category_id',
                        'offer_id',
                        'vat',
                        'price',
                        'old_price',
                    ],
                },
            };
        },
        computed: {
            ...mapGetters({
                categories: 'category/GET_CATEGORIES',
                pendingCategory: 'category/GET_PENDING',
                isResult: 'category/IS_RESULT',
                paramsData: 'products/GET_NEW_PRODUCT',
                marketplaceSlug: 'getSelectedMarketplaceSlug',
            }),
            baseParams() {
                const params = this.paramsData.params ? this.paramsData.params[0] : null;
                let result = {};

                if (params) {
                    Object.keys(params).forEach(key => {
                        if (!this.params.ignore.includes(key)) {
                            result[key] = params[key];
                        }
                    });
                } else {
                    result = params;
                }

                return result;
            },
        },
        watch: {
            baseParams() {
                this.setFields();
            },
            invalid(val) {
                this.$emit('setValid', !val);
            },
            queryCategory(val) {
                const check = this.categories.find(item => item.name === val);

                if (val && val.length > 2 && !check) {
                    this.$store.commit('category/SET_SEARCH', val);

                    debounce({
                        time: 1000,
                        collBack: () => {
                            this.$store.dispatch('category/LOAD_CATEGORIES', this.marketplaceSlug);
                        },
                    });
                }
            },
            form: {
                deep: true,
                handler() {
                    this.$nextTick(() => {
                        this.$refs.formAction.validate();
                    });
                },
            },
        },
        mounted() {
            this.$axios
                .$get('/api/vp/v2/generate-barcode/')
                .then(res => {
                    this.form.base.fields.barcode = res.barcode;
                    this.setFields();
                })
                .catch(({ response }) => {
                    errorHandler(response, this.$notify);
                });
        },
        methods: {
            setFields() {
                const fields = { ...this.form.base.fields };
                const rules = { ...this.form.base.rules };

                Object.keys(fields).forEach(key => {
                    if (this.form.fields[key]) {
                        fields[key] = this.form.fields[key];
                    }
                });

                if (this.baseParams) {
                    Object.keys(this.baseParams).forEach(key => {
                        const param = this.baseParams[key];
                        const paramRules = [
                            val => {
                                if (param.type === 'Integer' || param.type === 'Decimal') {
                                    return typeof val === 'number' || 'поле заполнено неверно';
                                }
                                return typeof val === 'string' || 'поле заполнено неверно';
                            },
                        ];

                        fields[key] = null;

                        if (param.is_required) {
                            paramRules.unshift(val => Boolean(val) || 'обязательное поле');
                        }

                        rules[key] = paramRules;
                    });
                }

                this.$set(this.form, 'fields', fields);
                this.$set(this.form, 'rules', rules);
            },
            setCategory() {
                if (this.form.fields.category_id) {
                    this.$store.commit(
                        'products/SET_NEW_PRODUCT_CATEGORY',
                        this.form.fields.category_id
                    );

                    debounce({
                        time: 500,
                        collBack: () => {
                            this.$store.dispatch('products/LOAD_NEW_PRODUCT_PARAMS');
                        },
                    });
                }
            },
        },
    };
</script>

<style lang="scss">
    .custom-radio {
        margin: 16px 0 8px;
    }
</style>
