<template>
    <BaseDialog v-model="isShow" width="440px">
        <VCard :class="$style.ModalSelectCategories">
            <div :class="$style.ModalHeader">
                <div>
                    <SearchInput
                        v-model="search"
                        :class="$style.SearchInput"
                        color="#710bff"
                        background-color="#f9f9f9"
                        label="Введите категорию"
                        outline-light
                    />
                    <IconLoading :pending="pending" size="20px" />
                </div>

                <div v-if="categories.length" class="pt-6" style="gap: 16px">
                    <VCheckbox
                        :input-value="allCategoriesChecked"
                        class="ma-0"
                        label="Выбрать всё"
                        color="primary"
                        hide-details
                        @click="checkAllCategories"
                    />
                </div>
            </div>

            <div :class="$style.ModalBody">
                <template v-if="categories.length">
                    <VTreeview
                        ref="categoriesList"
                        v-model="values"
                        selectable
                        :items="categories"
                    />
                </template>

                <template v-else>
                    <span class="base-txt plug-result-search">
                        {{ 'Категорий не найдено' }}
                    </span>
                </template>
            </div>

            <div :class="$style.ModalFooter">
                <span v-if="values.length" :class="$style.Selected">
                    Выбрано: {{ values.length }}
                </span>
                <VBtn
                    :class="$style.buttonAlwaysRight"
                    color="primary"
                    outlined
                    :disabled="!values.length || pending"
                    @click="selectCategories"
                >
                    Выбрать
                </VBtn>
            </div>
        </VCard>
    </BaseDialog>
</template>

<script>
    import { mapGetters, mapActions } from 'vuex';
    import { debounce } from '~utils/helper.utils';
    import IconLoading from '~/components/common/IconLoading.vue';

    export default {
        name: 'ModalSelectCategories',
        components: {
            IconLoading,
        },
        data() {
            return {
                isShow: true,
            };
        },
        computed: {
            ...mapGetters(['isSelectedMp']),
            ...mapGetters({
                categories: 'category/GET_CATEGORIES',
                pending: 'category/GET_PENDING',
                isResult: 'category/IS_RESULT',
                products: 'products/GET_PRODUCTS',
                selectedCategories: 'category/GET_SELECTED_CATEGORIES',
                stateSearch: 'category/GET_SEARCH',
                marketplaceSlug: 'getSelectedMarketplaceSlug',
            }),
            search: {
                get() {
                    return this.stateSearch;
                },
                set(val) {
                    this.$store.commit('category/SET_SEARCH', val);

                    debounce({
                        time: 1000,
                        collBack: () => {
                            this.$store.dispatch('category/LOAD_CATEGORIES', this.marketplaceSlug);
                        },
                    });
                },
            },
            values: {
                get() {
                    return this.selectedCategories || [];
                },
                set(val) {
                    return this.$store.commit('category/SET_STATE_KEY', {
                        key: 'selectedCategories',
                        value: val,
                    });
                },
            },
            allCategoriesChecked() {
                for (let i = 0; i < this.categories.length; i++) {
                    if (this.values.findIndex(el => el === this.categories[i].id) < 0) {
                        return false;
                    }
                }
                return true;
            },
        },
        created() {
            this.fetchCategories();
        },
        methods: {
            ...mapActions('category', ['fetchCategories']),
            selectCategories() {
                this.$store.commit('products/SET_CATEGORY', this.values);
                this.$store
                    .dispatch('products/LOAD_PRODUCTS', {
                        type: 'common',
                        reload: false,
                        marketplace: this.marketplaceSlug,
                    })
                    .then(() => {
                        this.$modal.close();
                    });
            },
            checkAllCategories() {
                if (this.allCategoriesChecked) {
                    this.values = [];
                } else {
                    this.values = this.categories.map(el => el.id);
                }
            },
        },
    };
</script>

<style lang="scss">
    .plug-result-search {
        padding: 1.75rem 2.5rem;
    }
</style>

<style lang="scss" module>
    .ModalSelectCategories {
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding: 16px 0;
    }

    .ModalHeader {
        position: relative;
        padding: 0 16px;

        & :global(.custom-loading) {
            position: absolute;
            top: 50%;
            right: 0;
            z-index: 0;
            margin-right: 40px;
            transform: translateY(-50%);
            pointer-events: none;
        }
    }

    .ModalBody {
        overflow: hidden auto;
        display: flex;
        max-height: 440px;
        padding: 20px 20px 20px 0;

        &::-webkit-scrollbar {
            width: 4px;
            background: transparent;
        }

        &::-webkit-scrollbar-thumb {
            border-radius: 16px;
            background: $color-purple-primary;
        }

        & :global(.v-treeview) {
            width: 100%;

            & :global(.v-treeview-node.v-treeview-node--leaf) {
                border-top: 1px solid $color-gray-light;
            }
        }
    }

    .ModalFooter {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        padding: 0 16px;
    }

    .SearchInput {
        width: 100%;
    }

    .Selected {
        font-weight: 500;
        color: rgba(113, 11, 255, 1);
    }

    .buttonAlwaysRight {
        margin-left: auto;
    }

    .TreeWrapper {
        padding-left: 0;
    }
</style>
