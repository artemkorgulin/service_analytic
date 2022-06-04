<template>
    <div
        :class="[
            $style.ProductsListProductImg,
            { [$style.WrapperBig]: productHasOptions && displayOptionBig && !singleProduct },
            { [$style.WrapperSmall]: productHasOptions && !displayOptionBig && !singleProduct },
        ]"
    >
        <NuxtLink
            :to="link"
            :class="[$style.Link, { [$style.LinkNoBorder]: noBorder || !productHasOptions }]"
        >
            <img :src="img" alt="" />
            <span
                v-if="showNumber && productHasOptions && variantsCount"
                :class="[$style.Number, displayOptionBig ? $style.NumberBig : $style.NumberSmall]"
            >
                {{ variantsCount }}
            </span>
        </NuxtLink>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex';
    export default {
        name: 'ProductsListProductImg',
        props: {
            link: {
                type: String,
                required: true,
            },
            img: {
                type: null,
                required: true,
            },
            showNumber: {
                type: Boolean,
                default: false,
            },
            noBorder: {
                type: Boolean,
                default: false,
            },
            singleProduct: {
                type: Boolean,
                default: false,
            },
            variantsCount: {
                type: Number,
                default: 0,
            },
        },
        data() {
            return {
                // expanded: false,
            };
        },
        computed: {
            ...mapGetters({
                getSelectedProducts: 'products/GET_ITEMS_SELECTED',
                displayOptionBig: 'products/GET_DISPLAY_OPTION',
                productHasOptions: 'isProductHasOptions',
            }),
        },
        methods: {
            ...mapActions({
                selectProduct: 'products/SELECT_PRODUCT',
                deselectProduct: 'products/DESELECT_PRODUCT',
            }),
        },
    };
</script>

<style lang="scss" module>
    .ProductsListProductImg {
        position: relative;
        max-height: 100%;
        text-align: center;
        cursor: pointer;

        & .Link {
            display: block;
            width: 100%;
            min-width: 40px;
            height: 100%;
            padding: 4px;
            border-radius: 4px;

            & img {
                display: inline-block;
                max-width: 100%;
                max-height: 100%;
                object-fit: contain;
            }

            &.LinkNoBorder {
                border-color: transparent;
            }
        }

        &.WrapperBig {
            //padding-right: 1rem;
        }

        &.WrapperSmall {
            padding-right: 2rem;
        }
    }

    .Number {
        position: absolute;
        top: 50%;
        right: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        border: 1px $color-gray-light-100 solid;
        background-color: $color-gray-light;
        font-weight: bold;
        color: $black;
        transform: translate(50%, -50%);
    }

    .NumberBig {
        width: 40px;
        height: 40px;
        font-size: 16px;
        line-height: 1.5;
    }

    .NumberSmall {
        width: 24px;
        height: 24px;
        font-size: 12px;
        line-height: 1.5;
    }
</style>
