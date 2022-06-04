<template>
    <div>
        <span>
            <div class="product-price">
                <div class="product-price__price price">
                    <span class="price__inner-el">{{ meanRange.price }}</span>
                </div>
                <div
                    v-if="meanRange.price !== meanRange.oldPrice"
                    class="product-price__old-price old-price"
                >
                    <span class="old-price__inner-el">{{ meanRange.oldPrice }}</span>
                </div>
            </div>
        </span>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';
    import productsMixin from '~mixins/products.mixin';
    import formatText from '~mixins/formatText.mixin';

    export default {
        name: 'ProductsListPrice',
        mixins: [productsMixin, formatText],
        props: {
            item: {
                type: Object,
                required: true,
            },
            range: {
                type: [Object, Boolean],
                default: null,
            },
            isOption: {
                type: Boolean,
                default: false,
            },
        },
        computed: {
            ...mapGetters(['isSelectedMp']),
            ...mapGetters({
                displayOptionBig: 'products/GET_DISPLAY_OPTION',
                productHasOptions: 'isProductHasOptions',
            }),

            computedOldPrice() {
                let old = null;
                let converted_old = '';
                if (this.item.old_price && this.item.old_price !== '0.00') {
                    old = this.item.old_price.toString().replace(/,/g, '');
                    converted_old = Number(old);
                }

                return converted_old.toLocaleString().replace(/,/g, ' ');
            },

            computedPrice() {
                const price = this.item.price && this.item.price.toString().replace(/,/g, '');
                let converted_old = Number(price);
                if (
                    this.item.nomenclatures &&
                    this.item.nomenclatures[0]?.discount &&
                    this.item.nomenclatures[0]?.discount !== 0
                ) {
                    converted_old *= 1 - (100 - this.item.nomenclatures[0].discount) / 100;
                }

                return Math.floor(converted_old).toLocaleString().replace(/,/g, ' ');
            },
            isRange() {
                return this.range && Object.values(this.range).every(value => Boolean(value));
            },
            meanRange() {
                const format = value => value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
                // eslint-disable-next-line no-extra-parens
                const getPrice = (price, disc) => Math.floor(price - (price * disc) / 100);

                try {
                    if (this.isSelectedMp.id === 1) {
                        const formatOzon = value =>
                            format(Number(value.toLocaleString().replace(/,/g, '')).toFixed(0));
                        const price = formatOzon(this.item.price);
                        const oldPrice = formatOzon(this.item.old_price);
                        return {
                            price: `${price} ₽`,
                            oldPrice: `${oldPrice == 0 ? price : oldPrice} ₽`,
                        };
                    } else if (!this.isOption) {
                        const disc = this.item.nomenclatures[0].discount;
                        const rangeValue = Object.values(this.range);

                        if (rangeValue[0] === rangeValue[1]) {
                            return {
                                price: `${format(getPrice(rangeValue[0], disc))} ₽`,
                                oldPrice: `${format(rangeValue[0])} ₽`,
                            };
                        } else {
                            const oldPrice = rangeValue.map(_ => `${format(_)} ₽`).join(' - ');
                            return {
                                price: oldPrice,
                                oldPrice: oldPrice,
                            };
                        }
                    } else {
                        const disc = this.item.discount || 0;
                        const price = getPrice(this.item.price, disc);
                        return {
                            price: `${format(price)} ₽`,
                            oldPrice: `${format(this.item.price)} ₽`,
                        };
                    }
                } catch {
                    return {
                        price: 0,
                        oldPrice: 0,
                    };
                }
            },
        },
    };
</script>

<style lang="scss" scoped>
    .product-price {
        display: flex;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 6px;
        font-size: 16px;
        font-weight: 500;

        &__old-price {
            position: relative;
            overflow: hidden;
            font-size: 14px !important;
            color: $neutral-600;

            span {
                text-decoration: line-through;
            }

            @include respond-to(md) {
                font-size: 12px !important;
            }
        }

        @include respond-to(md) {
            font-size: 12px;
        }
    }

    .old-price {
        color: $color-gray-light-100;

        &__inner-el {
            margin: 0 !important;
            padding-right: 10px;
        }
    }
</style>
