<template>
    <div :class="$style.KeyWordsBlock">
        <span class="subtitle-2 pr-4 pl-4">Ключевые запросы</span>
        <template v-if="!pickList.length">
            <div :class="$style.ProductSerachBarAddBox">
                <p :class="$style.ProductSerachBarInfo">
                    Для поисковой оптимизации вам понадобится добавить ключевые слова
                </p>
                <VBtn color="accent" @click="handleAddKeywords">Добавить ещё</VBtn>
            </div>
        </template>
        <template v-else>
            <div class="card-p-sides">
                <div :class="$style.KeyWordsBlockTop">
                    <!-- <div :class="$style.KeyWordsBlockButtons">
                        <VBtn color="accent" @click="handleAddKeywords">Добавить ещё</VBtn>
                        <VBtn outlined @click="handleAutofill">Автозаполнение</VBtn>
                    </div> -->
                    <div :class="$style.KeyWordsBlockInfo">
                        <SvgIcon name="filled/info" />
                        <ol>
                            <li>Выберите нужное поле слева</li>
                            <li>Нажмите на ключевое слово, чтобы добавить в поле</li>
                            <li>Сохраните изменения</li>
                        </ol>
                    </div>
                </div>
            </div>
            <VExpansionPanels
                v-if="pickListSorted && pickListSorted.length"
                v-model="panelsModel"
                accordion
                multiple
                flat
            >
                <VExpansionPanel v-for="(panel, panelIndex) in panels" :key="panelIndex">
                    <VExpansionPanelHeader class="se-exp-panel__header">
                        <div :class="$style.PanelHeader">
                            <div :class="$style.PanelHeaderTitle">{{ panel.title }}</div>
                            <div :class="$style.PanelHeaderSubitle">{{ panel.subTitle }}</div>
                        </div>
                    </VExpansionPanelHeader>
                    <VExpansionPanelContent eager class="se-exp-panel__content">
                        <perfect-scrollbar style="max-height: 340px">
                            <KeyWordsDraggable
                                :items="pickListSorted[panelIndex]"
                                :index="panelIndex"
                                @drag="handleDragEvent"
                            />
                        </perfect-scrollbar>
                    </VExpansionPanelContent>
                </VExpansionPanel>
            </VExpansionPanels>
            <div class="card-p-sides mb-3">
                <!-- <VBtn
                    :class="$style.ButtonSave"
                    depressed
                    block
                    color="accent"
                    @click="getOptimizationSaveMethod"
                >
                    Применить
                </VBtn> -->
            </div>
        </template>
    </div>
</template>

<script>
    /* eslint-disable no-duplicate-case */
    import { mapActions, mapGetters } from 'vuex';
    import productPickList from '~mixins/productPickList.mixin';

    export default {
        name: 'KeyWordsBlock',
        mixins: [productPickList],
        data() {
            return {
                drag: false,
                storedChanges: {},
            };
        },
        fetch() {
            return this.getFetchPickListMethod;
        },
        computed: {
            ...mapGetters({
                productId: 'product/getProductId',
                pickList: 'product/getPickList',
                pickListSorted: 'product/getPickListSorted',
                panels: 'product/getSearchOptimizationPanel',
                marketplaceSlug: 'getSelectedMarketplaceSlug',
                getOpenPanels: 'product/getOpenPanels',
                getActiveField: 'product/getActiveField',
            }),
            pickListSortedTrigger() {
                return JSON.stringify(this.pickListSorted.flat());
            },
            getFetchPickListMethod() {
                switch (this.marketplaceSlug) {
                    case 'wildberries':
                        return this.fetchPickListWidlberries(this.productId);

                    default:
                        return null;
                }
            },
            getOptimizationSaveMethod() {
                switch (this.marketplaceSlug) {
                    case 'wildberries':
                        return this.saveOptimizationWildberries;

                    default:
                        return this.saveOptimizationOzon;
                }
            },
            panelsModel: {
                get() {
                    return this.getOpenPanels;
                },
                set(val) {
                    return this.setOpenPanels(val);
                },
            },
        },
        watch: {
            getActiveField(val) {
                const openPanelsLocal = [...this.getOpenPanels];
                const panelsIndexes = this.getPanelIndexByFieldName(val, this.marketplaceSlug);
                panelsIndexes.forEach(el => {
                    const alreadyOpen = openPanelsLocal.find(el1 => el === el1);
                    if (!alreadyOpen) {
                        openPanelsLocal.push(el);
                    }
                });
                return this.setOpenPanels(openPanelsLocal);
            },
        },
        methods: {
            ...mapActions({
                fetchPickListWidlberries: 'product/fetchPickListWidlberries',
                setPickListSorted: 'product/setPickListSorted',
                saveOptimizationWildberries: 'product/saveOptimizationWildberries',
                saveOptimizationOzon: 'product/saveOptimizationOzon',
                setOpenPanels: 'product/setOpenPanels',
            }),
            handleDragEvent(data) {
                if (data.payload.length === this.pickListSorted[data.index].length) {
                    const savedData = [...this.pickListSorted];
                    this.$set(savedData, [data.index], data.payload);
                    this.setPickListSorted({ data: savedData.flat() });
                } else if (this.storedChanges.payload) {
                    const savedData = [...this.pickListSorted];
                    this.$set(savedData, [this.storedChanges.index], this.storedChanges.payload);
                    this.$set(savedData, [data.index], data.payload);
                    this.setPickListSorted({ data: savedData.flat() });
                    this.storedChanges = {};
                } else {
                    this.storedChanges = JSON.parse(JSON.stringify(data));
                }
            },
            handleAddKeywords() {
                return this.$modal.open({ component: 'ModalSearchOptimization' });
            },
        },
    };
</script>

<style lang="scss" module>
    .KeyWordsBlock {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        // padding: 1rem;
        background: #fff;

        & .ProductSerachBarAddBox {
            //position: relative;
            //top: 250px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;

            & .ProductSerachBarInfo {
                max-width: 358px;
                padding-bottom: 16px;
                text-align: center;
                line-height: 24px;
            }
        }

        & .KeyWordsBlockTop {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        & .KeyWordsBlockButtons {
            display: flex;
            gap: 1rem;
        }

        & .KeyWordsBlockInfo {
            display: flex;
            align-items: center;
            gap: size(8);
            padding: size(8);
            border-radius: size(8);
            background-color: rgba(87, 116, 221, 0.08);
            font-size: size(14);
            font-weight: 500;
            line-height: 1.4;
            color: $color-graph-blue;

            & li {
                list-style: unset;
            }
        }

        & .PanelHeader {
            display: flex;
            flex-direction: column;
            gap: size(4);

            & .PanelHeaderTitle {
                font-size: size(16);
                font-weight: 500;
                line-height: size(22);
                color: #2f3640;
            }

            & .PanelHeaderSubitle {
                font-size: size(14);
                font-weight: 500;
                line-height: size(22);
                color: $base-700;
            }
        }

        & .PanelContent {
            display: flex;
            flex-direction: column;
            gap: size(8);
        }
    }
</style>
