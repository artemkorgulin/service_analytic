<template>
    <VMenu offset-y open-on-hover top :nudge-bottom="-10">
        <template #activator="{ on, attrs }">
            <div class="product-table__product-view border-hover" v-bind="attrs" v-on="on">
                <span>{{ position || 0 }}</span>

                <p :class="{ 'minus-rating': triggers.position < 0 }">{{ triggers.position }}</p>
                <template v-if="triggers.position">
                    <template v-if="triggers.position > 0">
                        <img src="/images/product-view-icons__rating.svg" alt="" />
                    </template>
                    <template v-else>
                        <img
                            class="negative-position"
                            src="/images/product-view-icons__rating.svg"
                            alt=""
                        />
                    </template>
                </template>
            </div>
        </template>
        <VCard width="500">
            <div v-if="displayOptionBig" class="custom-popover__title-box">
                <span class="custom-popover__title">Позиции товара по категориям</span>
            </div>
            <div class="custom-popover__box-view">
                <div class="product-table__product-view">
                    <img src="/images/product-view-icons.svg" alt="" />
                    <span>{{ position || 0 }}</span>

                    <p :class="{ 'minus-rating': triggers.position < 0 }">
                        {{ triggers.position }}
                    </p>

                    <template v-if="triggers.position">
                        <template v-if="triggers.position > 0">
                            <img src="/images/product-view-icons__rating.svg" alt="" />
                        </template>
                        <template v-else>
                            <img
                                class="negative-position"
                                src="/images/product-view-icons__rating.svg"
                                alt=""
                            />
                        </template>
                    </template>
                </div>

                <span class="custom-popover__text">{{ categoryName }}</span>
            </div>
        </VCard>
    </VMenu>
</template>

<script>
    import { mapGetters } from 'vuex';
    export default {
        name: 'ProductsListPositionsByCategories',
        props: {
            position: {
                type: undefined,
                required: true,
            },
            triggers: {
                type: [Object, Array],
                required: true,
            },
            categoryName: {
                type: String,
                required: true,
            },
        },
        computed: {
            ...mapGetters({
                displayOptionBig: 'products/GET_DISPLAY_OPTION',
            }),
        },
    };
</script>

<style lang="scss" module>
    //
</style>
