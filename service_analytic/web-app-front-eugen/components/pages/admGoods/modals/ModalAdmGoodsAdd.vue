<template>
    <BaseDrawer
        ref="drawer"
        :class="$style.ModalAdmAddBaseList"
        :is-loading="!initialDataFetched && $fetchState.pending"
        :is-error="!!$fetchState.error"
        :error-message="$fetchState.error && $fetchState.error.message"
        width="1026px"
        @close="handleClose"
    >
        <VProgressLinear
            :active="$fetchState.pending || isLoading"
            :class="$style.loadingLinearBar"
            indeterminate
            color="accent"
        />
        <div v-resize="setScrollAreaHeight" :class="$style.wrapper">
            <div :class="$style.heading">Добавление товаров</div>
            <div :class="$style.searchInputWrapper">
                <InputSearch v-model="search" label="Введите название товара или Ozon ID" />
            </div>
            <div ref="scrollArea" :class="$style.tablesWrapper">
                <BaseList
                    :class="$style.table"
                    :headings="$options.HEADINGS"
                    uid="all"
                    icon="outlined/plus"
                    :height="innerHeight"
                    :is-infinite-loading="!!displayedItems.length"
                    :is-scrollable="!!displayedItems.length && displayedItems.length > 3"
                    @loadMore="handleLoadMore"
                >
                    <BaseListItem
                        v-for="item in displayedItems"
                        :key="`list-item-${item.id}`"
                        :class="$style.tableRow"
                        icon="outlined/plus"
                        :selected="selected.findIndex(it => it === String(item.id)) > -1"
                        @eventEmitted="handleSelectTableItem(item)"
                    >
                        <BaseListCell width="12rem">
                            <AdmProductLink :id="item.id" :sku="item.sku" />
                        </BaseListCell>
                        <BaseListCell width="calc(100% - 12rem)" :text="item.name" />
                    </BaseListItem>
                </BaseList>
                <BaseList
                    :class="$style.table"
                    :headings="$options.HEADINGS"
                    uid="result"
                    :height="innerHeight"
                    icon="outlined/deletetrash"
                >
                    <BaseListItem
                        v-for="item in selected"
                        :key="`checked-item-${item.id}`"
                        :class="$style.tableRow"
                        :item="item"
                        :headings="$options.HEADINGS"
                        icon="outlined/deletetrash"
                        @eventEmitted="handleDeleteTableItem"
                    >
                        <BaseListCell width="12rem">
                            <AdmProductLink :id="item.id" :sku="item.sku" />
                        </BaseListCell>
                        <BaseListCell width="calc(100% - 12rem)" :text="item.name" />
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
                    Добавить
                    {{
                        selected.length
                            ? selected.length +
                              ' ' +
                              $options.filters.plural(selected.length, [
                                  'товар',
                                  'товарa',
                                  'товаров',
                              ])
                            : 'товаров'
                    }}
                </VBtn>
            </div>
        </div>
    </BaseDrawer>
</template>
<script>
    import { pick, isEqual, debounce, omit } from 'lodash';
    import { isSet } from '~utils/helpers';
    const REQUEST_FILTERS = ['starts_with'];
    const TEMP_FILTERS = ['selected'];
    const HEADINGS = [
        { value: 'sku', width: '12rem' },
        { value: 'name', width: 'calc(100% - 12rem)' },
    ];
    export default {
        name: 'ModalAdmGoodsAdd',
        transition: {
            name: 'drawer',
            mode: 'out-in',
            duration: 1000,
        },
        HEADINGS,
        data() {
            return {
                isLoading: false,
                isActionLoading: false,
                isShow: false,
                items: [],
                pageData: {},
                initialDataFetched: false,
                selected: [],
                isInfinityLoaded: false,
                innerHeight: '100%',
            };
        },
        async fetch() {
            this.items = [];
            this.isLoading = false;
            return this.$axios
                .$get('/api/adm/v1/goods', {
                    params: {
                        ...this.filtersToRequest,
                    },
                })
                .then(({ data: { data: goods, ...pageData } }) => {
                    this.initialDataFetched = true;
                    this.items = goods;
                    this.pageData = pageData;
                })
                .catch(error => {
                    console.log('🚀 ~ file: _id.vue ~ line 263 ~ fetch ~ error', error);
                    this?.$sentry?.captureException(error);
                    throw new Error(error);
                });
        },
        computed: {
            campaignId() {
                return this.$route.params.id;
            },
            displayedItems() {
                if (!this.items) {
                    return [];
                }
                return this.items?.filter(item => !this.selected.includes(item));
            },
            isActionDisabled() {
                return !this.selected.length;
            },
            search: {
                get() {
                    return this.filtersValues.starts_with;
                },
                set(val) {
                    this.filtersValues = { starts_with: val };
                },
            },
            filtersValues: {
                get() {
                    const picked = pick(this.$route.query, REQUEST_FILTERS);
                    return Object.entries(picked).reduce((acc, val) => {
                        if (!val[1]) {
                            return acc;
                        } else {
                            acc[val[0]] = val[1];
                        }
                        return acc;
                    }, {});
                },
                set(values) {
                    const picked = pick(values, REQUEST_FILTERS);
                    const reduced = Object.entries(picked).reduce((acc, val) => {
                        if (!val[1]) {
                            acc[val[0]] = undefined;
                        } else {
                            acc[val[0]] = val[1];
                        }
                        return acc;
                    }, {});
                    this.$router.push({
                        name: this.$route.name,
                        params: this.$route.params,
                        query: { ...this.$route.query, ...reduced },
                    });
                },
            },
            filtersToRequest() {
                return Object.entries(this.filtersValues).reduce(
                    (acc, val) => {
                        if (isSet(val[1])) {
                            acc[val[0]] = val[1];
                        }
                        return acc;
                    },
                    { campaign_id: this.campaignId }
                );
            },
        },
        watch: {
            filtersValues(val, oldVal) {
                const isValuesEqual = isEqual(val, oldVal);
                if (isValuesEqual) {
                    return;
                }
                return this.fetchDelayed();
            },
        },
        mounted() {
            this.isMounted = true;
            this.setScrollAreaHeight();
        },
        beforeDestroy() {
            this.items = [];
            this.pageData = {};
            this.loading = false;
            this.search = undefined;
        },
        methods: {
            setScrollAreaHeight() {
                // fix Perfect Scroll bug
                if (!this.$refs.scrollArea) {
                    return;
                }
                this.innerHeight = this.$refs.scrollArea.offsetHeight + 'px';
            },
            async handleLoadMore($state) {
                try {
                    if (this.isLoading || this.isEnd) {
                        return false;
                    }
                    this.isLoading = true;
                    const {
                        data: { data: goods, ...pageData },
                    } = await this.$axios.$get('/api/adm/v1/goods', {
                        params: {
                            ...this.filtersToRequest,
                            page: (this.pageData?.current_page || 0) + 1,
                        },
                    });
                    this.isLoading = false;
                    this.pageData = pageData;
                    if (!goods?.length || this.isEnd) {
                        $state.complete();
                    } else {
                        this.items = [...this.items, ...goods];
                        $state.loaded();
                    }
                } catch (error) {
                    console.log(
                        '🚀 ~ file: ModalAdmGoodsAdd.vue ~ line 215 ~ handleLoadMore ~ error',
                        error
                    );
                    await this?.$sentry?.captureException(error);
                    this.isLoading = false;
                    await $state.complete();
                }
            },
            async handleAction() {
                this.isActionLoading = true;
                const ids = this.selected.map(item => item.id);
                try {
                    const response = await this.$axios.$post(
                        '/api/adm/v1/campaign/goods/ids/store',
                        {
                            without_group: {
                                goods: ids,
                            },
                            campaign_id: this.$route.params.id,
                        }
                    );
                    if (!response.success) {
                        throw new Error('Ошибка при добавлении товаров в РК');
                    }
                    await this.$store.dispatch(
                        'campaign/fetchCampaignDataAndGoods',
                        this.$route.params.id
                    );

                    await this.$notify.create({
                        message: 'Товары были успешно добавлены в РК',
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
                        message: 'Ошибка при добавлении товаров в РК',
                        type: 'negative',
                        timeout: 5000,
                    });
                    return this?.$sentry?.captureException(error);
                }
            },
            handleDeleteTableItem(item) {
                if (!this.selected.includes(item)) {
                    return;
                }
                const strId = String(item.id);
                this.selected = this.selected.filter(it => String(it.id) !== strId);
            },
            handleSelectTableItem(item) {
                // console.log(
                //     '🚀 ~ file: ModalAdmGoodsAdd.vue ~ line 282 ~ handleSelectTableItem ~ item',
                //     JSON.stringify(item)
                // );
                if (this.selected.includes(item)) {
                    // console.log(
                    //     '🚀 ~ file: ModalAdmGoodsAdd.vue ~ line 284 ~ handleSelectTableItem ~ includes'
                    // );
                    return;
                }
                // console.log(
                //     '🚀 ~ file: ModalAdmGoodsAdd.vue ~ line 284 ~ handleSelectTableItem ~ NO includes'
                // );

                this.selected = [...this.selected, item];
            },
            handleClose() {
                const queryOmitted = omit(this.$route.query, [...TEMP_FILTERS, ...REQUEST_FILTERS]);
                this.$router.push({
                    name: this.$route.name,
                    params: this.$route.params,
                    query: queryOmitted,
                });
                this.isShow = false;
            },
            fetchDelayed: debounce(function () {
                this.$fetch();
            }, 750),
        },
    };
</script>
<style lang="scss" module>
    .tablesWrapper {
        overflow: hidden;
        display: flex;
        flex: 1;
        margin-bottom: 40px;
        gap: 24px;
    }

    .table {
        max-width: 50%;
    }

    .tableRow {
        position: relative;
        display: flex;
        min-height: 5.6rem;

        @include respond-to(md) {
            min-height: 40px;
        }

        &:not(&:last-child) {
            @include borderLine();
        }
    }

    .wrapper {
        display: flex;
        flex: 1;
        height: 100%;
        padding: 24px;
        flex-direction: column;
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
</style>
