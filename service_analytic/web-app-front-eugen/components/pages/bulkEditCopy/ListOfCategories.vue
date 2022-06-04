<template>
    <div class="bc-product-list">
        <div class="bc-product-list__search-field">
            <SvgIcon
                name="outlined/search"
                style="height: 16px"
                color="#C8CFD9"
                class="search-input__btn"
            />
            <input v-model="pageSetting.search" type="text" placeholder="Поиск" />
        </div>
        <table class="bc-product-list__table bc-table">
            <tr
                v-for="category in categories"
                :key="category.id"
                @click="
                    $emit(
                        'setCategory',
                        [{ id: category.id, name: category.name }, category.name][isSelMpIndex]
                    )
                "
            >
                <td width="50%">
                    {{ category.name }}
                </td>
                <td width="50%" class="parent-name">
                    {{ category.parent_name }}
                </td>
            </tr>
            <tr v-if="pageLoading">
                <td colspan="100%" class="text-center pt-4 pb-4">
                    <v-progress-circular
                        indeterminate
                        size="32"
                        color="primary"
                    ></v-progress-circular>
                </td>
            </tr>
            <tr v-if="!pageLoading && !categories.length">
                <td colspan="100%" class="text-center pt-4 pb-4">Не найдено ни одной категории</td>
            </tr>
        </table>
    </div>
</template>

<script>
    import _l from 'lodash';
    import { mapGetters } from 'vuex';
    import lazyLoading from '~/assets/js/mixins/lazyLoadingPag.mixin';
    export default {
        mixins: [lazyLoading],
        data() {
            return {
                categories: [],
            };
        },
        computed: {
            ...mapGetters(['isSelectedMp', 'isSelMpIndex']),

            params() {
                const params = {};

                for (const [key, value] of Object.entries(this.pageSetting)) {
                    if (value) {
                        params[_l.snakeCase(key)] = value;
                    }
                }

                return params;
            },
        },
        watch: {
            'pageSetting.search': {
                handler() {
                    this.debounceSearch();
                },
            },
        },
        async mounted() {
            this.debounceSearch = _l.debounce(this.searchByMatch, 500);
            this.defRequest = this.getAListOfCategories;
            await this.getAListOfCategories();
        },
        methods: {
            async getAListOfCategories() {
                /* eslint-disable */
                this.pageLoading = true;

                const urls = [
                    '/api/vp/v2/ozon/account-categories',
                    '/api/vp/v2/wildberries/account-categories',
                ];
                const topic = urls[this.isSelMpIndex];

                let { params } = this;

                if (params.search) {
                    params['query[search]'] = params.search;
                    delete params.search;
                }

                try {
                    const {
                        data: {
                            data: { data },
                            last_page,
                        },
                    } = await this.$axios.get(topic, { params });

                    this.categories = [...this.categories, ...data];
                    this.lastPage = last_page;
                } catch (error) {
                    console.error(error);
                } finally {
                    this.pageLoading = false;
                }
            },
            searchByMatch() {
                this.pageSetting.page = 1;
                this.categories = [];
                this.getAListOfCategories();
            },
        },
    };
</script>

<style lang="scss" scoped>
    .parent-name {
        color: $base-700;
    }
</style>
