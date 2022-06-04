<template>
    <div
        :class="[
            $style.SelectOrExpand,
            { [$style.WrapperExpandableBig]: productHasOptions && displayOptionBig },
            { [$style.WrapperExpandableSmall]: productHasOptions && !displayOptionBig },
        ]"
    >
        <div
            v-if="!filter"
            :class="$style.CheckboxWrapper"
            @click="productSelected = !productSelected"
        >
            <BaseCheckbox :value="productSelected" auto-size />
        </div>
        <div v-else :class="$style.CheckboxWrapper" @click="$emit('click')">
            <BaseCheckbox :value="selectAll" auto-size />
        </div>
        <div
            v-if="!filter && productHasOptions"
            :class="[
                $style.Expand,
                { [$style.ExpandSmall]: !displayOptionBig },
                { [$style.ExpandSmallExpanded]: !displayOptionBig && expanded },
            ]"
            @click="$emit('switch-expanded')"
        >
            <SvgIcon
                name="outlined/chevronDown"
                :class="[$style.ExpandIcon, { [$style.ExpandIconExpanded]: expanded }]"
            />
        </div>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex';
    export default {
        name: 'SelectOrExpand',
        props: {
            filter: Boolean,
            selectAll: Boolean,
            id: {
                type: Number,
            },
            expanded: {
                type: Boolean,
                required: true,
            },
        },
        data() {
            return {
                //
            };
        },
        computed: {
            ...mapGetters({
                getSelectedProducts: 'products/GET_ITEMS_SELECTED',
                displayOptionBig: 'products/GET_DISPLAY_OPTION',
                productHasOptions: 'isProductHasOptions',
            }),
            productSelected: {
                get() {
                    return this.getSelectedProducts.includes(this.id);
                },
                set(val) {
                    if (val) {
                        this.selectProduct(this.id);
                    } else {
                        this.deselectProduct(this.id);
                    }
                },
            },
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
    .SelectOrExpand {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        width: 100%;
        height: 100%;
        padding-top: 0.25rem;
        border-radius: 4px;
    }

    .WrapperExpandableBig {
        flex-direction: column;
        background-color: $color-gray-light;
    }

    .WrapperExpandableSmall {
        align-items: center;
    }

    .CheckboxWrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        //align-self: baseline;
        width: 32px;
        height: 32px;
    }

    .Expand {
        display: flex;
        align-items: flex-end;
        justify-content: center;
        flex-grow: 1;
        width: 2rem;
        height: 2rem;
        border-radius: 4px;
        background-color: $color-gray-light;
        cursor: pointer;
    }

    .ExpandSmall {
        align-items: flex-end;
        flex-grow: 0;
    }

    .ExpandSmallExpanded {
        height: 100%;
    }

    .ExpandIcon {
        width: 9px;
        margin-bottom: 7px;
        transition: transform 150ms ease-in;

        &:global(.icon.sprite-outlined) {
            width: 1rem;
            height: 1rem;
        }
    }

    .ExpandIconExpanded {
        transform: rotate(180deg);
    }
</style>
