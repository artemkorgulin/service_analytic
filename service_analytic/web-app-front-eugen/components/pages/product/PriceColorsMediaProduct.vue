<template>
    <div
        :class="$style.PriceColorsMediaProduct"
        class="product-content__panel product-content__panel--full-width"
    >
        <VTabsItems v-model="getActiveOptionIndex">
            <VTabItem v-for="(option, index) in getTabs" :key="index" :eager="true">
                <NomenclatureBase
                    :ref="'nomenclatureBaseProduct-' + index"
                    :values="getTabs[index].data"
                    :nomenclature="{ index, name: option.name }"
                    :variations="getTabs[index].variations"
                />

                <price-product
                    :ref="'priceProduct-' + index"
                    :index="index"
                    :values="getTabs[index].price"
                    :nomenclature="{ index, name: option.name }"
                />

                <SizeProduct
                    v-if="marketplaceSlug === 'wildberries' && getTabs[index].variations.length > 1"
                    :ref="'sizeProduct-' + index"
                    :variations="getTabs[index].variations"
                />
            </VTabItem>
        </VTabsItems>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex';

    export default {
        name: 'PriceColorsMediaProduct',
        props: {
            options: {
                required: true,
                type: Array,
            },
        },
        data() {
            return {
                articulLocal: null,
                model: {
                    id: null,
                    name: null,
                    vendorCode: null,
                    additionalColors: [],
                    package: [],
                    price: null,
                    discount: null,
                    promocode: null,
                    images: [],
                    images360: [],
                    youtubecodes: null,
                    baseColor: null,
                },
            };
        },
        computed: {
            ...mapGetters({ marketplaceSlug: 'getSelectedMarketplaceSlug' }),
            ...mapGetters('product', ['getActiveOptionIndex']),
            getOptions() {
                return this.options || [];
            },
            getTabs() {
                const options = this.getOptions;
                const data = [];

                options.forEach((tab, index) => {
                    const newOption = { ...this.model, ...tab };

                    const newOptionVariations = this.getNomenclatureVariations(
                        newOption.variations,
                        {
                            name: newOption.name,
                            index,
                        }
                    );
                    data.push({
                        name: newOption.name,
                        id: newOption.id,
                        data: {
                            name: newOption.name,
                            id: newOption.id,
                            vendorCode: newOption.vendorCode,
                            baseColor: newOption.baseColor,
                            nmId: newOption.nmId,
                            additionalColors: newOption.additionalColors,
                            nomenclature: {
                                name: newOption.name,
                                index,
                            },
                        },
                        price: {
                            price: newOption.price,
                            discount: newOption.discount,
                            promocode: newOption.promocode,
                            nmId: newOption.nmId,
                            nomenclature: {
                                name: newOption.name,
                                index,
                            },
                        },
                        media: {
                            images: newOption.images,
                            images360: newOption.images360,
                            youtubecodes: newOption.youtubecodes,
                            nmId: newOption.nmId,
                            nomenclature: {
                                name: newOption.name,
                                index,
                            },
                        },
                        statistics: {
                            categoriesNumber: newOption.categoriesNumber,
                            bestPosition: newOption.bestPosition,
                            rating: newOption.rating,
                        },
                        variations: newOptionVariations,
                    });
                });

                return data;
            },
        },
        watch: {
            getActiveOptionIndex(value) {
                this.$store.commit('product/setField', { field: 'selectedWbNmID', value });
            },
        },
        async created() {
            this.$store.commit('product/setField', {
                field: 'selectedWbNmID',
                value: this.getActiveOptionIndex,
            });
            this.fetchDictionaryWildberries('/colors');
        },
        methods: {
            ...mapActions({
                fetchDictionaryWildberries: 'product/fetchDictionaryWildberries',
            }),
            getTabKey(key, index) {
                return `nomenclature.${index}.${key}`;
            },
            async getRefs() {
                const result = [];
                for (const ref in this.$refs) {
                    const formLvl1 = this.$refs[ref][0];
                    if (formLvl1.form) {
                        result.push(formLvl1.getInputs());
                    } else if (formLvl1.$refs) {
                        for (const subRef in formLvl1.$refs) {
                            const formLvl2 = formLvl1.$refs[subRef][0];
                            if (formLvl2.form) {
                                result.push(formLvl2.getInputs());
                            }
                        }
                    }
                }
                return Promise.all([...result]);
            },
            getNomenclatureVariations(arr, nomenclature) {
                try {
                    return arr.reduce((acc, current, index) => {
                        const sizeValueVal = current.addin.find(el => el.type === 'Рос. размер')
                            ?.params?.[0].value;
                        const sizeValue = sizeValueVal ? [sizeValueVal] : [];
                        acc[index] = {
                            barcode: current.barcodes[0],
                            sizeName:
                                current.addin.find(el => el.type === 'Размер')?.params?.[0].value ||
                                '',
                            sizeValue,
                            price:
                                current.addin.find(el => el.type === 'Розничная цена')?.params?.[0]
                                    .count || '',
                            nomenclature,
                            id: current.id,
                            chrtId: current.chrtId,
                        };

                        return acc;
                    }, []);
                } catch (error) {
                    console.error(error);
                    return [];
                }
            },
        },
    };
</script>

<style lang="scss">
    .product-options__price--pt {
        padding-top: 1rem;
    }
</style>

<style lang="scss" module>
    .PriceColorsMediaProduct {
        & :global(.product-options__price-value) {
            @include respond-to(md) {
                font-size: 1rem;
            }
        }
    }

    .AddTabBtn {
        margin-right: 16px;

        &:global(.v-btn) {
            &:global(.v-size--default) {
                min-width: 2.5rem;
                padding: 0;
            }
        }
    }

    .Active {
        background-color: transparent;

        &:before {
            content: none;
        }

        & .TabBtn {
            border-color: $primary-500;
            box-shadow: 0 0.2rem 1rem rgba(95, 69, 255, 0.16);
        }
    }

    .TabsWrapper {
        & :global(.v-item-group) {
            height: 60px;
            border-bottom: 1px $color-gray-light solid;
        }

        & :global(.v-tab) {
            align-items: flex-start;
            padding: 0;
        }
    }

    .Tab {
        height: fit-content;
        margin-right: 16px;
    }

    .Label {
        display: flex;

        .LabelRequired {
            color: $color-pink-dark;
        }
    }

    .TabBtn {
        .TabBtnWarning {
            width: 0.75rem;
            margin-right: 0.5rem;
        }

        & ~ .TabBtnDel {
            display: none;
        }
    }

    .TabBtnCaption {
        font-weight: bold;
        font-size: 1rem;
        letter-spacing: normal;
        color: $color-main-font;
    }

    .TabBtnDel {
        width: 1rem;
        height: 1rem;
        margin-left: 0.5rem;

        & :global(.v-btn__content) {
            width: 100%;
            height: 100%;
        }
    }

    .Form {
        display: grid;
        grid-template-rows: none;
        grid-gap: 1rem;

        &.FormWBTop {
            grid-template-columns: repeat(4, 1fr) 2.1fr;
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
</style>
