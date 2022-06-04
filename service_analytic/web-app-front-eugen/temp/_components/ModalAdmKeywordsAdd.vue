<template>
    <BaseDrawer
        ref="drawer"
        :class="$style.ModalAdmKeywordsAddList"
        :is-error="!!$fetchState.error"
        :error-message="$fetchState.error && $fetchState.error.message"
        width="60%"
        @close="handleClose"
    >
        <VProgressLinear
            :active="$fetchState.pending || isLoading"
            :class="$style.loadingLinearBar"
            indeterminate
            color="accent"
        />
        <div key="wrapper" :class="$style.wrapper">
            <div :class="$style.heading">Добавить ключевые слова</div>
            <div :class="$style.searchInputWrapper">
                <InputSearch v-model="search" label="Ключевое слово" />
            </div>
            <div ref="scrollArea" :class="$style.tablesWrapper">
                <BaseList
                    :class="$style.table"
                    uid="all"
                    icon="outlined/plus"
                    height="calc(100% - 56px)"
                    is-show-headings
                    :headings="$options.HEADINGS"
                    :is-infinite-loading="!!displayedItems.length"
                    :is-scrollable="!!displayedItems.length && displayedItems.length > 3"
                    @loadMore="handleLoadMore"
                >
                    <template #heading-append>
                        <div :class="$style.emptyAction" />
                    </template>
                    <BaseListItem
                        v-for="listItem in displayedItems"
                        :key="`list-item-${listItem.id}`"
                        icon="outlined/plus"
                        @eventEmitted="handleSelectTableItem(listItem)"
                    >
                        <BaseListCell width="calc(100% - 110px)" :text="listItem.name" />
                        <BaseListCell width="110px" :text="listItem.popularity" />
                    </BaseListItem>
                    <template #empty>
                        <VProgressCircular
                            v-if="$fetchState.pending || isLoading"
                            key="loading"
                            :class="$style.loadingWrapper"
                            indeterminate
                            size="50"
                            color="accent"
                        />
                        <div v-else-if="search && search.length >= 3">
                            <BaseListItem
                                :headings="$options.HEADINGS"
                                icon="outlined/plus"
                                selected
                                :action-disabled="isNewElementSelected"
                                @eventEmitted="handleAddNewKeyword"
                            >
                                <BaseListCell width="100%" :text="search" />
                            </BaseListItem>
                        </div>
                    </template>
                </BaseList>
                <BaseList
                    :class="$style.table"
                    :items="selected"
                    :headings="$options.HEADINGS"
                    is-show-headings
                    uid="result"
                >
                    <template #heading-append>
                        <div :class="$style.emptyAction" />
                    </template>
                    <BaseListItem
                        v-for="listItem in selected"
                        :key="`list-item-${listItem.id}`"
                        icon="outlined/minus"
                        @eventEmitted="handleDeleteTableItem(listItem)"
                    >
                        <BaseListCell width="calc(100% - 110px)" :text="listItem.name" />
                        <BaseListCell width="110px" :text="listItem.popularity" />
                    </BaseListItem>
                </BaseList>
            </div>
            <div :class="$style.btnWrapper">
                <VBtn
                    :disabled="isActionDisabled"
                    :class="$style.action"
                    block
                    large
                    color="accent"
                    :loading="isActionLoading"
                    @click="handleAction"
                >
                    {{ actionText }}
                </VBtn>
            </div>
        </div>
    </BaseDrawer>
</template>
<script>
    import { debounce } from 'lodash';
    const HEADINGS = [
        { value: 'name', width: 'calc(100% - 130px)', text: 'Ключевое слово' },
        { value: 'popularity', width: '130px', text: 'Популярность' },
    ];
    export default {
        name: 'ModalAdmKeywordsAdd',
        HEADINGS,
        transition: {
            name: 'drawer',
            mode: 'out-in',
            duration: 1000,
        },
        data() {
            return {
                isLoading: false,
                isActionLoading: false,
                isShow: false,
                items: [],
                pageData: {},
                selected: [],
                search: this.$route.query.search,
                benched: 0,
            };
        },
        fetch() {
            if (!this.search || this.search.length < 3) {
                return;
            }
            const pickedObject = this.$store.getters['keywords/pickedElementObject'];

            return this.$axios
                .$get('/api/adm/v2/keywords', {
                    params: {
                        search: this.search,
                        [pickedObject.isGroup ? 'group_id' : 'campaign_good_id']:
                            this.$route.query.picked_id,
                    },
                })
                .then(({ success, data, ...pageData }) => {
                    if (!success) {
                        console.warn(
                            '🚀 ~ file: ModalAdmKeywordsAdd.vue ~ line 136 ~ .then ~ error',
                            data,
                            pageData
                        );
                        throw new Error('fail');
                    }
                    this.items = data;
                    this.pageData = pageData;
                })
                .catch(error => {
                    throw new Error(error);
                });
        },
        computed: {
            isActionDisabled() {
                return !this.selected.length;
            },
            actionText() {
                if (this.isActionDisabled) {
                    return 'Добавить ключевые слова';
                }
                const plural = this.$options.filters.plural(this.selected.length, [
                    'ключевое слово',
                    'ключевых слова',
                    'ключевых слов',
                ]);
                return `Добавить ${this.selected.length} ${plural}`;
            },
            displayedItems() {
                if (!this.items) {
                    return [];
                }
                return this.items.filter(
                    item => this.selected.findIndex(it => it.name === item.name) <= -1
                );
            },
            isNewElementSelected() {
                if (!this.search) {
                    return false;
                }
                return this.selected.findIndex(item => item.name === this.search.trim()) > -1;
            },
            isEnd() {
                if (
                    !this.displayedItems?.length ||
                    this.displayedItems.length < this.perPage ||
                    !this?.pageData?.last_page
                ) {
                    return true;
                }
                return this.pageData.current_page >= this.pageData.last_page;
            },
        },
        watch: {
            search(val, oldVal) {
                const isValuesEqual = val === oldVal;
                if (isValuesEqual) {
                    return;
                }
                if (!val) {
                    this.items = [];
                    return this.clearUrl();
                }
                this.$router.push({
                    name: this.$route.name,
                    params: this.$route.params,
                    query: { ...this.$route.query, search: val },
                });
                return this.fetchDelayed();
            },
        },
        beforeDestroy() {
            return this.clearUrl();
        },
        methods: {
            getPlural(payload = this.selected.length) {
                return this.$options.filters.plural(payload, [
                    'ключевое слово',
                    'ключевых слова',
                    'ключевых слов',
                ]);
            },
            async handleLoadMore($state) {
                try {
                    if (this.isLoading || this.isEnd) {
                        return false;
                    }
                    this.isLoading = true;
                    const search = this.search;
                    const { data: items, ...pageData } = await this.$axios.$get(
                        '/api/adm/v2/keywords',
                        {
                            params: {
                                search,
                                page: (this.pageData?.current_page || 0) + 1,
                            },
                        }
                    );
                    this.isLoading = false;
                    this.pageData = pageData;
                    if (!items?.length) {
                        console.log(
                            '🚀 ~ file: ModalAdmKeywordsAdd.vue ~ line 214 ~ handleLoadMore ~ isEnd'
                        );
                        $state.complete();
                    } else {
                        this.items = [...this.items, ...items];
                        $state.loaded();
                    }
                } catch (error) {
                    console.log(
                        '🚀 ~ file: ModalAdmGoodsAdd.vue ~ line 215 ~ handleLoadMore ~ error',
                        error
                    );
                    this.isLoading = false;
                    await $state.complete();
                }
            },
            async handleAction() {
                this.isActionLoading = true;
                try {
                    const pickedObject = this.$store.getters['keywords/pickedElementObject'];
                    const response = await this.$axios.$post(
                        `/api/adm/v2/campaign/${this.$route.params.id}/keywords`,
                        {
                            keywords: this.selected.map(item => ({
                                [item.isNewKeyword ? 'keyword_name' : 'keyword_id']:
                                    item.isNewKeyword ? item.keyword_name : item.id,
                                [pickedObject.isGroup ? 'group_id' : 'campaign_good_id']:
                                    this.$route.query.picked_id,
                            })),
                        }
                    );
                    console.log(
                        '🚀 ~ file: ModalAdmKeywordsAdd.vue ~ line 234 ~ handleAction ~ response',
                        response
                    );
                    if (!response.success) {
                        throw new Error(response);
                    }
                    const missed = this.getMissedKeywords(response?.data?.missed_keywords);
                    const addedCount = this.selected.length - missed.length;
                    let message = 'Ключевые слова были успешно добавлены';
                    if (addedCount === 0) {
                        throw new Error(response);
                    } else {
                        message = `${addedCount} ${this.getPlural(addedCount)} добавлено.`;
                        if (missed.length) {
                            message += ` ${missed.length} ${this.getPlural(
                                missed.length
                            )} не добавлены.`;
                        }
                    }
                    await this.$notify.create({
                        message,
                        type: 'positive',
                        timeout: 5000,
                    });
                    await this.$store.dispatch('keywords/fetchKeywords');

                    this.$refs.drawer.show = false;
                    this.isActionLoading = false;
                } catch (error) {
                    console.log(
                        '🚀 ~ file: add-goods.vue ~ line 193 ~ handleAction ~ error',
                        error
                    );

                    this.isActionLoading = false;
                    this.$notify.create({
                        message: 'Ошибка при добавлении ключевых слов',
                        type: 'negative',
                        timeout: 5000,
                    });
                    return this?.$sentry?.captureException(error);
                }
            },
            getMissedKeywords(payload) {
                if (!payload || !Array.isArray(payload) || !payload.length) {
                    return [];
                }

                console.log(
                    '🚀 ~ file: ModalAdmKeywordsAdd.vue ~ line 256 ~ getMissedKeywords ~ payload',
                    payload
                );

                return payload;
                //  this.$notify.create({
                //     message: `Ошибка при добавлении ${payload.length} ключевых слов`,
                //     type: 'negative',
                //     timeout: 5000,
                // });
            },
            clearUrl() {
                return this.$router.push({
                    name: this.$route.name,
                    params: this.$route.params,
                    query: { ...this.$route.query, search: undefined },
                });
            },
            handleClose() {
                // this.search = undefined;
                this.isShow = false;
            },
            fetchDelayed: debounce(function () {
                return this.$fetch();
            }, 750),
            handleAddNewKeyword() {
                if (this.isNewElementSelected) {
                    return;
                }
                const name = this.search.trim();
                this.selected.push({
                    id: name,
                    name,
                    popularity: '-',
                    keyword_name: name,
                    isNewKeyword: true,
                });
            },
            handleSelectTableItem(item) {
                if (this.selected.includes(item)) {
                    return;
                }
                this.selected.push(item);
                // this.selected = [...this.selected, item];
            },
            handleDeleteTableItem(item) {
                if (!this.selected.includes(item)) {
                    return;
                }
                const strId = String(item.id);
                this.selected = this.selected.filter(it => String(it.id) !== strId);
            },
        },
    };
</script>
<style lang="scss" module>
    .emptyAction {
        width: 56px;
        min-width: 56px;
    }

    .wrapper {
        display: flex;
        flex: 1;
        height: 100%;
        padding: 24px;
        flex-direction: column;
    }

    .loadingWrapper {
        @include centerer;
    }

    .table {
        flex-basis: 50%;
        max-width: 50%;

        @include md {
            max-width: 100%;
            flex-basis: 100%;
        }
    }

    .loadingLinearBar {
        position: absolute;
        top: 0;
        right: 0;
        left: 0;
    }

    .heading {
        @extend %text-h4;

        margin-bottom: 24px;
        padding-right: 32px;
        color: $base-900;
        font-weight: bold;
    }

    .searchInputWrapper {
        margin-bottom: 24px;
    }

    .btnWrapper {
        margin-top: auto;
    }

    .tablesWrapper {
        overflow: hidden;
        display: flex;
        flex: 1;
        margin-bottom: 40px;
        gap: 24px;

        @include md {
            flex-direction: column;
        }
    }
</style>
