<template>
    <div
        class="product-content__panel product-content__panel--full-width pa-0"
        :class="$style.ParamsProduct"
    >
        <div v-show="Object.keys(getValues.notFilled).length">
            <div
                class="title-h5 title-h5--medium pl-5 mt-3 mb-3"
                :class="$style.ParamsProductHeader"
            >
                <span style="font-size: 16px">Нужно заполнить</span>
            </div>
            <div class="product-params-form pl-4 pr-4">
                <se-alert type="alert" class="mb-7">
                    Убедитесь, что вы указали все возможные характеристики этого товара. Чем больше
                    значений вы укажете, тем больше покупателей увидят товар
                </se-alert>
                <fields ref="isFilledForm" no-filled :values="getValues.notFilled" seek-attention />
                <div v-if="recommendations.length"></div>
            </div>
        </div>

        <span class="title-h5 title-h5--medium pl-5" :class="$style.ParamsProductHeader">
            <span style="font-size: 16px">Заполненные</span>
        </span>

        <div class="product-params-form pl-4 pr-4 pt-3">
            <SeAlert class="mb-7">
                Проверьте, насколько введенные значения соответствуют вашему товару. Чем точнее
                характеристики товара, тем легче покупателям будет найти и купить ваш товар
            </SeAlert>
            <fields ref="notFilledForm" :values="getValues.isFilled" class="mb-0" />
            <div v-if="isSelectedMp.id === 1" class="req-char-ozon">
                <VRow class="mb-4 pr-2 pl-2" no-gutters>
                    <VCol lg="12" sm="12">
                        <VTextField
                            v-model="weightOzon"
                            type="number"
                            :label="`Вес c упаковкой, г`"
                            class="light-inline mt-0 pd-0 mb-3"
                        ></VTextField>
                    </VCol>
                    <VCol lg="12" sm="12">
                        <VTextField
                            v-model="depthOzon"
                            type="number"
                            :label="`Длина упаковки, мм`"
                            class="light-inline mt-0 pd-0 mb-3"
                        ></VTextField>
                    </VCol>
                    <VCol lg="12" sm="12">
                        <VTextField
                            v-model="widthOzon"
                            type="number"
                            :label="`Ширина упаковки, мм`"
                            class="light-inline mt-0 pd-0 mb-3"
                        ></VTextField>
                    </VCol>
                    <VCol lg="12" sm="12">
                        <VTextField
                            v-model.number="heightOzon"
                            type="number"
                            :label="`Высота упаковки, мм`"
                            class="light-inline mt-0 pd-0"
                        ></VTextField>
                    </VCol>
                </VRow>
            </div>
        </div>
    </div>
</template>

<script>
    /* eslint-disable no-extra-parens */

    import { mapGetters, mapActions, mapState, mapMutations } from 'vuex';
    import SeAlert from '~/components/common/SeAlert.vue';
    import Fields from '~/components/pages/product/Params/Fields.vue';

    const checkValues = el => {
        const elKeys = Object.keys(el);

        for (let i = 0, l = elKeys.length; i < l; i += 1) {
            if (!el[elKeys[i]]) {
                return false;
            }
        }
        return true;
    };

    export default {
        // eslint-disable-next-line vue/match-component-file-name
        name: 'ParamsProduct',
        components: { Fields, SeAlert },
        props: {
            values: {
                required: true,
                type: Object,
                default: null,
            },
            recommendations: {
                require: true,
                type: Array,
                default: () => [],
            },
        },
        computed: {
            ...mapState('product', {
                ozonData: 'data',
            }),
            ...mapGetters(['isSelectedMp']),
            additCharUnitOzon() {
                /* eslint-disable */
                if (!this.ozonData) {
                    return undefined;
                }

                const { dimension_unit, weight_unit } = this.ozonData;

                return {
                    dimUnit: dimension_unit,
                    weightUnit: weight_unit,
                };
            },
            weightOzon: {
                get() {
                    return this.ozonData?.weight || '';
                },
                set(value) {
                    this.setAdditUnitOzon({ field: 'weight', value });
                },
            },
            depthOzon: {
                get() {
                    return this.ozonData?.depth || '';
                },
                set(value) {
                    this.setAdditUnitOzon({ field: 'depth', value });
                },
            },
            heightOzon: {
                get() {
                    return this.ozonData?.height || '';
                },
                set(value) {
                    this.setAdditUnitOzon({ field: 'height', value });
                },
            },
            widthOzon: {
                get() {
                    return this.ozonData?.width || '';
                },
                set(value) {
                    this.setAdditUnitOzon({ field: 'width', value });
                },
            },
            getFields() {
                return this.values || {};
            },
            getValues() {
                const fields = this.getFields;
                const data = {
                    isFilled: {},
                    notFilled: {},
                };

                // TODO: доработать сортировку с учетом результирующего объекта
                const fieldsSort = Object.values(fields).sort((a, b) => {
                    const rating = {
                        a: a.type === 'multiline' ? 0 : 1,
                        b: b.type === 'multiline' ? 0 : 1,
                    };

                    return rating.b - rating.a;
                });

                fieldsSort.forEach(field => {
                    const newField = { ...field };

                    if (this.isSelectedMp.id === 2 && Array.isArray(newField.selected_options)) {
                        newField.selected_options = newField.selected_options.filter(el =>
                            checkValues(el)
                        );
                    }

                    newField.id = String(newField.id);
                    const key =
                        (newField.value !== '' && newField.value !== undefined) ||
                        (newField.selected_options && newField.selected_options.length)
                            ? 'isFilled'
                            : 'notFilled';

                    const extFields = ['Описание', 'Наименование', 'Ключевые слова'];

                    if (!extFields.includes(newField.id)) {
                        data[key][newField.id] = newField;
                    }
                });

                return data;
            },
            valuesNumber() {
                return {
                    notFilled: Object.values(this.getValues.notFilled).length,
                    isFilled: Object.values(this.getValues.isFilled).length,
                };
            },
        },
        mounted() {
            this.$store.commit('product/setSignalAlert', {
                field: 'char',
                value: this.valuesNumber.notFilled > 0,
            });
        },
        watch: {
            'getValues.notFilled': {
                deep: true,
                immediate: true,
                handler(val) {
                    this.setEmptyCharacterisctics(Object.keys(val).length);
                },
            },
        },
        methods: {
            ...mapMutations('product', ['setAdditUnitOzon']),
            ...mapActions({
                setEmptyCharacterisctics: 'product/setEmptyCharacterisctics',
            }),
            async getInputs() {
                const isFilledForm = this.$refs.isFilledForm.getInputs();
                const notFilledForm = this.$refs.notFilledForm.getInputs();
                const result = await Promise.all([isFilledForm, notFilledForm]);

                if (result.includes(false)) {
                    return false;
                }

                return {
                    characteristics: result.reduce((acc, current) => {
                        Object.keys(current).forEach(key => {
                            const value = current[key];
                            const output = {
                                id: key,
                            };

                            // eslint-disable-next-line valid-typeof
                            if (typeof value === 'object' && value !== null) {
                                output.selected_options = value;
                            } else {
                                output.value = value;
                            }
                            acc.push(output);
                        });
                        return acc;
                    }, []),
                };
            },
        },
    };
</script>

<style lang="scss">
    .product-params-form {
        .custom-form {
            display: flex;
            flex-wrap: wrap;
            margin: 14px 0;

            .product-options__input-grid {
                width: calc(25% - 16px);
                margin: 0 8px;

                @include respond-to(md) {
                    width: calc(50% - 16px);
                }

                @include phone-large {
                    width: calc(100% - 16px);
                }
            }

            .product-options__input-full {
                width: 100%;
            }
        }
    }
</style>

<style lang="scss" module>
    .ParamsProduct {
        & .ParamsProductHeader {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            padding: 13px 0;
            background-color: $base-100;
            font-size: 16px;

            & .Number {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 24px;
                height: 24px;
                border-radius: 50%;
                background-color: #ececec;
                font-size: 12px;
                font-weight: bold;
                color: $base-900;

                &.Unfilled {
                    background-color: $error;
                    color: $white;
                }
            }
        }
    }
</style>
