<template>
    <BaseDialog v-model="show" width="660">
        <VCard class="custom-dialog">
            <div class="custom-dialog__header custom-dialog__header--close">
                <span class="custom-dialog__main-title">Добавление нового товара</span>
            </div>

            <div class="custom-dialog__body">
                <span class="custom-dialog__text txt-center">
                    Добавьте от 1 до 10 артикулов из каталога Ozon.
                    <br />
                    Артикулы должны разделяться пробелом или клавишей Enter.
                </span>

                <VForm ref="formAction" class="custom-form">
                    <VTextarea
                        v-model="form.fields.skus"
                        :rules="form.rules.skus"
                        rows="8"
                        outlined
                        dense
                        no-resize
                        color="#710bff"
                    />
                </VForm>
            </div>

            <div class="custom-dialog__footer">
                <VBtn
                    class="primary-btn--full-width"
                    color="accent"
                    :loading="pending"
                    @click="addProducts"
                >
                    Добавить товары
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
                        skus: '',
                    },
                    rules: {
                        skus: [
                            val =>
                                Boolean(val) || 'Пожалуйста, введите данные хотя бы одного товара',
                            val => {
                                const skus = String(val)
                                    .replace(/ /g, '\n')
                                    .split('\n')
                                    .filter(sku => Boolean(sku))
                                    .map(sku => sku.trim());

                                if (skus.length > 10) {
                                    return 'Вы можете указать максимум 10 артикулов';
                                } else {
                                    const checkFormatSkus = skus.filter(
                                        sku => Number.isNaN(Number(sku)) || sku.length !== 9
                                    );
                                    const checkUniqSkus = skus.filter(
                                        (sku, index) => skus.indexOf(sku) !== index
                                    );

                                    if (checkFormatSkus.length) {
                                        return `Следующие артикулы введены неверно: ${checkFormatSkus.join(
                                            ', '
                                        )}`;
                                    } else if (checkUniqSkus.length) {
                                        return `Найдены повторы: ${checkUniqSkus.join(', ')}`;
                                    } else {
                                        return true;
                                    }
                                }
                            },
                        ],
                    },
                },
            };
        },
        methods: {
            async addProducts() {
                const check = await this.$refs.formAction.validate();

                if (check) {
                    this.pending = true;

                    this.$axios
                        .$post('/api/vp/v2/add-products', {
                            skus: this.form.fields.skus,
                        })
                        .then(() => {
                            this.show = false;
                            this.form.fields.skus = null;
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
