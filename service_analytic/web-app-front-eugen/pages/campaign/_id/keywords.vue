<template>
    <div ref="page" :class="$style.CampaignEditKeyWordsPage">
        <div :class="$style.goodsWrapper">
            <div :class="[$style.controlsWrapper, $style.controlsGoods]">
                <InputSearch v-model="searchGoods" label="Найти товар" />
            </div>
            <AdmKeywordsGoodsPicker
                ref="goodsTable"
                :class="$style.tableWrapper"
                :items="pickerItemsFiltered"
                :is-search="isSearch"
                :search="searchGoods"
            />
        </div>
        <div :class="$style.keywordsWrapper">
            <div :class="$style.controlsWrapper">
                <VBtn :disabled="!isPicked" outlined @click="handleAddKeywords">
                    <SvgIcon data-left name="outlined/plus" />
                    Добавить
                </VBtn>
                <VBtn :disabled="!isSelected" outlined @click="handleChangeCPM(false)">
                    Изменить CPM
                </VBtn>
            </div>
            <AdmKeywordsTable
                ref="keywordsTable"
                :class="$style.tableWrapper"
                :headings="headersKeywords"
                :items="keywords"
                :highlight-elements="selectedElements"
                :loading="isLoadingKeywords"
                @delete="handleKeywordsDeleteFromTable"
                @context="handleContextOpen"
                @addStopword="handleAddStopwordsFromKeywords"
                @deleteAll="handleKeywordsDeleteAllFromTable"
            />
        </div>
        <div :class="$style.stopwordsWrapper">
            <div :class="$style.controlsWrapper" />
            <AdmStopwordsTable
                ref="stopwordsTable"
                :loading="isLoadingStopwords"
                :class="$style.tableWrapper"
                :headings="headersStopwords"
                :items="stopwords"
                @delete="handleStopwordsDeleteFromTable"
                @deleteAll="handleStopwordsDeleteAllFromTable"
            />
        </div>
    </div>
</template>
<router>
{
  name: 'campaign-keywords',
  path: '/:marketplace/campaign/:id/keywords',
  meta:{
    pageGroup: "perfomance",
    redirectOnChangeMarketplace: true,
    fallbackRoute: {
      name: "adm-campaigns"
    },
    isEnableGoBackOnMobile:true,
    name: "keywords",
    growHeightOnMobile: true
  }
}
</router>
<script>
    import { debounce } from 'lodash';

    import { mapGetters, mapState, mapActions } from 'vuex';
    const headersGoods = [{ text: 'Товары', value: 'name' }];
    const headersKeywords = [
        {
            text: 'Ключевое слово',
            value: 'name',
            width: 'calc(100% - 16.8rem - 12rem)',
        },
        { text: 'Популярность', value: 'popularity', width: '16.8rem' },
        { text: 'Ставка', value: 'bid', width: '12rem' },
    ];
    const headersStopwords = [{ text: 'Минус слово', value: 'name', width: '100%' }];
    export default {
        name: 'AdmKeywordsPage',

        transition: {
            name: 'fade',
            mode: 'out-in',
        },
        data() {
            return {
                searchGoods: '',
                searchKeyword: '',
                headersGoods,
                headersKeywords,
                headersStopwords,
                isLoadingKeywords: false,
                isStopWordsFetched: true,
                isLoadingStopwords: false,
                // resizeObserver: null,
                contextMenuItemCached: null,
            };
        },
        async fetch() {
            if (!this.pickedElement) {
                return;
            }
            return this.getPageData();
        },
        computed: {
            ...mapGetters('campaign', {
                campaign: 'getCampaignData',
            }),

            ...mapState('keywords', {
                keywords: state => state.keywords,
                stopwords: state => state.stopwords,
                selectedKeywords: state => state.selectedKeywords,
                selectedStopwords: state => state.selectedStopwords,
            }),
            ...mapGetters('keywords', {
                pickerItems: 'getPickerItems',
                isSelected: 'isAnyKeywordSelected',
                isStopWordSelected: 'isAnyStopwordSelected',
                pickedElementObject: 'pickedElementObject',
                keywordsParams: 'keywordsParams',
            }),

            pickedElement() {
                return this.$route?.query?.picked_id ? String(this.$route.query.picked_id) : null;
            },
            isPicked() {
                return Boolean(this.pickedElement);
            },
            isSelectable() {
                return this.pickerItems.length > 1;
            },
            contextMenuItems() {
                return [
                    {
                        title: 'Изменить ставку',
                        value: 'change',
                        icon: 'outlined/collection',
                        callback: this.handleContextChange,
                    },
                    // {
                    //     title: 'Подобрать ',
                    //     value: 'auto',
                    //     icon: 'outlined/folderplus',
                    // },
                    {
                        title: 'Удалить',
                        value: 'delete',
                        icon: 'outlined/deletetrash',
                        callback: this.handleContextDelete,
                    },
                ];
            },
            selectedElements() {
                const isSelected = this.selectedKeywords?.length;
                if (!isSelected) {
                    if (!this.contextMenuItemCached?.id) {
                        return [];
                    }
                    return [this.contextMenuItemCached];
                }
                if (!this?.contextMenuItemCached?.id) {
                    return this.selectedKeywords;
                }
                const isElementIsSelected = this.selectedKeywords.some(
                    item => String(item.id) === String(this.contextMenuItemCached.id)
                );
                return isElementIsSelected ? this.selectedKeywords : [this.contextMenuItemCached];
            },
            isSearch() {
                return this.searchGoods && this.searchGoods.length >= 3;
            },
            pickerItemsFiltered() {
                if (!this.isSearch) {
                    return this.pickerItems;
                }
                return this.pickerItems.filter(item =>
                    item.name.toLowerCase().includes(this.searchGoods.toLowerCase())
                );
            },
        },
        watch: {
            pickedElement: {
                async handler() {
                    await this.unselectAllKeywords();
                    await this.unselectAllStopwords();
                    this.isLoadingKeywords = true;
                    return this.fetchKeywordsDelayed();
                },
            },
        },
        async mounted() {
            if (!this.pickedElement) {
                const id = await this.$store.dispatch('keywords/setPicker');
                if (!id) {
                    console.warn('🚀 ~ file: keywords.vue ~ NO id');
                    return;
                }
                return this.$router.replace({
                    name: 'campaign-keywords',
                    params: this.$route.params,
                    query: {
                        picked_id: id,
                    },
                });
            }
            if (this.$route?.query?.search) {
                this.handleAddKeywords();
            }
            if (this.$route?.query?.stop_search) {
                this.handleAddStopwords();
            }
            // if (this.$refs?.page) {
            //     this.resizeObserver = new ResizeObserver(this.handleSetScrollAreaHeight);
            //     this.resizeObserver.observe(this.$refs.page);
            // } else {
            //     console.warn('NO PAGE REF');
            // }
        },
        beforeDestroy() {
            //     if (this.resizeObserver) {
            //         this.resizeObserver.unobserve(this.$refs.page);
            //     }
            return this.flushKeywordData();
        },
        methods: {
            ...mapActions('keywords', {
                handleSelect: 'toggleKeyword',
                handleSelectAll: 'toggleAllKeyword',
                unselectAllKeywords: 'unselectAllKeywords',
                unselectAllStopwords: 'unselectAllStopwords',
                fetchStopwords: 'fetchStopwords',
                fetchKeywords: 'fetchKeywords',
                flushKeywordData: 'flushKeywordData',
            }),
            fetchKeywordsDelayed: debounce(function () {
                return this.getPageData();
            }, 300),
            async getPageData() {
                this.isLoadingKeywords = true;
                this.isLoadingStopwords = true;
                return Promise.all([
                    this.fetchStopwords().then(() => {
                        this.isLoadingStopwords = false;
                    }),
                    this.fetchKeywords().then(() => {
                        this.isLoadingKeywords = false;
                    }),
                ]).catch(error => {
                    this.isLoadingKeywords = false;
                    this.isLoadingStopwords = false;
                    return this?.$sentry?.captureException(error);
                });
            },
            handleAddKeywords() {
                return this.$modal.open({
                    component: 'ModalAdmKeywordsAdd',
                });
            },
            handleAddStopwordsFromKeywords(payload) {
                return this.$modal.open({
                    component: 'ModalAdmStopwordsAddAlt',
                    attrs: {
                        item: payload,
                        // items: [
                        //     {
                        //         keyword_name: payload,
                        //     },
                        // ],
                    },
                });
            },
            handleAddStopwords() {
                return this.$modal.open({
                    component: 'ModalAdmStopwordsAdd',
                });
            },
            handleAutopicker() {
                return this.$modal.open({
                    component: 'ModalAdmKeywordsChangeCPM',
                });
            },
            handleChangeCPM(payload) {
                const finalItems = payload || this.selectedKeywords;
                if (!finalItems?.length) {
                    console.warn('keywords.vue ~ handleChangeCPM ~ no items');
                    return;
                }
                return this.$modal.open({
                    component: 'ModalAdmKeywordsChangeCPM',
                    attrs: {
                        items: finalItems,
                    },
                });
            },
            handleKeywordsDelete(payload) {
                const finalItems = payload || this.selectedKeywords;
                return this.$modal.open({
                    component: 'ModalAdmKeywordsDelete',
                    attrs: {
                        items: finalItems,
                    },
                });
            },
            // handleStopwordsDelete(payload) {
            //     const finalItems = payload || this.selectedStopwords;
            //     return this.$modal.open({
            //         component: 'ModalAdmStopwordsDelete',
            //         attrs: {
            //             items: finalItems,
            //         },
            //     });
            // },
            async handleContextOpen({ options, item }) {
                this.contextMenuItemCached = item;
                return this.$nextTick(() =>
                    this.$contextMenu.open({
                        options,
                        item,
                        items: this.contextMenuItems,
                    })
                );
            },
            handleContextChange() {
                return this.handleChangeCPM(this.selectedElements);
            },
            handleContextDelete() {
                return this.handleKeywordsDelete(this.selectedElements);
            },
            handleKeywordsDeleteFromTable(payload) {
                let finalItems = [payload];
                if (this.selectedKeywords) {
                    const isIncludes =
                        this.selectedKeywords.findIndex(
                            item => String(item.id) === String(payload.id)
                        ) > -1; // .includes(payload);
                    if (isIncludes) {
                        finalItems = this.selectedKeywords;
                    }
                }
                return this.handleKeywordsDelete(finalItems);
            },
            handleKeywordsDeleteAllFromTable(val) {
                return this.handleKeywordsDelete(val);
            },
            handleStopwordsDeleteFromTable(item) {
                return this.$modal.open({
                    component: 'ModalAdmStopwordsDelete',
                    attrs: {
                        items: [item],
                    },
                });
            },
            handleStopwordsDeleteAllFromTable(items) {
                return this.$modal.open({
                    component: 'ModalAdmStopwordsDelete',
                    attrs: {
                        items,
                    },
                });
            },
        },
    };
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */

    .CampaignEditKeyWordsPage {
        overflow: hidden;
        display: flex;
        flex: 1;
        height: 100%;
        font-size: 1.6rem;
        gap: 1.6rem;

        @include respond-to(md) {
            flex-direction: column;
        }
    }

    .goodsWrapper {
        display: flex;
        flex: 1;
        max-width: 32.5rem;
        height: 100%;
        flex-basis: 23%;
        flex-direction: column;

        @include respond-to(md) {
            max-width: 100%;
            flex-basis: 100%;
        }
    }

    .stopwordsWrapper {
        display: flex;
        flex: 1;
        max-width: 32.7rem;
        flex-basis: 23%;
        flex-direction: column;

        @include respond-to(md) {
            width: 100%;
            max-width: 100%;
            flex-basis: 100%;
        }
    }

    .keywordsWrapper {
        display: flex;
        flex: 1;
        flex-direction: column;
    }

    .tableWrapper {
        overflow: hidden;
        display: flex;
        flex: 1;
        height: 100%;
        border-radius: 8px;
        border: 1px solid $base-400;
        flex-direction: column;

        @include respond-to(md) {
            width: 100%;
            height: 40px * 7 !important;
            min-height: 40px * 7 !important;
            max-height: 40px * 7 !important;
        }
    }

    // .pickerWrapper {
    //     // overflow: hidden;
    //     // display: flex;
    //     // flex: 1;
    //     // height: 100%;
    //     border-radius: 8px;
    //     border: 1px solid $base-400;
    //     // flex-direction: column;

    //     @include respond-to(md) {
    //         height: 40px * 7;
    //     }
    // }

    .icon {
        width: 2.4rem;
        height: 2.4rem;
    }

    .heading {
        @extend %text-body-2;

        margin-bottom: 1rem;
        font-size: 16px;
        font-weight: 600;
    }

    .controlsWrapper {
        display: flex;
        flex-wrap: wrap;
        min-height: 40px;
        margin-bottom: 16px;
        gap: 10px;
    }
</style>
