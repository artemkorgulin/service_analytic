<template>
    <div v-if="showTmpl" class="select-categories">
        <div class="select-categories__header">
            <h2 class="select-categories__title">Выберите категорию</h2>
        </div>

        <div class="select-categories__content">
            <vue-custom-scrollbar class="select-categories__section" :settings="settings">
                <template v-for="(item, index) in levelOne">
                    <div
                        :key="index"
                        class="select-categories__item"
                        :class="{
                            'select-categories__item--select': selectCategories.levelOne == item,
                        }"
                        @mouseover="setSelectCategories(1, item)"
                    >
                        <div class="select-categories__item-name">
                            {{ item.name }}
                        </div>
                        <div class="select-categories__item-open">
                            <img src="/images/icons/vectorCategories.svg" alt="" />
                        </div>
                    </div>
                </template>
            </vue-custom-scrollbar>

            <vue-custom-scrollbar class="select-categories__section" :settings="settings">
                <div
                    v-for="(item, index) in levelTwo"
                    :key="index"
                    class="select-categories__item select-categories__item--alone"
                    @mouseover="setSelectCategories(2, item)"
                    @click="assignPathToCategory(item.path)"
                >
                    <div class="select-categories__item-name">
                        {{ item.name }}
                    </div>
                    <div v-if="item.children.length" class="select-categories__item-open">
                        <img src="/images/icons/vectorCategories.svg" alt="" />
                    </div>
                </div>
            </vue-custom-scrollbar>

            <vue-custom-scrollbar class="select-categories__section" :settings="settings">
                <div
                    v-for="(item, index) in levelThree"
                    :key="index"
                    class="select-categories__item select-categories__item--alone"
                    @mouseover="setSelectCategories(3, item)"
                    @click="assignPathToCategory(item.path)"
                >
                    <div class="select-categories__item-name">
                        {{ item.name }}
                    </div>
                </div>
            </vue-custom-scrollbar>
        </div>
    </div>
    <div v-else></div>
</template>

<script>
    import vueCustomScrollbar from 'vue-custom-scrollbar';
    import 'vue-custom-scrollbar/dist/vueScrollbar.css';

    import { mapActions, mapMutations, mapGetters, mapState } from 'vuex';

    export default {
        components: {
            vueCustomScrollbar,
        },

        data: () => ({
            search: '',
            showTmpl: false,
            settings: {
                suppressScrollY: false,
                suppressScrollX: false,
                wheelPropagation: false,
            },
        }),

        computed: {
            ...mapState('categories-analitik', ['selectCategories']),
            ...mapGetters('categories-analitik', ['levelOne', 'levelTwo', 'levelThree']),
        },

        async created() {
            await this.loadAnalyticsCategoriesWildberries();

            if (this.$route.query?.category) {
                this.assignPathToCategory(this.$route.query?.category);
            }

            this.showTmpl = true;
        },

        methods: {
            ...mapActions('categories-analitik', [
                'loadAnalyticsCategoriesWildberries',
                'searchAndOpenCategory',
            ]),
            ...mapMutations('categories-analitik', ['setCategoriesData']),

            assignPathToCategory(path) {
                if (!Array.isArray(path)) {
                    path = path.split('/');
                }
                const pathText = path.join('/');

                const levels = [];
                const levelsName = ['One', 'Two', 'Three'];
                const findLevel = (array, name) => array.find(({ name: nameEl }) => name == nameEl);

                path.forEach((_, index) => {
                    levels[index] = findLevel(
                        !index ? this.levelOne : levels[index - 1].children,
                        _
                    );
                });

                // TODO: Убрать после того

                this.setCategoriesData([
                    'selectCategories',
                    levels.reduce((acc, el, index) => {
                        acc[`level${levelsName[index]}`] = el;
                        return acc;
                    }, {}),
                ]);

                this.$router.push({ query: { ...this.$route.query, category: pathText } });

                this.setCategoriesData(['categoryPath', pathText]);
                this.$emit('click');
            },

            setSelectCategories(level, item) {
                switch (level) {
                    case 1:
                        this.setCategoriesData([
                            'selectCategories',
                            {
                                levelOne: item,
                                levelTwo: null,
                                levelThree: null,
                            },
                        ]);
                        break;
                    case 2:
                        this.setCategoriesData([
                            'selectCategories',
                            {
                                levelOne: this.selectCategories.levelOne,
                                levelTwo: item,
                                levelThree: null,
                            },
                        ]);
                        break;
                    case 3:
                        this.setCategoriesData([
                            'selectCategories',
                            {
                                levelOne: this.selectCategories.levelOne,
                                levelTwo: this.selectCategories.levelTwo,
                                levelThree: item,
                            },
                        ]);
                        break;
                }
            },
        },
    };
</script>

<style lang="scss">
    .select-categories {
        .ps__thumb-y {
            border-radius: 0;
        }
    }
</style>

<style lang="scss" scoped>
    .select-categories {
        height: 100%;
        border-radius: 16px;
        background: #fff;

        @include cardShadow;

        .select-categories__header {
            display: flex;
            height: 72px;
            padding: 16px;
            border-bottom: 1px solid #e9edf2;

            .select-categories__title {
                flex: 1 1 auto;
                padding: 0;
                font-size: 24px;
                font-style: normal;
                font-weight: 500;
                line-height: 33px;
                color: #2f3640;
            }

            .select-categories__search {
                display: flex;
                align-items: center;
                width: 328px;
                padding: 8px;
                border-radius: 8px;
                border: 1px solid #c8cfd9;

                & input {
                    flex: 1 1 auto;
                    margin-left: 8px;
                    font-size: 16px;
                    font-style: normal;
                    font-weight: normal;
                    line-height: 24px;
                }
            }
        }

        .select-categories__content {
            display: flex;
            height: calc(100% - 72px);

            .select-categories__section {
                flex: 1 1 auto;
                width: calc(100% / 3);
                border-right: 1px solid #e9edf2;

                &:last-child {
                    border: none;
                }

                .select-categories__item {
                    position: relative;
                    display: flex;
                    padding: 16px;
                    font-size: 16px;
                    font-style: normal;
                    font-weight: 500;
                    line-height: 22px;
                    color: #2f3640;

                    &--alone:hover {
                        background: #e9edf2;
                        color: #710bff;
                        cursor: pointer;
                    }

                    &--select {
                        background: #e9edf2;
                    }

                    .select-categories__item-open {
                        flex: 1 1 auto;
                        text-align: right;

                        & img {
                            position: relative;
                        }
                    }
                }
            }
        }
    }
</style>
