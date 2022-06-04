<template>
    <div class="product__sortable">
        <div class="product__checkbox">
            <BaseCheckbox :value="selectAll" auto-size @click="$emit('selectAll')" />
        </div>
        <div class="product__show-nom"></div>
        <div class="product__img"></div>
        <div
            v-for="item in items"
            :key="item.name"
            outlined
            class="filter-btn"
            :disabled="pending.show"
            :class="[
                {
                    bottom: sort.sortBy === item.value && sort.sortType === 'desc',
                    top: sort.sortBy === item.value && sort.sortType === 'asc',
                },
                `product__${item.class}`,
            ]"
            @click="setSort(item.value, marketplaceSlug)"
        >
            <span class="filter-btn__text">{{ item.name }}</span>

            <icon-loading
                class="product__sortable-icon"
                :pending="pending.show && pending.type === 'sort' && sort.sortBy === item.value"
            >
                <span class="filter-btn__icon">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="#710BFF">
                        <path
                            fill-rule="evenodd"
                            clip-rule="evenodd"
                            d="M11.7655 8.23435C11.9155 8.38437 11.9998 8.58782 11.9998 8.79995C11.9998 9.01208 11.9155 9.21553 11.7655 9.36555L8.56554 12.5656C8.41552 12.7155 8.21207 12.7998 7.99994 12.7998C7.78781 12.7998 7.58437 12.7155 7.43434 12.5656L4.23434 9.36555C4.15793 9.29175 4.09699 9.20348 4.05506 9.10588C4.01313 9.00827 3.99107 8.9033 3.99014 8.79707C3.98922 8.69085 4.00946 8.58551 4.04969 8.48719C4.08991 8.38887 4.14931 8.29955 4.22443 8.22444C4.29954 8.14932 4.38886 8.08992 4.48718 8.04969C4.5855 8.00947 4.69084 7.98923 4.79706 7.99015C4.90329 7.99107 5.00826 8.01314 5.10587 8.05507C5.20347 8.097 5.29175 8.15794 5.36554 8.23435L7.19994 10.0688V3.99995C7.19994 3.78778 7.28423 3.5843 7.43426 3.43427C7.58429 3.28424 7.78777 3.19995 7.99994 3.19995C8.21212 3.19995 8.4156 3.28424 8.56563 3.43427C8.71566 3.5843 8.79994 3.78778 8.79994 3.99995V10.0688L10.6343 8.23435C10.7844 8.08437 10.9878 8.00012 11.1999 8.00012C11.4121 8.00012 11.6155 8.08437 11.7655 8.23435Z"
                        />
                    </svg>
                </span>
            </icon-loading>
        </div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';
    import { debounce } from '~utils/helper.utils';
    import IconLoading from '~/components/common/IconLoading.vue';

    export default {
        name: 'Sortable',
        components: { IconLoading },
        props: {
            selectAll: {
                type: Boolean,
                default: false,
            },
        },
        data() {
            return {
                lists: {
                    ozon: [
                        {
                            name: 'Название',
                            value: 'name',
                            class: 'name',
                        },
                        {
                            name: 'Бренд',
                            value: 'rating',
                            class: 'brand',
                            // notSort: true,
                        },
                        {
                            name: 'Цена',
                            value: 'price',
                            class: 'price',
                        },
                        {
                            name: 'Оптимизация',
                            value: 'optimization',
                            class: 'optimization',
                        },
                        {
                            name: 'Рейтинг',
                            value: 'rating',
                            class: 'rating',
                        },
                        {
                            name: 'Артикул',
                            value: 'rating',
                            class: 'artikul',
                            notSort: true,
                        },
                        {
                            name: 'Статус',
                            value: 'status_id',
                            class: 'status',
                        },
                    ],
                    wildberries: [
                        {
                            name: 'Название',
                            value: 'name',
                            class: 'name',
                        },
                        {
                            name: 'Рейтинг',
                            value: 'rating',
                            notSort: true,
                        },
                        {
                            name: 'Цена',
                            value: 'price',
                            class: 'price',
                        },
                        {
                            name: 'Оптимизация',
                            value: 'characteristics',
                            class: 'optimization',
                        },
                        {
                            name: 'Статус',
                            value: 'status_id',
                            class: 'status',
                        },
                    ],
                },
                currentMarketplace: '',
            };
        },
        computed: {
            ...mapGetters({
                sort: 'products/GET_SORT',
                pending: 'products/GET_PENDING',
                marketplaceSlug: 'getSelectedMarketplaceSlug',
            }),
            items() {
                return this.lists[this.currentMarketplace];
            },
        },
        mounted() {
            this.currentMarketplace = this.marketplaceSlug;
        },
        methods: {
            setSort(value, marketplace) {
                this.currentMarketplace = marketplace;
                const direction = this.sort.sortBy !== value ? null : this.sort.sortType;
                let newDirection;

                switch (direction) {
                    case 'desc':
                        newDirection = 'asc';
                        break;
                    case 'asc':
                        newDirection = null;
                        break;
                    default:
                        newDirection = 'desc';
                }

                this.$store.commit('products/SET_SORT', {
                    sortType: newDirection,
                    sortBy: value,
                });
                debounce({
                    time: 250,
                    collBack: () => {
                        this.$store.dispatch('products/LOAD_PRODUCTS', {
                            type: 'sort',
                            reload: false,
                            marketplace: this.marketplaceSlug,
                        });
                    },
                });
            },
        },
    };
</script>

<style lang="scss">
    .product__sortable-icon {
        margin: 0 -2px 0 5px;
    }
</style>
