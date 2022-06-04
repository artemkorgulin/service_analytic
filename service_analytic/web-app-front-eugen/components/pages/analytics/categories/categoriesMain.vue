<template>
    <div>
        <Page
            :title="title"
            :back-btn="backBtn"
            :back-btn-click="backBtnClick"
            :period="period"
            :period-change="changeSetDate"
        >
            <div class="categories-main__content">
                <div class="categories-main__select-cat">
                    <ul class="category-links">
                        <li
                            v-for="(item, index) in categoryPathArr"
                            :key="item"
                            class="category-links__item"
                            :class="{ disabled: disabledLevelCat.includes(index) }"
                        >
                            <span
                                class="category-links__title"
                                @click="setPathFromParent(item, index)"
                            >
                                {{ item }}
                            </span>
                            <v-menu
                                v-if="
                                    categoryMenu[index] &&
                                    categoryMenu[index].length &&
                                    !disabledLevelCat.includes(index)
                                "
                                bottom
                                left
                                offset-y
                                nudge-bottom="10"
                            >
                                <template #activator="{ on, attrs }">
                                    <v-btn
                                        x-small
                                        depressed
                                        class="pl-0 pr-0"
                                        style="min-width: 24px; min-height: 24px"
                                        v-bind="attrs"
                                        v-on="on"
                                    >
                                        <SvgIcon
                                            name="outlined/chevronDown"
                                            style="height: 14px"
                                        ></SvgIcon>
                                    </v-btn>
                                </template>
                                <v-list dense>
                                    <v-list-item
                                        v-for="subItem in categoryMenu[index]"
                                        :key="subItem"
                                        style="min-height: 30px"
                                        @click="setAlternatePath(subItem, index)"
                                    >
                                        <v-list-item-content
                                            class="pt-0 pb-0"
                                            style="font-size: 14px"
                                        >
                                            {{ subItem }}
                                        </v-list-item-content>
                                    </v-list-item>
                                </v-list>
                            </v-menu>
                            <span
                                v-if="categoryPathArr.length - 1 !== index"
                                class="category-links__slash"
                            >
                                /
                            </span>
                        </li>
                        <li v-if="selMenuForNextCat.length" class="category-links__item">
                            <v-menu bottom left offset-y nudge-bottom="10">
                                <template #activator="{ on, attrs }">
                                    <v-btn
                                        x-small
                                        depressed
                                        class="pl-0 pr-0"
                                        style="min-width: 24px; min-height: 24px"
                                        v-bind="attrs"
                                        v-on="on"
                                    >
                                        <SvgIcon
                                            :name="`outlined/${
                                                lastCategoryInserted ? 'chevronDown' : 'plus'
                                            }`"
                                            style="height: 14px"
                                        ></SvgIcon>
                                    </v-btn>
                                </template>
                                <v-list dense>
                                    <v-list-item
                                        v-for="subItem in selMenuForNextCat"
                                        :key="subItem"
                                        style="min-height: 30px"
                                        @click="setPath(subItem)"
                                    >
                                        <v-list-item-content
                                            class="pt-0 pb-0"
                                            style="font-size: 14px"
                                        >
                                            {{ subItem }}
                                        </v-list-item-content>
                                    </v-list-item>
                                </v-list>
                            </v-menu>
                        </li>

                        <li class="category-links__item ml-lg-2">
                            <a
                                class="wb-link d-flex align-center"
                                style="gap: 4px"
                                :href="`https://wildberries.ru/${lastCategoryUrl}`"
                                target="_blank"
                            >
                                Перейти в WB
                                <SvgIcon
                                    style="height: 16px"
                                    class="wb-link__icon"
                                    name="outlined/link"
                                />
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="categories-main__tabs">
                    <VTabs v-model="demoTab" class="se-tabs" dense>
                        <VTab v-ripple="false">Товары</VTab>
                        <VTab v-ripple="false">Подкатегории</VTab>
                        <VTab v-ripple="false">Ценовой анализ</VTab>
                    </VTabs>
                </div>
                <div v-if="firstLoading" class="categories-main__tabs-content">
                    <TabsProducts v-if="tab === 0"></TabsProducts>
                    <TabsCat v-if="tab === 1"></TabsCat>
                    <TabsPrices
                        v-if="tab === 2"
                        :load-data="loadAnalyticsCategoriesPrices"
                        :set-data-prices="setDataPrices"
                        :data-prices="dataPrices"
                        :start-price-range="startPriceRange"
                        :columns="pricesColums"
                        :prices="prices"
                        :is-load-prices="isLoadPrices"
                        :is-first-loading="firstLoadingPrice"
                    ></TabsPrices>
                </div>
                <div
                    v-else
                    class="page-loader d-flex align-center justify-center"
                    style="min-height: 400px"
                >
                    <v-progress-circular :size="40" color="primary" indeterminate />
                </div>
            </div>
        </Page>
    </div>
</template>

<script>
    import { mapMutations, mapGetters, mapState, mapActions } from 'vuex';

    import TabsProducts from '~/components/pages/analytics/categories/tabsProducts.vue';
    import TabsCat from '~/components/pages/analytics/categories/tabsCat.vue';
    import TabsPrices from '~/components/pages/analytics/categories/tabsPrices.vue';
    import Page from '~/components/ui/SeInnerPage';

    export default {
        components: {
            TabsProducts,
            TabsCat,
            TabsPrices,
            Page,
        },
        data: () => ({
            title: {
                isActive: true,
                text: 'Категории',
            },
            backBtn: {
                isActive: true,
                text: 'Назад',
            },
            lastWebIdSubjects: null,
            categoryMenu: [],
            categoryPathArr: [],
            lastCategoryInserted: false,
            selectedLastCategory: false,
            lastCategory: false,
            selMenuForNextCat: [],
            tab: 0,
            demoTab: 0,
            checkboxTovar: false,
            lastCatInfo: null,
            firstLoading: false,
            lastCategoryUrl: '',
            disabledLevelCat: [0],
        }),
        computed: {
            ...mapState('categories-analitik', ['date']),
            ...mapGetters('categories-analitik', [
                'levelOne',
                'levelTwo',
                'levelThree',
                'finishCategiry',
                'subjectsList',
            ]),
            ...mapState('categories-analitik', [
                'selectCategories',
                'selectSubjectId',
                'date',
                'categoryPath',
                'dataPrices',
                'pricesColums',
                'isLoadPrices',
                'startPriceRange',
                'prices',
                'firstLoadingPrice',
            ]),
            selectedDates() {
                if (this.date && Array.isArray(this.date) && this.date.length > 0) {
                    return this.date;
                }
                return [];
            },
            categoryLink() {
                /* eslint-disable */
                const nameLastCategory = this.categoryPathArr.pop();

                return nameLastCategory;
            },
            period() {
                return {
                    isActive: true,
                    selectedDates: this.selectedDates,
                };
            },
        },
        watch: {
            // TODO: Обдумать как перзагружать без флага, но в принципе работает)
            '$route.query': {
                deep: true,
                handler(value) {
                    if (value?.fc == 1) {
                        this.categoryPathArr = value.category.split('/');
                    }
                },
            },
            // TODO: Убрать эту прокладку, когда решу проблему с проивзодительностью
            demoTab(value) {
                setTimeout(() => {
                    this.tab = value;
                }, 300);
            },
            tab() {
                this.loadData();
            },
            categoryPathArr(value) {
                this.setCategoriesData(['categoryPath', value.join('/')]);

                this.resetParams();
                this.loadData();
            },
        },
        async mounted() {
            this.setDefaultParams();
            if (this.categoryPath) {
                this.categoryPathArr = this.categoryPath.split('/');
            }
        },
        beforeDestroy() {
            this.сlearСategory();
        },

        methods: {
            ...mapMutations('categories-analitik', [
                'setDate',
                'setSubjectsData',
                'clearDataProduts',
                'setCategoriesData',
                'setDefaultParams',
                'resetParams',
                'setDataPrices',
            ]),
            ...mapActions('categories-analitik', [
                'loadAnalyticsCategoriesSubjects',
                'loadAnalyticsProductsWildberries',
                'loadAnalyticsSubcategoriesWildberries',
                'loadAnalyticsCategoriesPrices',
            ]),
            сlearСategory(back) {
                if (back) {
                    this.$router.push({ query: {} });
                }
                this.setCategoriesData(['categoryPath', '']);
                this.setCategoriesData(['selectCategoriesFlag', true]);
            },
            async setMenuForNextCat() {
                this.selMenuForNextCat = [];

                const { categoryPathArr } = this;
                let finalCategory;
                const levels = [];

                (function findTheLastChildren(arr, level = 0) {
                    if (!categoryPathArr[level] || !arr.length) {
                        return;
                    }

                    for (let i = 0, l = arr.length; i < l; i += 1) {
                        const { name, children } = arr[i];
                        if (name === categoryPathArr[level]) {
                            finalCategory = arr[i];
                            levels.push(arr[i]);
                            findTheLastChildren(children, level + 1);
                            return;
                        }
                    }
                })(this.levelOne);

                this.lastCategoryUrl = finalCategory.url;

                this.categoryMenu = levels.map(item => item.children.map(({ name }) => name));

                this.categoryMenu.unshift(this.levelOne.map(({ name }) => name));
                this.categoryMenu.pop();

                this.lastCategoryInserted = this.categoryMenu.length < categoryPathArr.length;

                if (finalCategory && finalCategory.children.length) {
                    this.selMenuForNextCat = finalCategory.children.map(({ name }) => name);
                    this.selectedLastCategory = false;
                    this.lastCatInfo = this.subjectsList;
                } else {
                    if (this.lastWebIdSubjects !== finalCategory.web_id) {
                        this.lastWebIdSubjects = finalCategory.web_id;
                        await this.loadAnalyticsCategoriesSubjects(finalCategory.web_id);
                    }
                    this.selMenuForNextCat = this.subjectsList.map(({ label }) => label);
                    this.selectedLastCategory = true;
                    this.lastCatInfo = this.subjectsList;
                }

                if (this.lastCategoryInserted && this.subjectsList.length) {
                    const { id: subjectId } = this.subjectsList.find(
                        ({ label }) =>
                            label === this.categoryPathArr[this.categoryPathArr.length - 1]
                    );

                    this.lastCategoryUrl += `?xsubject=${subjectId}`;
                }
            },
            setAlternatePath(name, index) {
                this.categoryPathArr = this.categoryPathArr.slice(0, index);
                this.categoryPathArr.push(name);
                this.setMenuForNextCat();
            },
            setPathFromParent(value, index) {
                const lastElement = this.categoryPathArr[this.categoryPathArr.length - 1];
                const isLastElement = lastElement === value;

                if (this.disabledLevelCat.includes(index) || isLastElement) {
                    return;
                }
                this.categoryPathArr = this.categoryPathArr.slice(
                    0,
                    this.categoryPathArr.indexOf(value) + 1
                );
                this.setMenuForNextCat();
            },
            setPath(value) {
                if (this.lastCategoryInserted) {
                    this.categoryPathArr.pop();
                }
                this.categoryPathArr.push(value);
                this.setMenuForNextCat();
            },
            changeSetDate(value) {
                this.setDate(value);
                if (this.firstLoading) this.loadData();
            },
            async loadData() {
                await [
                    this.loadAnalyticsProductsWildberries,
                    this.loadAnalyticsSubcategoriesWildberries,
                    this.loadAnalyticsCategoriesPrices,
                ][this.tab]();

                if (!this.firstLoading) {
                    this.firstLoading = true;
                    this.setMenuForNextCat();
                }
            },
            backBtnClick() {
                this.$router.push({ query: {} });
                this.setCategoriesData(['selectCategoriesFlag', true]);
            },
        },
    };
</script>

<style lang="scss" scoped>
    .category-links {
        display: flex;
        gap: 8px;
        margin: 0;
        padding: 0;
        font-size: 18px;

        &__title {
            color: $primary-500;
            cursor: pointer;
        }

        &__slash {
            margin-right: 8px;
            margin-left: 8px;
            color: $base-500;
        }
    }

    .categories-main {
        display: block;

        &__title {
            display: flex;
            align-items: center;
            padding: 18px 8px;

            &-box {
                display: flex;
                flex: 1 1 auto;
            }

            &-prev {
                padding: 6px 16px 6px 12px;
                border-radius: 200px;
                border: 1px solid #c8cfd9;
                font-size: 14px;
                font-style: normal;
                font-weight: 500;
                line-height: 19px;
                color: #7e8793;
                cursor: pointer;

                & svg {
                    margin-right: 8px;
                }
            }

            &-text {
                padding: 0 12px;
                font-size: 24px;
                font-style: normal;
                font-weight: 500;
                line-height: 33px;
                color: #2f3640;
            }
        }

        &__content {
            border-radius: 16px;
            background: #fff;

            @include cardShadow;
        }

        &__select-cat {
            display: flex;
            align-items: center;
            padding: 16px;
        }

        &__name {
            font-size: 20px;
            font-style: normal;
            font-weight: 500;
            line-height: 27px;
            color: #710bff;
        }

        &__delimetr {
            padding: 0 8px 0 8px;
            font-size: 14px;
            font-style: normal;
            font-weight: 500;
            line-height: 19px;
            color: #e9edf2;
        }

        &__select-subject {
            max-width: 250px;
            margin-left: 10px !important;
        }

        &__link {
            display: flex;
            align-items: center;
            margin: 0 20px;
            font-size: 12px;
            font-style: normal;
            font-weight: normal;
            line-height: 16px;
            color: #20c274;

            & svg {
                margin-left: 6px;
            }
        }

        &__check-tovars {
            display: flex;
            align-items: center;
            flex: 1 1 auto;
            font-size: 14px;
            font-style: normal;
            font-weight: 500;
            line-height: 19px;
            color: #2f3640;

            & span {
                margin-left: 8px;
            }
        }
    }
</style>
<style lang="scss">
    .categories-main {
        &__datepicker {
            min-width: 200px;
        }

        &__select-period {
            max-width: 115px;
        }

        &__select.v-select.v-text-field {
            position: relative;
            display: inline-block;
            flex: 0 1 auto;
            width: auto;
            height: 32px;
            margin: 0;
            padding: 0;

            input {
                position: absolute;
                flex-grow: 0;
                width: 0;
                opacity: 0;
            }

            .v-select__selections {
                flex: 0 1 auto;
                width: auto;
            }

            .v-select__selection--comma {
                overflow: visible;
                margin-left: 0;
                font-size: 20px;
                font-style: normal;
                font-weight: 500;
                line-height: 27px;
                color: #710bff;
            }

            .v-input__append-inner {
                width: 24px;
                height: 24px;
                margin-left: 6px;
                border-radius: 8px;
                background-color: #f9f9f9;
                background-image: url(/images/icons/addCategories.svg);
                background-position: center;
                background-repeat: no-repeat;
                background-size: 10px;
                cursor: pointer;

                & .v-input__icon {
                    display: none;
                }
            }

            &.v-input--is-label-active .v-input__append-inner {
                background-image: url(/images/icons/selectCategories.svg);
            }

            .v-input__slot {
                border: none;
            }

            .v-select__slot {
                height: 32px;
            }

            &.v-input--is-focused .v-input__slot {
                box-shadow: none;
            }
        }
    }
</style>
