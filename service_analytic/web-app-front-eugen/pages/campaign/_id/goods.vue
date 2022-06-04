<template>
    <div :class="$style.CampaignEditKeyWordsPage">
        <div :class="$style.filtersWrapper">
            <VBtn outlined class="responsiveButton" @click="handleAddGoods">
                <SvgIcon name="outlined/plus" :class="$style.btnIcon" />
                Добавить товары
            </VBtn>
            <VBtn
                outlined
                :disabled="isGroupingEnabled"
                class="responsiveButton"
                @click="handleCreateGroup"
            >
                <SvgIcon name="outlined/folderPlus" :class="$style.btnIcon" />
                Создать группу
            </VBtn>
            <VBtn outlined :disabled="isSelected" class="responsiveButton" @click="handleDelete">
                <SvgIcon name="outlined/deletetrash" :class="$style.btnIcon" />
                Удалить
            </VBtn>
        </div>
        <AdmEditGoodsTable
            :class="$style.tableWrapper"
            :headers="headers"
            :items="tableItems"
            selectable
            is-enable-context-menu
            :highlight-elements="selectedElements"
            @onContext="handleContextOpen"
        />
        <div :class="$style.btnWrapper">
            <div :class="$style.statsWrapper">
                <div>
                    Выделено товаров:
                    <span :class="$style.statsValue">{{ selected.length }}</span>
                </div>
                <div>
                    Всего товаров:
                    <span :class="$style.statsValue">{{ tableItems.length }}</span>
                </div>
            </div>
        </div>
    </div>
</template>
<router>
{
  name: 'campaign-goods',
  path: '/:marketplace/campaign/:id/goods',
  meta:{
    pageGroup: "perfomance",
    redirectOnChangeMarketplace: true,
    isEnableGoBackOnMobile:true,
    fallbackRoute: {
      name: "adm-campaigns"
    },
    name: "goods"
  }
}
</router>
<script>
    import { mapGetters, mapState } from 'vuex';

    export default {
        name: 'AdmGoodsPage',

        transition: {
            name: 'fade',
            mode: 'out-in',
        },
        data() {
            return {
                headers: [
                    {
                        text: 'Артикул',
                        value: 'id',
                        childValue: 'product_id',
                        width: '15%',
                    },
                    {
                        text: 'Название товара',
                        value: 'name',
                        width: 'calc(100% - 5.6rem - 69%)',
                    },
                    { text: 'Категория', value: 'category_name', width: '14%' },
                    {
                        text: 'Дата добавления',
                        value: 'created_at',
                        width: '14%',
                        valueTransfromFunction: val =>
                            this.$options.filters.formatDateTime(val, '$d.$m.$y - $G:$I'),
                    },
                    {
                        text: 'Ключевые слова',
                        value: 'keywords_count',
                        width: '13%',
                    },
                    {
                        text: 'Минус слова',
                        value: 'stop_words_count',
                        width: '13%',
                    },
                ],
                contextMenuItemCached: {},
            };
        },
        computed: {
            ...mapGetters('campaign', {
                tableItems: 'getCompaignGoodsWithGroups',
                groups: 'getCampaignGroups',
            }),
            ...mapState('campaign', {
                selected: state => state.selectedGoods,
            }),
            isGroups() {
                return Boolean(this.groups?.length);
            },
            isSelected() {
                return !this.selected?.length;
            },
            isGroupingEnabled() {
                return this.selected?.length < 2;
            },
            contextMenuItems() {
                return [
                    {
                        title: 'Сгруппировать',
                        value: 'goodCreateGroup',
                        icon: 'outlined/collection',
                        callback: this.handleGoodCreateGroup,
                    },
                    {
                        title: 'Переместить в группу',
                        value: 'goodMoveToGroup',
                        icon: 'outlined/folderplus',
                        // true ||
                        disabled: !this.isGroups,
                        callback: this.handleGoodMoveToGroup,
                    },
                    {
                        title: 'Удалить',
                        value: 'goodDelete',
                        icon: 'outlined/deletetrash',
                        callback: this.handleGoodDelete,
                    },
                ];
            },
            groupContextMenuItems() {
                return [
                    {
                        title: 'Переменовать',
                        value: 'groupChange',
                        icon: 'outlined/edit',
                        callback: this.handleGroupRename,
                    },
                    {
                        title: 'Расформировать',
                        value: 'groupDisband',
                        icon: 'outlined/link',
                        callback: this.handleGroupDisband,
                    },
                    {
                        title: 'Удалить',
                        value: 'groupDelete',
                        icon: 'outlined/deletetrash',
                        callback: this.handleGroupDelete,
                    },
                ];
            },
            childContextMenuItems() {
                return [
                    {
                        title: 'Переместить в группу',
                        value: 'childChange',
                        // true ||
                        disabled: this.groups?.length < 2,
                        icon: 'outlined/folderplus',
                        callback: this.handleGoodMoveToGroup,
                    },
                    {
                        title: 'Переместить из группы',
                        value: 'childMove',
                        icon: 'outlined/folderminus',
                        callback: this.handleGoodRemoveFromGroup,
                    },
                    {
                        title: 'Удалить',
                        value: 'childDelete',
                        icon: 'outlined/deletetrash',
                        callback: this.handleGoodDelete,
                    },
                ];
            },
            selectedElements() {
                const isElementIsSelected = this.selected.some(
                    item => String(item.id) === String(this.contextMenuItemCached.id)
                );
                return isElementIsSelected ? this.selected : [this.contextMenuItemCached];
            },
        },
        methods: {
            handleAddGoods() {
                return this.$modal.open({
                    component: 'ModalAdmGoodsAdd',
                });
            },
            handleAddGoodsList() {
                return this.$modal.open({
                    component: 'ModalAdmGoodsAddList',
                });
            },
            handleCreateGroup() {
                return this.$modal.open({
                    component: 'ModalAdmGoodsGroupCreate',
                    attrs: {
                        items: this.selected,
                    },
                });
            },
            handleDelete() {
                return this.$modal.open({
                    component: 'ModalAdmGoodsDelete',
                    attrs: {
                        items: this.selected,
                    },
                });
            },
            async handleContextOpen(options, item) {
                this.contextMenuItemCached = item;
                if (this.$contextMenu?.data?.show) {
                    return this.$contextMenu.close();
                }
                let items = [];
                if (item.isGroup) {
                    items = this.groupContextMenuItems;
                } else if (item.isInGroup) {
                    items = this.childContextMenuItems;
                } else {
                    items = this.contextMenuItems;
                }

                return this.$nextTick(() =>
                    this.$contextMenu.open({
                        items,
                        options,
                        item,
                    })
                );
            },
            async handleGoodCreateGroup() {
                if (!this?.contextMenuItemCached?.id) {
                    return;
                }
                await this.$contextMenu.close();
                this.$modal.open({
                    component: 'ModalAdmGoodsGroupCreate',
                    attrs: {
                        items: this.selectedElements,
                    },
                });
            },
            async handleGoodMoveToGroup() {
                if (!this?.contextMenuItemCached?.id) {
                    return;
                }
                await this.$contextMenu.close();
                return this.$modal.open({
                    component: 'ModalAdmGoodsMove',
                    attrs: {
                        item: this.contextMenuItemCached,
                        items: this.selectedElements,
                    },
                });
            },
            async handleGoodRemoveFromGroup() {
                if (!this?.contextMenuItemCached?.id) {
                    return;
                }
                await this.$contextMenu.close();
                return this.$modal.open({
                    component: 'ModalAdmGoodsUngroup',
                    attrs: {
                        item: this.contextMenuItemCached,
                        items: this.selectedElements,
                    },
                });
            },
            async handleGoodDelete() {
                if (!this?.contextMenuItemCached?.id) {
                    return;
                }
                await this.$contextMenu.close();
                return this.$modal.open({
                    component: 'ModalAdmGoodsDelete',
                    attrs: {
                        items: this.selectedElements,
                    },
                });
            },
            async handleGroupDelete() {
                if (!this?.contextMenuItemCached?.id) {
                    return;
                }
                await this.$contextMenu.close();
                return this.$modal.open({
                    component: 'ModalAdmGoodsGroupDelete',
                    attrs: {
                        item: this.contextMenuItemCached,
                    },
                });
            },
            async handleGroupDisband() {
                if (!this?.contextMenuItemCached?.id) {
                    return;
                }
                await this.$contextMenu.close();
                return this.$modal.open({
                    component: 'ModalAdmGoodsGroupDisband',
                    attrs: {
                        item: this.contextMenuItemCached,
                    },
                });
            },
            async handleGroupRename() {
                if (!this?.contextMenuItemCached?.id) {
                    return;
                }
                await this.$contextMenu.close();
                return this.$modal.open({
                    component: 'ModalAdmGoodsGroupRename',
                    attrs: {
                        item: this.contextMenuItemCached,
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
        flex-direction: column;
    }

    .btnWrapper {
        display: flex;
        margin-top: auto;
        gap: 16px;
    }

    .actionBtn {
        flex-basis: 256px;
        height: 40px !important;
        font-size: 16px !important;
    }

    .filtersWrapper {
        gap: 0.8rem;
        display: flex;
        margin-bottom: 1.6rem;
    }

    .statsWrapper {
        display: flex;
        align-items: center;
        margin-left: auto;
        font-size: 1.2rem;
        gap: 1.6rem;

        @extend %text-caption;
    }

    .statsValue {
        font-weight: bold;
    }

    .tableWrapper {
        position: relative;
        overflow: hidden;
        flex: 1;
        margin-bottom: 16px;
        border-radius: 8px;
        border: 1px solid $base-400;
    }
</style>
