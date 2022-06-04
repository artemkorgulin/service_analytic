<template>
    <div class="categories">
        <div v-if="selectCategoriesFlag" class="categories__select">
            <SelectCategories
                @click="setCategoriesData(['selectCategoriesFlag', false])"
            ></SelectCategories>
        </div>
        <div v-else class="categories__main">
            <CategoriesMain />
        </div>
    </div>
</template>

<script>
    import SelectCategories from '~/components/pages/analytics/categories/selectCategories.vue';
    import CategoriesMain from '~/components/pages/analytics/categories/categoriesMain.vue';
    import { mapState, mapMutations } from 'vuex';
    export default {
        components: {
            SelectCategories,
            CategoriesMain,
        },
        computed: {
            ...mapState('categories-analitik', ['selectCategoriesFlag']),
        },
        created() {
            if (this.$route.query?.category) {
                this.setCategoriesData(['selectCategoriesFlag', true]);
            }
        },
        methods: {
            ...mapMutations('categories-analitik', ['setCategoriesData']),
        },
    };
</script>

<style lang="scss" scoped>
    .categories {
        display: block;
        min-height: calc(100vh - 64px);
        background: #f9f9f9;

        &__select {
            height: calc(100vh - 128px);
            padding: 32px;
        }
    }
</style>
