<template>
    <VCard flat :class="$style.NomenclatureBase">
        <div :class="$style.FormWrapper" class="mt-4">
            <VForm ref="formAction" class="custom-form" :class="[$style.Form, $style.FormWBTop]">
                <VRow>
                    <VCol lg="3" col="12">
                        <select-remote-search
                            v-model="form.fields.baseColor"
                            :items="getDictionaryColors"
                            :max-selected="1"
                            object-model
                            label="Основной цвет"
                            dictionary-slug="/colors"
                            @change="
                                onChange(
                                    `nomenclature.${nomenclature.index}.baseColor`,
                                    `Изменен основной цвет номенклатуры «${nomenclature.name}»`,
                                    'baseColor'
                                )
                            "
                        >
                            <template #append-outer>
                                <div :class="$style.SelectLabel">
                                    <IconTooltip
                                        message="Основной цвет"
                                        margin-right
                                        :icon-color="colors.tooltipIconColor"
                                    />
                                </div>
                            </template>
                        </select-remote-search>
                    </VCol>
                    <VCol lg="3" col="12">
                        <VTextField
                            v-model="form.fields.vendorCode"
                            :rules="form.rules.articul"
                            label="Артикул цвета товара"
                            class="light-inline mt-0 pt-0"
                            color="#710bff"
                            @change="
                                onChange(
                                    `nomenclature.${nomenclature.index}.vendorCode`,
                                    `Изменен артикул цвета номенклатуры «${nomenclature.name}»`,
                                    'vendorCode'
                                )
                            "
                        >
                            <template #append-outer>
                                <div :class="$style.Label">
                                    <IconTooltip
                                        message="Артикул цвета товара"
                                        margin-right
                                        :icon-color="colors.tooltipIconColor"
                                    />
                                </div>
                            </template>
                        </VTextField>
                    </VCol>
                    <VCol lg="3" col="12">
                        <VTextField
                            v-if="variations && variations.length === 1"
                            v-model="form.fields.barcodeBase"
                            :rules="form.rules.articul"
                            label="Штрихкод"
                            class="light-inline mt-0 pt-0"
                            :disabled="barcodeFieldDisabled"
                            color="#710bff"
                            @change="
                                onChange(
                                    `nomenclature.${nomenclature.index}.barcodeBase`,
                                    `Изменен штрихкод номенклатуры ${nomenclature.name}`,
                                    'nmId'
                                )
                            "
                        >
                            <template #append-outer>
                                <div :class="$style.Label">
                                    <IconTooltip
                                        message="Штрихкод"
                                        margin-right
                                        :icon-color="colors.tooltipIconColor"
                                    />
                                </div>
                            </template>
                        </VTextField>
                    </VCol>
                    <VCol lg="3" col="12">
                        <select-remote-search
                            v-model="form.fields.additionalColors"
                            :items="getDictionariesWildberries['/colors']"
                            label="Доп цвета"
                            dictionary-slug="/colors"
                            @change="
                                onChange(
                                    `nomenclature.${nomenclature.index}.additionalColors`,
                                    'Изменены доп. цвета номенклатуры «' + nomenclature.name + '»',
                                    'additionalColors'
                                )
                            "
                        >
                            <template #append-outer>
                                <div :class="$style.SelectLabel">
                                    <IconTooltip
                                        message="Доп цвета"
                                        margin-right
                                        :icon-color="colors.tooltipIconColor"
                                    />
                                </div>
                            </template>
                        </select-remote-search>
                    </VCol>
                </VRow>
            </VForm>
        </div>
    </VCard>
</template>

<script>
    import { mapGetters } from 'vuex';
    import formMixin from '~mixins/form.mixin';
    import productMixin from '~mixins/product.mixin';

    export default {
        name: 'NomenclatureBase',
        mixins: [formMixin, productMixin],
        props: {
            values: {
                type: Object,
                default: null,
            },
            nomenclature: {
                type: [Object, Boolean],
                default: false,
            },
            variations: {
                type: [Array, Boolean],
                default: false,
            },
        },
        data() {
            return {
                baseColor: undefined,
                isNumeric: false,
                form: {
                    fields: {
                        baseColor: null,
                        vendorCode: null,
                        nmId: null,
                        barcodeBase: null,
                    },
                    rules: {
                        articul: [val => Boolean(val) || 'Укажите значение'],
                        price: [
                            val => Boolean(val) || 'Пожалуйста, укажите цену',
                            val => typeof val === 'number' || 'Значение должно быть числом',
                        ],
                        discount: [val => typeof val === 'number' || 'Значение должно быть числом'],
                        marketing_price: [
                            val => typeof val === 'number' || 'Значение должно быть числом',
                        ],
                        buybox_price: [
                            val => typeof val === 'number' || !val || 'Значение должно быть числом',
                        ],
                        premium_price: [
                            val => typeof val === 'number' || !val || 'Значение должно быть числом',
                        ],
                    },
                },
                dictionaryColors: [],
                colors: {
                    tooltipIconColor: '#a6afbd',
                },
                barcodeFieldDisabled: true,
            };
        },
        computed: {
            ...mapGetters({
                getDictionariesWildberries: 'product/getDictionariesWildberries',
            }),
            getDictionaryColors() {
                if (this.getDictionariesWildberries['/colors']) {
                    return this.getDictionariesWildberries['/colors'];
                } else {
                    return [];
                }
            },
            barcodeFieldTrigger() {
                if (this.variations.length === 1) {
                    return JSON.stringify(this.variations?.[0].barcode?.[0]);
                }
                return false;
            },
        },
        watch: {
            baseColor(value) {
                const { value: val } = value;
                this.form.fields.baseColor = [val];
            },
            barcodeFieldTrigger: {
                immediate: true,
                handler(val) {
                    if (val === undefined) {
                        this.barcodeFieldDisabled = false;
                    } else if (val) {
                        this.form.fields.barcodeBase = this.variations[0]?.barcode || '';
                    }
                },
            },
        },
    };
</script>

<style lang="scss" module>
    .NomenclatureBase {
        .Label {
            display: flex;

            .LabelRequired {
                color: $color-pink-dark;
            }
        }

        .SelectLabel {
            margin: 16px 0 4px 9px;
        }

        .FormWrapper {
            padding-top: 1rem;
        }

        .Form {
            &.FormWBTop {
                grid-template-columns: repeat(auto-fit, minmax(14.2rem, 20rem));

                @include respond-to(md) {
                    grid-template-columns: repeat(auto-fit, minmax(21rem, 1fr));
                }
            }

            &.FormWBPrice {
                grid-template-columns: repeat(auto-fit, 18.4rem);
            }

            &
                :global(.v-select.v-text-field--outlined:not(.v-text-field--single-line).v-input--dense
                    .v-select__selections) {
                padding: 0;
            }
        }
    }
</style>
