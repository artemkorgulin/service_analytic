<template>
    <BaseDialog v-model="show" width="660">
        <VCard class="custom-dialog">
            <div class="custom-dialog__header custom-dialog__header--close">
                <span class="custom-dialog__main-title">Добавление нового товара</span>
            </div>

            <div class="custom-dialog__body">
                <span class="custom-dialog__text txt-center">
                    Укажите артикул товара из каталога Ozon.
                </span>

                <VForm ref="formAction" class="custom-form">
                    <VTextField
                        v-model="form.fields.sku"
                        :rules="form.rules.sku"
                        outlined
                        dense
                        color="#710bff"
                    />
                </VForm>
            </div>

            <div class="custom-dialog__footer">
                <VBtn
                    class="primary-btn--full-width"
                    color="accent"
                    :loading="pending"
                    @click="addProduct"
                >
                    Добавить товар
                </VBtn>
            </div>
        </VCard>
    </BaseDialog>
</template>

<script>
    import formMixin from '~mixins/form.mixin';
    import { errorHandler } from '~utils/response.utils';

    export default {
        name: 'AddNewProduct',
        mixins: [formMixin],
        data() {
            return {
                show: true,
                pending: false,
                form: {
                    fields: {
                        sku: '',
                    },
                    rules: {
                        sku: [
                            val => Boolean(val) || 'Пожалуйста, заполните это поле',
                            val => {
                                const sku = String(val).trim();

                                if (!sku) {
                                    return 'Пожалуйста, заполните это поле';
                                } else if (sku.length !== 9 || Number.isNaN(Number(sku))) {
                                    return 'Артикул введен неверно';
                                } else {
                                    return true;
                                }
                            },
                        ],
                    },
                },
            };
        },
        methods: {
            async addProduct() {
                const check = await this.$refs.formAction.validate();

                if (check) {
                    this.pending = true;

                    this.$axios
                        .$post('/api/vp/v2/add-product', {
                            sku: this.form.fields.sku,
                        })
                        .then(() => {
                            this.show = false;
                            this.form.fields.sku = null;
                            this.$store.dispatch('products/LOAD_PRODUCTS', {
                                type: 'common',
                                reload: true,
                            });
                        })
                        .catch(({ response }) => {
                            errorHandler(response, this.$notify);
                        })
                        .finally(() => {
                            this.pending = false;
                        });
                }
            },
        },
    };
</script>
