<template>
    <BaseDrawer :class="$style.ModalAdmAddBaseList" width="50" @close="handleClose">
        <div :class="$style.wrapper">
            <div :class="$style.heading">Добавить товары списком</div>
            <div class="custom-dialog__body">
                <div :class="$style.subheading">
                    Добавьте 3 артикула или ссылки на товары из вашего магазина.
                    <br />
                    Разделите их пробелом или через клавишу Enter.
                    <br />
                    Чтобы добавить больше товаров, подключите
                    <span :class="$style.link">платный тариф.</span>
                    <!-- TODO show only if acc is not activated -->
                </div>
                <VForm ref="formAction" class="custom-form">
                    <FormFieldText
                        v-model="form.skus"
                        component="VTextarea"
                        field="skus"
                        label="Артикул / ссылка"
                        :error-messages="validatorErrors"
                        :validators="validatorsList"
                    />
                </VForm>
            </div>
            <div :class="$style.btnWrapper">
                <VBtn color="accent" block :disabled="isSubmitDisabled" @click="handleSubmit">
                    Добавить товар
                </VBtn>
            </div>
        </div>
    </BaseDrawer>
</template>
<script>
    /* eslint-disable nuxt/no-timing-in-fetch-data,no-empty-function */
    import { omit } from 'lodash';
    import Validator from '~mixins/validator';

    const REQUEST_FILTERS = [];
    export default {
        name: 'ModalAdmAddBaseList',
        mixins: [Validator],
        transition: {
            name: 'drawer',
            mode: 'out-in',
            duration: 1000,
        },
        data() {
            return {
                isLoading: false,
                notification: () => {},
            };
        },
        computed: {
            skusInputList() {
                if (!this.form.skus) {
                    return [];
                }
                return this.form.skus
                    .replace(/ /g, '\n')
                    .split('\n')
                    .filter(sku => Boolean(sku))
                    .map(sku => sku.trim());
            },
            wrongSkus() {
                const checkFormatSkus = this.skusInputList.filter(
                    sku => Number.isNaN(Number(sku)) || sku.length !== 9
                );
                return checkFormatSkus.join(', ');
            },
            repeatSkus() {
                const checkUniqSkus = this.skusInputList.filter(
                    (sku, index) => this.skusInputList.indexOf(sku) !== index
                );
                return checkUniqSkus.join(', ');
            },
            validatorErrors() {
                return {
                    max: 'Вы можете указать максимум 10 артикулов',
                    wrong: 'Следующие артикулы введены неверно: ' + this.wrongSkus,
                    repeat: 'Следующие артикулы дублируются: ' + this.repeatSkus,
                };
            },
            validatorsList() {
                return {
                    max: () => {
                        const skus = this.skusInputList;

                        if (skus.length > 10) {
                            return false;
                        }
                        return true;
                    },
                    wrong: () => !this.wrongSkus,
                    repeat: () => !this.repeatSkus,
                };
            },
        },
        methods: {
            handleClose() {
                const queryOmitted = omit(this.$route.query, [...REQUEST_FILTERS]);
                this.$router.push({
                    name: this.$route.name,
                    params: this.$route.params,
                    query: queryOmitted,
                });
            },
            clearServerError() {
                this.serverError = '';
            },
            async handleSubmit() {
                this.$v.$touch();
                if (this.$v.$anyError || this.isRegistrationDisabled) {
                    return;
                }
                this.notification();
                this.isLoading = true;
                // TODO make request
            },
        },
    };
</script>
<style lang="scss">
    /* stylelint-disable declaration-no-important */
    .v-textarea.v-text-field--box .v-text-field__prefix,
    .v-textarea.v-text-field--box textarea,
    .v-textarea.v-text-field--enclosed .v-text-field__prefix,
    .v-textarea.v-text-field--enclosed textarea {
        margin-top: 16px !important;
    }
</style>
<style lang="scss" module>
    .wrapper {
        display: flex;
        height: 100%;
        padding: 24px;
        flex-direction: column;

        :global(.el-form-item__error) {
            font-size: 12px;
        }
    }

    .textArea {
        font-size: 16px;

        > textarea {
            font-size: inherit;
        }
    }

    .heading {
        @extend %text-h4;

        margin-bottom: 24px;
        padding-right: 32px;
        font-weight: bold;
    }

    .subheading {
        @extend %text-body-1;

        margin-bottom: 24px;
        // text-align: center;

        .link {
            @extend %text-body-2;

            color: $accent;
            transition: $primary-transition;
            cursor: pointer;

            &:hover {
                opacity: 0.65;
            }
        }
    }

    .textarea {
        // min-height: 240px;
        margin-bottom: 24px;

        // :global(.el-textarea__inner) {
        //     height: 240px;
        // }
    }

    // .closeBtnWrapper {
    //     position: absolute;
    //     top: 24px;
    //     right: 24px;

    //     .closeBtn {
    //         width: 32px;
    //         height: 32px;
    //     }
    // }

    .btnWrapper {
        margin-top: auto;
    }
</style>
