<template>
    <div class="option option-wrap">
        <div
            :key="options.id"
            :class="['option', displayOptionBig ? 'option_big' : 'option_small']"
        >
            <ProductsListProductImg
                :link="'product/' + itemId"
                :img="getPhoto"
                :no-border="!!displayOptionBig"
                single-product
                :class="[displayOptionBig ? 'option_big__picture' : 'option_small__picture']"
            />
            <div :class="['infoPanel', displayOptionBig ? 'infoPanel_big' : 'infoPanel_small']">
                <div class="infoPanel_item nameLink">
                    <span class="infoPanel_item__color">
                        {{ getColor || options.vendorCode }}
                    </span>
                    <ProductsListUrl :url="getUrl" :sku="options.nmId" />
                </div>
                <ProductsListPrice
                    class="mt-3"
                    :item="{
                        price: nom.price || price,
                        discount: options.discount,
                    }"
                    is-option
                />
            </div>
        </div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';

    export default {
        name: 'ProductsListOptionsItem',
        props: {
            options: {
                type: Object,
                default: () => ({}),
            },
            price: {
                type: [String, Number],
                default: '',
            },
            itemId: {
                type: [String, Number],
                required: true,
            },
            nom: {
                type: Object,
                default: () => ({}),
            },
        },
        computed: {
            ...mapGetters({
                displayOptionBig: 'products/GET_DISPLAY_OPTION',
            }),
            getPhoto() {
                return this.options.addin.find(item => item.type === 'Фото')?.params[0].value;
            },
            getColor() {
                return this.options.addin.find(item => item.type === 'Основной цвет')?.params[0]
                    .value;
            },
            getUrl() {
                return `https://www.wildberries.ru/catalog/${this.options.nmId}/detail.aspx`;
            },
        },
    };
</script>

<style scoped lang="scss">
    .option-wrap {
        border: 1px solid $color-gray-light;
    }

    .option {
        display: grid;
        border-radius: 0.5rem;

        &_big {
            grid-template-columns: 7.5rem auto;
            grid-gap: 0.5rem;
            padding: 1rem;

            &__picture {
                height: 7.5rem;
            }
        }

        &_small {
            align-items: center;
            grid-template-columns: 2.5rem auto;
            grid-gap: 1rem;
            min-width: 2.5rem;
            padding: 0.5rem;

            &__picture {
                height: 2.5rem;
            }
        }
    }

    .infoPanel {
        // display: grid;

        // &_big {
        //     grid-template-columns: auto;
        //     grid-template-rows: 1.7rem 1.5rem auto;
        //     grid-gap: 0.5rem;
        // }

        // &_small {
        //     grid-template-columns: 1fr;
        //     grid-auto-flow: column;
        //     grid-gap: 1rem;

        //     &__item {
        //         &-nameLink {
        //             grid-column: 2 / 3;
        //             grid-row: 1 / 2;
        //         }
        //     }
        // }
    }

    .infoPanel_item {
        display: flex;
        align-items: center;

        & > * {
            margin-right: 0.25rem;
        }

        &__color {
            margin-right: 8px;
            padding: 4px 16px;
            border-radius: 4px;
            background: #f9f9f9;
            font-size: 14px;
        }
    }

    .optionName {
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        background-color: $base-400;
        font-size: 0.875rem;
        line-height: 1.3;
        color: $color-gray-dark-800;
    }
</style>
