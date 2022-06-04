<template>
    <VBtn outlined @click="openCategoriesModal">
        <template v-if="selectedCategories.length">
            <div>{{ title }}</div>
        </template>
        <template v-else>Категории</template>
    </VBtn>
</template>

<script>
    import { mapGetters } from 'vuex';

    export default {
        name: 'ProductsCategories',
        computed: {
            ...mapGetters('category', {
                selectedCategories: 'GET_SELECTED_CATEGORIES',

            }),
            title() {
                const number = this.selectedCategories.length;
                const selected = this.$options.filters.plural(number, [
                    'Выбрана',
                    'Выбрано',
                    'Выбрано',
                ]);
                const categories = this.$options.filters.plural(number, [
                    'категория',
                    'категории',
                    'категорий',
                ]);
                return `${selected} ${number} ${categories}`;
            },
        },
        methods: {
            openCategoriesModal() {
                return this.$modal.open({
                    component: 'ModalSelectCategories',
                });
            },
        },
    };
</script>
