<template>
    <VForm ref="formAction" :class="$style.SizeProductItem" class="custom-form">
        <VTextField
            v-model="form.fields.sizeName"
            :rules="form.rules.notEmpty"
            label="Размеры"
            class="light-inline"
            color="#710bff"
            @change="
                onChange(
                    `nomenclature.${nomenclature.index}.size.${index}.sizeName`,
                    `Изменен размер ${values.sizeName} номенклатуры «${nomenclature.name}»`,
                    'sizeName'
                )
            "
        >
            <template #append-outer>
                <div :class="$style.Label">
                    <IconTooltip
                        message="Размеры"
                        margin-right
                        :icon-color="colors.tooltipIconColor"
                    />
                </div>
            </template>
        </VTextField>

        <select-remote-search
            v-model="form.fields.sizeValue"
            :max-selected="1"
            :rules="form.rules.notEmpty"
            label="Размер справочный"
            dictionary-slug="/wbsizes"
            disabled
            @change="
                onChange(
                    `nomenclature.${nomenclature.index}.size.${index}.sizeValue`,
                    `Изменено справочное значение размера ${values.sizeName} номенклатуры «${nomenclature.name}»`,
                    'sizeValue'
                )
            "
        >
            <template #append-outer>
                <div :class="$style.SelectLabel">
                    <IconTooltip
                        message="Размер справочный"
                        margin-right
                        :icon-color="colors.tooltipIconColor"
                    />
                </div>
            </template>
        </select-remote-search>

        <VTextField
            v-model="form.fields.barcode"
            :rules="form.rules.notEmpty"
            label="Штрихкод"
            class="light-inline"
            :disabled="barcodeFieldDisabled"
            color="#710bff"
            @change="
                onChange(
                    `nomenclature.${nomenclature.index}.size.${index}.barcode`,
                    `Изменен штрих-код размера ${values.sizeName} номенклатуры «${nomenclature.name}»`,
                    'barcode'
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
        <VTextField
            v-model.number="form.fields.price"
            :rules="form.rules.isNumber"
            label="Цена"
            class="light-inline"
            disabled
            color="#710bff"
            @change="
                onChange(
                    `nomenclature.${nomenclature.index}.size.${index}.price`,
                    `Изменена цена для размера ${values.sizeName} номенклатуры «${nomenclature.name}»`,
                    'price'
                )
            "
        >
            <template #append-outer>
                <div :class="$style.Label">
                    <IconTooltip
                        message="Цена"
                        margin-right
                        :icon-color="colors.tooltipIconColor"
                    />
                </div>
            </template>
        </VTextField>
    </VForm>
</template>

<script>
    import formMixin from '~mixins/form.mixin';
    import productMixin from '~mixins/product.mixin';

    export default {
        name: 'SizeProductItem',
        mixins: [formMixin, productMixin],
        props: {
            values: {
                type: Object,
                default: null,
            },
            index: {
                type: Number,
                default: 0,
            },
            barcodeFieldDisabled: {
                type: Boolean,
                default: true,
            },
        },
        data() {
            return {
                form: {
                    fields: {
                        barcode: null,
                        sizeName: null,
                        sizeValue: null,
                        price: null,
                    },
                    rules: {
                        notEmpty: [val => Boolean(val) || 'Укажите значение'],
                        isNumber: [val => typeof val === 'number' || 'Введите число'],
                    },
                },
                colors: {
                    tooltipIconColor: '#a6afbd',
                },
            };
        },
        computed: {
            nomenclature() {
                return this.values.nomenclature;
            },
            sizeIndex() {
                return this.index;
            },
        },
    };
</script>

<style lang="scss" module>
    .SizeProductItem {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(14.2rem, 1fr));
        grid-template-rows: none;
        grid-auto-flow: row dense;
        grid-column-gap: 16px;

        .SelectLabel {
            margin: 12px 0 0 9px;
        }

        &:global(.custom-form .v-select.v-input--dense .v-chip--select) {
            background: $base-700 !important;
        }
    }
</style>
