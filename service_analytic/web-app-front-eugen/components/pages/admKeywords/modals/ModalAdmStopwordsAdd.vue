<template>
    <BaseDrawer
        ref="drawer"
        :class="$style.ModalAdmStopwordsAddList"
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
        <div :class="$style.wrapper">
            <div :class="$style.heading">Добавление минус слов</div>
            <div :class="$style.searchInputWrapper">
                <InputSearch v-model="search" label="Минус слово" />
            </div>
            <div ref="scrollArea" :class="$style.tablesWrapper">
                <BaseList
                    :class="$style.table"
                    uid="all"
                    :is-infinite-loading="!isEnd"
                    @loadMore="handleLoadMore"
                >
                    <BaseListItem
                        v-for="listItem in displayedItems"
                        :key="`list-item-${listItem.id}`"
                        :headings="$options.HEADINGS"
                        icon="outlined/plus"
                        @eventEmitted="handleSelectTableItem(listItem)"
                    >
                        <BaseListCell width="100%" :text="listItem.name" />
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
                                @eventEmitted="
                                    handleAddNewKeyword({
                                        id: 'new',
                                        name: search,
                                        popularity: '-',
                                    })
                                "
                            >
                                <BaseListCell width="100%" :text="search" />
                            </BaseListItem>
                        </div>
                    </template>
                </BaseList>
                <BaseList :class="$style.table" uid="result">
                    <BaseListItem
                        v-for="listItem in selected"
                        :key="`selected-item-${listItem.id}`"
                        :headings="$options.HEADINGS"
                        icon="outlined/deletetrash"
                        @eventEmitted="handleDeleteTableItem(listItem)"
                    >
                        <BaseListCell width="100%" :text="listItem.name" />
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
        { value: 'name', width: 'calc(100% - 12rem)' },
        { value: 'popularity', width: '12rem' },
    ];
    export default {
        name: 'ModalAdmStopwordsAdd',
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
                search: this.$route.query.stop_search,
                benched: 0,
                perPage: 15,
            };
        },
        fetch() {
            if (!this.search || this.search.length < 3) {
                return;
            }
            return this.$axios
                .$get('/api/adm/v2/stop-words', {
                    params: {
                        search: this.search,
                    },
                })
                .then(({ success, data, ...pageData }) => {
                    if (!success) {
                        console.warn(
                            '🚀 ~ file: ModalAdmStopwordsAdd.vue ~ line 136 ~ .then ~ error',
                            data,
                            pageData
                        );
                        throw new Error('fail');
                    }
                    this.items = data;
                    this.pageData = pageData;
                })
                .catch(error => {
                    console.log(
                        '🚀 ~ file: ModalAdmStopwordsAdd.vue ~ line 143 ~ fetch ~ error',
                        error
                    );
                    throw new Error(error);
                });
        },
        computed: {
            isActionDisabled() {
                return !this.selected.length;
            },
            actionText() {
                if (this.isActionDisabled) {
                    return 'Добавить минус слова';
                }
                const plural = this.$options.filters.plural(this.selected.length, [
                    'cтоп слово',
                    'минус слова',
                    'минус слов',
                ]);
                return `Добавить ${this.selected.length} ${plural}`;
            },
            displayedItems() {
                if (!this.items) {
                    return [];
                }
                return this.items?.filter(
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
            search(val) {
                return this.fetchDelayed(val);
            },
        },
        beforeDestroy() {
            return this.clearUrl();
        },
        methods: {
            async handleLoadMore($state) {
                try {
                    if (this.isLoading || this.isEnd) {
                        return false;
                    }
                    this.isLoading = true;
                    const search = this.search;
                    const { data: items, ...pageData } = await this.$axios.$get(
                        '/api/adm/v2/stop-words',
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
                            '🚀 ~ file: ModalAdmStopwordsAdd.vue ~ line 214 ~ handleLoadMore ~ isEnd'
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
                    return this?.$sentry?.captureException(error);
                }
            },
            async handleAction() {
                this.isActionLoading = true;
                try {
                    const pickedObject = this.$store.getters['keywords/pickedElementObject'];
                    const response = await this.$axios.$post(
                        `/api/adm/v2/campaign/${this.$route.params.id}/stop-words`,
                        {
                            stop_words: this.selected.map(item => ({
                                stop_word_name: item.name,
                                [pickedObject.isGroup ? 'group_id' : 'campaign_good_id']:
                                    this.$route.query.picked_id,
                            })),
                        }
                    );
                    if (!response.success || response?.data?.missed_stop_words?.length) {
                        throw new Error('err');
                    }
                    await this.$store.dispatch('keywords/fetchStopwords');
                    await this.$notify.create({
                        message: 'Минус слова были успешно добавлены',
                        type: 'positive',
                        timeout: 5000,
                    });
                    this.$refs.drawer.show = false;
                    this.isActionLoading = false;
                } catch (error) {
                    console.log(
                        '🚀 ~ file: add-goods.vue ~ line 193 ~ handleAction ~ error',
                        error
                    );

                    this.isActionLoading = false;
                    this.$notify.create({
                        message: 'Ошибка при добавлении минус слов',
                        type: 'negative',
                        timeout: 5000,
                    });
                }
            },
            clearUrl() {
                return this.$router.push({
                    name: this.$route.name,
                    params: this.$route.params,
                    query: { ...this.$route.query, stop_search: undefined },
                });
            },
            handleClose() {
                this.isShow = false;
            },
            fetchDelayed: debounce(function (val) {
                if (!val) {
                    this.items = [];
                    return this.clearUrl();
                }
                this.$router.push({
                    name: this.$route.name,
                    params: this.$route.params,
                    query: { ...this.$route.query, stop_search: val },
                });
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
                });
            },
            handleSelectTableItem(item) {
                if (this.selected.includes(item)) {
                    return;
                }
                this.selected.push(item);
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
    }

    .tableAll {
        max-width: 50%;
    }
</style>
