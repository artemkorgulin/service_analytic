<template>
    <div :style="isSelectedMp.id === 2 ? 'padding-top: 20px' : ''">
        <template v-if="showRecom">
            <SeAlert v-if="showError" type="alert" class="mb-3">
                Средняя цена аналогичного товара у конкурентов с первой страницы в категории -
                {{ Math.round(infoTop36.price) }}. Измените{{
                    isSelectedMp.id === 1 ? ' розничную' : ''
                }}
                цену до {{ Math.round(infoTop36.price) }}, чтобы конкурировать по цене в категории
            </SeAlert>
            <SeAlert v-else type="success" class="mb-3">
                Средняя цена аналогичного товара у конкурентов с первой страницы в категории -
                {{ Math.round(infoTop36.price) }}. Ваша цена не превышает среднюю цену у конкурентов
            </SeAlert>
        </template>
        <SeAlert v-if="additionalPrice" type="alert" class="mb-3">
            Цена товара на маркетплейсе отличается от той, что вы хотите отправить, если вы хотите
            установить новую цену, отправьте карточку без изменений. <br />
            {{ additionalAlertPriceTitle }} -
            {{ Number(additionalPrice).toLocaleString('ru-RU') }} ₽
            <br />
            <template v-if="isSelectedMp.id !== 1">
                <div>{{ additionalAlertDiscountTitle }} - {{ additionalDiscount }}%</div>
            </template>
        </SeAlert>
        <div class="badges-price">
            <div class="badges-price__item mr-2">
                <span class="badges-price__title">Текущая цена</span>
                <span class="badges-price__price"
                    >{{ Number(originalPrice).toLocaleString('ru-RU') }} ₽</span
                >
            </div>
            <div v-if="calcNewPrice !== originalPrice" class="badges-price__item">
                <span class="badges-price__title">Новая цена</span>
                <div class="badges-price__info d-flex align-center justify-space-between">
                    <span class="badges-price__price"
                        >{{ Number(calcNewPrice).toLocaleString('ru-RU') }} ₽</span
                    >
                    <div class="badges-price__change-info">
                        <span class="calc">
                            {{ calcRatioPrice > 0 ? '+' : '' }}
                            {{ Number(calcRatioPrice).toLocaleString('ru-RU') }} ₽
                        </span>
                        <SvgIcon
                            name="outlined/arrowUp"
                            :color="arrowStyleCalc.color"
                            style="height: 14px"
                            :style="arrowStyleCalc.style"
                        ></SvgIcon>
                    </div>
                </div>
            </div>
            <div v-if="additionalPrice" class="badges-price__item badges-price__item--additional">
                <span class="badges-price__title">{{ additionalPriceTitle }}</span>
                <div class="badges-price__info d-flex align-center justify-space-between">
                    <span class="badges-price__price"
                        >{{ Number(additionalPrice).toLocaleString('ru-RU') }} ₽</span
                    >
                </div>
            </div>
        </div>
        <template v-if="marketplaceSlug === 'wildberries'">
            <div class="product-options__price">
                <span class="product-options__price-value mb-6">Цена</span>
                <VForm ref="formAction" :class="[$style.Form, $style.FormWBPrice]">
                    <VTextField
                        v-model.number="form.fields.price"
                        :rules="form.rules.marketing_price"
                        label="Розничная цена"
                        class="light-inline"
                        dense
                        color="#710bff"
                        @change="
                            onChange(
                                `nomenclature.${nomenclature.index}.price`,
                                `Изменена розничная цена номенклатуры ${nomenclature.name}`,
                                'price'
                            )
                        "
                    />
                    <VTextField
                        v-model.number="form.fields.discount"
                        :rules="form.rules.discount"
                        type="number"
                        label="Скидка"
                        class="light-inline"
                        dense
                        color="#710bff"
                        suffix="%"
                        @change="
                            onChange(
                                `nomenclature.${nomenclature.index}.discount`,
                                `Изменена скидка на товар номенклатуры ${nomenclature.name}`,
                                'discount'
                            )
                        "
                    />
                </VForm>
            </div>
        </template>
        <template v-else>
            <div class="product-options__price">
                <VForm ref="formAction" class="custom-form custom-form--not-required-symbol">
                    <VTextField
                        v-model.number="form.fields.old_price"
                        :rules="form.rules.old_price"
                        type="number"
                        label="Базовая цена"
                        class="light-inline"
                        dense
                        color="#710bff"
                        @change="onChange('old_price', 'Изменена базовая цена товара')"
                    />
                    <VTextField
                        v-model.number="form.fields.discount"
                        label="Скидка"
                        type="number"
                        class="light-inline"
                        dense
                        disabled
                        color="#710bff"
                        suffix="%"
                        @change="onChange('discount', 'Изменена скидка на товар')"
                    />
                    <VTextField
                        v-model.number="form.fields.price"
                        :rules="form.rules.price"
                        label="Розничная цена"
                        type="number"
                        class="light-inline"
                        dense
                        color="#710bff"
                        @change="onChange('price', 'Изменена розничная цена товара')"
                    />
                </VForm>
            </div>
        </template>
    </div>
</template>

<script>
    import { mapState, mapGetters } from 'vuex';
    import formMixin from '~mixins/form.mixin';
    import productMixin from '~mixins/product.mixin';
    /* eslint-disable no-extra-parens */
    export default {
        name: 'PriceProduct',
        mixins: [formMixin, productMixin],
        props: {
            index: {
                type: Number,
                default: 0,
            },
            values: {
                require: true,
                type: Object,
                default: null,
            },
            nomenclature: {
                type: [Object, Boolean],
                default: false,
            },
        },
        data() {
            return {
                selfMounted: true,
                isNumeric: true,
                price: 0,
                originalPrice: 0,
                form: {
                    fields: {
                        price: 0,
                        discount: 0,
                        promocode: 0,
                        old_price: 0,
                        buybox_price: 0,
                        premium_price: 0,
                    },
                    rules: {
                        old_price: [
                            val => typeof val === 'number' || 'Значение должно быть числом',
                        ],
                        price: [
                            val => Boolean(val) || 'Пожалуйста, укажите цену',
                            val => typeof val === 'number' || 'Значение должно быть числом',
                        ],
                        discount: [
                            val =>
                                val === 0 ||
                                (val >= 3 && val <= 100) ||
                                'Значение должно быть в диапазоне от 3 до 100',
                        ],
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
                colors: {
                    tooltipIconColor: '#a6afbd',
                },
            };
        },

        computed: {
            ...mapGetters({
                isSelectedMp: 'isSelectedMp',
                marketplaceSlug: 'getSelectedMarketplaceSlug',
            }),

            ...mapState('product', ['dataWildberries', 'infoTop36']),
            ...mapGetters('product', [
                'showRecom',
                'getAdditionalPricesOzon',
                'getAdditionalPricesWB',
            ]),

            showError() {
                return this.infoTop36.price < this.calcNewPrice;
            },
            calcRatioPrice() {
                return this.calcNewPrice - this.originalPrice;
            },
            calcNewPrice() {
                const { discount, price } = this.form.fields;
                if (this.isSelectedMp.id === 1) {
                    return price;
                } else {
                    return Math.floor(price - (price * discount) / 100);
                }
            },
            additionalPrice() {
                let price = 0;

                if (this.isSelectedMp.id === 1) {
                    const externalId = this.$store.state.product.data.externalId;
                    this.getAdditionalPricesOzon.forEach(el => {
                        if (el.id === externalId) {
                            price = el.price;
                        }
                    });
                } else {
                    const { data_nomenclatures: nomenclatures } = this.dataWildberries;
                    const dataPrice = nomenclatures[this.index];

                    this.getAdditionalPricesWB.forEach(el => {
                        if (el.nmid === dataPrice.nmId) {
                            price = el.price;
                        }
                    });
                }

                return price;
            },
            additionalDiscount() {
                let discount = 0;

                if (this.isSelectedMp.id !== 1) {
                    const { data_nomenclatures: nomenclatures } = this.dataWildberries;
                    const dataPrice = nomenclatures[this.index];

                    this.getAdditionalPricesWB.forEach(el => {
                        if (el.nmid === dataPrice.nmId) {
                            discount = el.discount;
                        }
                    });
                }

                return discount;
            },
            additionalPriceTitle() {
                return `Цена на ${this.isSelectedMp.id === 1 ? 'Ozon' : 'WB'}`;
            },
            additionalAlertPriceTitle() {
                return `Розничная цена на ${this.isSelectedMp.id === 1 ? 'Ozon' : 'WB'}`;
            },
            additionalAlertDiscountTitle() {
                return `Скидка на ${this.isSelectedMp.id === 1 ? 'Ozon' : 'WB'}`;
            },
            arrowStyleCalc() {
                /* eslint-disable */
                if (this.calcNewPrice > this.originalPrice) {
                    return {
                        color: '#20C274',
                        style: '',
                    };
                } else {
                    return {
                        color: '#20C274',
                        style: 'transform: rotate(180deg);',
                    };
                }
            },
        },
        watch: {
            calcNewPrice() {
                this.$store.commit('product/setSignalAlert', {
                    field: 'price',
                    value: this.showError,
                });
            },
            'form.fields': {
                handler() {
                    if (this.isSelectedMp.id === 1) {
                        const { price, old_price } = this.form.fields;
                        this.form.fields.discount = Math.floor(
                            ((old_price - price) / old_price) * 100
                        );
                    } else {
                    }
                },
                deep: true,
            },
        },
        mounted() {
            if (this.isSelectedMp.id === 2) {
                this.form.fields.nomenclature = this.nomenclature;
            }
            this.$store.commit('product/setSignalAlert', { field: 'price', value: this.showError });
        },
        methods: {
            setFields() {
                Object.keys(this.values).forEach(key => {
                    const value = this.getValue(key);
                    if (this.isSelectedMp.id === 2) {
                        this.setValuesForWb();
                        this.originalPrice = this.calcNewPrice;
                    } else {
                        this.setValue(value, key);
                        this.originalPrice = this.form.fields.price;
                    }
                });
            },
            async setValuesForWb() {
                try {
                    let nomenclatures = this.dataWildberries.nomenclatures;

                    if (this.dataWildberries.data.data_nomenclatures) {
                        nomenclatures = this.dataWildberries.data.data_nomenclatures;
                    }
                    const dataPrice = nomenclatures[this.index];

                    const { price, discount, promocode } = dataPrice;

                    this.form.fields.price = price;
                    this.form.fields.discount = discount;
                    this.form.fields.promocode = promocode;
                } catch (error) {
                    console.error(error);
                }
            },
        },
    };
</script>

<style lang="scss" scoped>
    .badges-price {
        display: flex;
        margin-bottom: 26px;

        &__item {
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 58px;
            padding: 0 12px;
            border-radius: $min-border-radius;
            background: $base-100;

            &--additional {
                background-color: #f9f4ff;
            }
        }

        &__title {
            font-size: 14px;
            font-weight: 500;
            color: $base-700;
        }

        &__price {
            font-size: 20px;
            line-height: 1;
            font-weight: 500;
            color: $base-900;
        }
    }

    .calc {
        margin-left: 10px;
        font-size: 12px;
        color: $base-700;
    }
</style>

<style lang="scss" module>
    .Form {
        display: grid;
        grid-template-rows: none;
        grid-gap: 1rem;

        &.FormWBTop {
            grid-template-columns: repeat(4, 1fr) 2.1fr;
        }

        &.FormWBPrice {
            grid-template-columns: repeat(auto-fit, minmax(14.2rem, 20rem));

            @include respond-to(md) {
                grid-template-columns: repeat(auto-fit, minmax(21rem, 1fr));
            }
        }

        &
            :global(.v-select.v-text-field--outlined:not(.v-text-field--single-line).v-input--dense
                .v-select__selections) {
            padding: 0;
        }
    }
</style>
