<template>
    <div v-resize="resetTmpl" class="product-card" :class="$style.ProductPage">
        <div ref="pageContainer" class="page-app__container" style="padding-bottom: 80px">
            <div v-if="isLoadingProduct" key="loading" :class="$style.loadingWrapper">
                <VProgressCircular indeterminate size="100" color="accent" />
            </div>
            <div v-else-if="showErrorDownloadProduct">Ошибка получения продукта</div>
            <div v-else key="content">
                <div class="page-app__top">
                    <div :class="$style.ProductPageTitle">
                        <div :class="$style.heading">
                            <VFadeTransition>
                                <VBtn
                                    v-if="isEnableGoBack"
                                    :to="routeBack"
                                    :class="$style.ArrowBack"
                                    tile
                                    text
                                >
                                    <SvgIcon name="outlined/chevronback"></SvgIcon>
                                </VBtn>
                            </VFadeTransition>
                            <span class="page-app__header">Анализ карточки товара</span>
                            <ProductsListIconCheck
                                v-if="getStatus"
                                :class="$style.ProductPageTitleStatus"
                                time=""
                                :status="getStatus"
                            />
                            <div class="product-card-btns-wrapper">
                                <VBtn
                                    outlined
                                    @click="
                                        $router.push({
                                            name: 'bulk-edit-copy',
                                            query: queryForBulkCopy,
                                        })
                                    "
                                >
                                    <SvgIcon class="mr-1" name="outlined/tableSplit" data-left />
                                    Копирование характеристик
                                </VBtn>
                            </div>
                        </div>
                    </div>
                </div>

                <SeProductHeader
                    :options="getProductOptions"
                    :title="getProductNameShort"
                    :url="getProductUrl"
                    :product="product"
                ></SeProductHeader>

                <SePageTab
                    ref="sePageTab"
                    v-model="selectedTab"
                    :items="tabItems"
                    :disable-by-index="disabledTabs"
                    class="mb-5"
                ></SePageTab>

                <div class="product-content">
                    <div v-show="selectedTab === 0">
                        <div>
                            <div class="product-content__row product-content__base-data">
                                <ProductInfo
                                    :sku="getProductSku"
                                    :url="getProductUrl"
                                    :quantity="getProductQuantity"
                                    :optimization="getProductOptimization"
                                    :product-stock-availability-params="
                                        productStockAvailabilityParams
                                    "
                                ></ProductInfo>
                                <ProductStat
                                    :rating="getProductRating"
                                    :metrics="getProductMetrics"
                                    :options="productHasOptions ? getProductOptions : false"
                                />
                            </div>
                        </div>
                        <div class="product-content__charts-grid mb-4">
                            <ProductChartByDaysBar
                                title="Контентная оптимизация"
                                :tab-to-open="1"
                                :data="optimizationHistoryContent"
                                :labels="optimizationHistoryLabels"
                                @proceed="handleOpenTab"
                            />
                            <ProductChartByDaysBar
                                title="Поисковая оптимизация"
                                :tab-to-open="2"
                                :data="optimizationHistorySearch"
                                :labels="optimizationHistoryLabels"
                                chart-color="#3DC1CA"
                                @proceed="handleOpenTab"
                            />
                            <ProductChartEscrow
                                :tab-to-open="3"
                                :nomenclatures-tab-selected="getActiveOptionIndex"
                                :info="{
                                    photo: getProductPhotoUrl,
                                    name: getProductNameShort,
                                    sku: getProductSku,
                                    url: getProductUrl,
                                    id: productIdLocal,
                                    options: getProductOptions,
                                    imagesNum: imagesNumber,
                                }"
                                :current-tab="selectedTab"
                                @proceed="handleOpenTab"
                            />
                        </div>
                        <ProductChartSales
                            :product-sku="getProductSku"
                            :product-id-local="productIdLocal"
                            :options="getProductOptions"
                        />
                    </div>
                    <div v-show="selectedTab === 1" ref="productOptions" class="product-options">
                        <div ref="productMonitor" class="product-options__monitor pt-4 pb-2">
                            <ProgressBar
                                :value="contentOptimization"
                                :max="100"
                                :is-loading="Boolean(changesList.length)"
                                title="Полнота заполнения"
                                desc="Текущая заполненность"
                                prefix="%"
                                color="green"
                                class="mr-2"
                            ></ProgressBar>
                            <ProgressBar
                                :value="visibilityOptimization"
                                :max="100"
                                :is-loading="Boolean(changesList.length)"
                                title="Видимость товара"
                                desc="Текущая видимость"
                                prefix="%"
                                color="violet"
                                class="ml-2"
                            ></ProgressBar>
                        </div>
                        <div class="product-options__expansion">
                            <VExpansionPanels
                                v-model="section"
                                multiple
                                :class="$style.ExpansionPanels"
                                class="custom-collapse"
                            >
                                <VExpansionPanel :class="$style.ExpansionPanel">
                                    <VExpansionPanelHeader
                                        :class="$style.ExpansionPanelHeader"
                                        class="collapse-item__header"
                                    >
                                        <div :class="$style.heading">Название товара</div>
                                        <div
                                            v-if="getIconForRecom.name[3]"
                                            :class="$style.iconWrapper"
                                            class="d-flex align-center"
                                        >
                                            <SvgIcon
                                                :name="getIconForRecom.name[0]"
                                                :class="getIconForRecom.name[1]"
                                            />
                                            <span
                                                class="ml-3"
                                                style="width: 100%; padding-top: 2px"
                                                :class="getIconForRecom.name[1]"
                                                v-text="getIconForRecom.name[2]"
                                            ></span>
                                        </div>
                                    </VExpansionPanelHeader>
                                    <VExpansionPanelContent
                                        eager
                                        :class="$style.ExpansionPanelContent"
                                    >
                                        <name-product
                                            ref="nameProduct"
                                            :values="getProductNameFull"
                                            :recommendations="recommendationTitle"
                                        />
                                    </VExpansionPanelContent>
                                </VExpansionPanel>
                                <VExpansionPanel
                                    v-if="productHasOptions"
                                    :class="$style.ExpansionPanel"
                                >
                                    <VExpansionPanelHeader
                                        ref="priceNomen"
                                        class="collapse-item__header"
                                        :class="$style.ExpansionPanelHeader"
                                    >
                                        <div :class="$style.heading">Цена и номенклатура</div>
                                        <div
                                            v-if="getIconForRecom.price[3]"
                                            :class="$style.iconWrapper"
                                            class="d-flex align-center"
                                        >
                                            <SvgIcon
                                                :name="getIconForRecom.price[0]"
                                                :class="getIconForRecom.price[1]"
                                            />
                                            <span
                                                class="ml-3"
                                                style="width: 100%; padding-top: 2px"
                                                :class="getIconForRecom.price[1]"
                                                v-text="getIconForRecom.price[2]"
                                            ></span>
                                        </div>
                                    </VExpansionPanelHeader>
                                    <VExpansionPanelContent
                                        v-show="getProductOptions.length"
                                        eager
                                        :class="$style.ExpansionPanelContent"
                                    >
                                        <PriceColorsMediaProduct
                                            ref="priceColorsMediaProduct"
                                            :options="getProductOptions"
                                        />
                                    </VExpansionPanelContent>
                                </VExpansionPanel>
                                <VExpansionPanel
                                    v-if="!productHasOptions"
                                    :class="$style.ExpansionPanel"
                                >
                                    <VExpansionPanelHeader
                                        ref="priceNomen"
                                        class="collapse-item__header"
                                        :class="$style.ExpansionPanelHeader"
                                    >
                                        <div :class="$style.heading">Цена</div>
                                        <div
                                            v-if="getIconForRecom.price[3]"
                                            :class="$style.iconWrapper"
                                            class="d-flex align-center"
                                        >
                                            <SvgIcon
                                                :name="getIconForRecom.price[0]"
                                                :class="getIconForRecom.price[1]"
                                            />
                                            <span
                                                class="ml-3"
                                                style="width: 100%; padding-top: 2px"
                                                :class="getIconForRecom.price[1]"
                                                v-text="getIconForRecom.price[2]"
                                            ></span>
                                        </div>
                                    </VExpansionPanelHeader>
                                    <VExpansionPanelContent
                                        eager
                                        class="mt-0 pt-0"
                                        style="padding-top: 0"
                                        :class="$style.ExpansionPanelContent"
                                    >
                                        <ProductPanelWrapper
                                            class="mt-0 pt-0"
                                            style="padding-top: 0"
                                        >
                                            <price-product
                                                v-if="productOzon.data"
                                                ref="priceProduct"
                                                :values="{
                                                    price: productOzon.data.price,
                                                    discount: productOzon.data.discount,
                                                    old_price: productOzon.data.old_price,
                                                    premium_price: productOzon.data.premium_price,
                                                    buybox_price: productOzon.data.buybox_price,
                                                }"
                                            />
                                        </ProductPanelWrapper>
                                    </VExpansionPanelContent>
                                </VExpansionPanel>
                                <VExpansionPanel :class="$style.ExpansionPanel">
                                    <VExpansionPanelHeader
                                        class="collapse-item__header"
                                        :class="$style.ExpansionPanelHeader"
                                    >
                                        <div :class="$style.heading">Рейтинг и отзывы</div>
                                        <div
                                            v-if="getIconForRecom.grade[3]"
                                            :class="$style.iconWrapper"
                                            class="d-flex align-center"
                                        >
                                            <SvgIcon
                                                :name="getIconForRecom.grade[0]"
                                                :class="getIconForRecom.grade[1]"
                                            />
                                            <span
                                                class="ml-3"
                                                style="width: 100%; padding-top: 2px"
                                                :class="getIconForRecom.grade[1]"
                                                v-text="getIconForRecom.grade[2]"
                                            ></span>
                                        </div>
                                    </VExpansionPanelHeader>
                                    <VExpansionPanelContent
                                        eager
                                        :class="$style.ExpansionPanelContent"
                                    >
                                        <div class="so-container">
                                            <RatingStat></RatingStat>
                                        </div>
                                    </VExpansionPanelContent>
                                </VExpansionPanel>
                                <VExpansionPanel :class="$style.ExpansionPanel">
                                    <VExpansionPanelHeader
                                        class="collapse-item__header"
                                        :class="$style.ExpansionPanelHeader"
                                    >
                                        <div :class="$style.heading">Фото</div>
                                        <div
                                            v-if="getIconForRecom.media[3]"
                                            :class="$style.iconWrapper"
                                            class="d-flex align-center"
                                        >
                                            <SvgIcon
                                                :name="getIconForRecom.media[0]"
                                                :class="getIconForRecom.media[1]"
                                            />
                                            <span
                                                class="ml-3"
                                                style="width: 100%; padding-top: 2px"
                                                :class="getIconForRecom.media[1]"
                                                v-text="getIconForRecom.media[2]"
                                            ></span>
                                        </div>
                                    </VExpansionPanelHeader>
                                    <VExpansionPanelContent
                                        eager
                                        class="pt-0"
                                        :class="$style.ExpansionPanelContent"
                                    >
                                        <ProductPanelWrapper class="pt-0">
                                            <media-product ref="mediaProduct" />
                                        </ProductPanelWrapper>
                                    </VExpansionPanelContent>
                                </VExpansionPanel>
                                <VExpansionPanel :class="$style.ExpansionPanel">
                                    <VExpansionPanelHeader
                                        class="collapse-item__header"
                                        :class="$style.ExpansionPanelHeader"
                                    >
                                        <div :class="$style.heading">Описание</div>
                                        <div
                                            v-if="getIconForRecom.descr[3]"
                                            :class="$style.iconWrapper"
                                            class="d-flex align-center"
                                        >
                                            <SvgIcon
                                                :name="getIconForRecom.descr[0]"
                                                :class="getIconForRecom.descr[1]"
                                            />
                                            <span
                                                class="ml-3"
                                                style="width: 100%; padding-top: 2px"
                                                :class="getIconForRecom.descr[1]"
                                                v-text="getIconForRecom.descr[2]"
                                            ></span>
                                        </div>
                                    </VExpansionPanelHeader>
                                    <VExpansionPanelContent
                                        eager
                                        :class="$style.ExpansionPanelContent"
                                    >
                                        <DescProduct
                                            ref="descProduct"
                                            :values="{
                                                descriptions: getProductDescription,
                                            }"
                                            :recommendation="recommendations.desc"
                                            @checkRecommendation="checkRecommendation"
                                        />
                                    </VExpansionPanelContent>
                                </VExpansionPanel>
                                <VExpansionPanel :class="$style.ExpansionPanel">
                                    <VExpansionPanelHeader
                                        class="collapse-item__header"
                                        :class="$style.ExpansionPanelHeader"
                                    >
                                        <div :class="$style.heading">Характеристики</div>
                                        <div
                                            v-if="getIconForRecom.specifications[3]"
                                            :class="$style.iconWrapper"
                                            class="d-flex align-center"
                                        >
                                            <SvgIcon
                                                :name="getIconForRecom.specifications[0]"
                                                :class="getIconForRecom.specifications[1]"
                                            />
                                            <span
                                                class="ml-3"
                                                style="width: 100%; padding-top: 2px"
                                                :class="getIconForRecom.specifications[1]"
                                                v-text="getIconForRecom.specifications[2]"
                                            ></span>
                                        </div>
                                    </VExpansionPanelHeader>
                                    <VExpansionPanelContent
                                        eager
                                        :class="$style.ExpansionPanelContent"
                                    >
                                        <ParamsProduct
                                            ref="paramsProduct"
                                            :values="getCharacteristicsMethod"
                                            :recommendations="recommendationParams"
                                        />
                                    </VExpansionPanelContent>
                                </VExpansionPanel>
                            </VExpansionPanels>
                        </div>
                    </div>
                    <div v-show="selectedTab === 2" class="pb-4">
                        <!-- selectedKeyReq -->
                        <div class="monitor-keys mb-5">
                            <VRow>
                                <VCol lg="6" col="12" sm="12">
                                    <ProgressBar
                                        :value="searchOptimization"
                                        :max="100"
                                        title="Видимость товара"
                                        desc="Текущая видимость"
                                        prefix="%"
                                        color="green"
                                    ></ProgressBar>
                                </VCol>
                            </VRow>
                        </div>
                        <SearchOpt
                            v-if="selectedTab === 2"
                            :keywords-form-values="keywordsFormValues"
                        />
                    </div>
                    <div v-if="selectedTab === 3">
                        <div class="monitor-keys mb-5">
                            <DeponImages
                                :info="{
                                    photo: getProductPhotoUrl,
                                    name: getProductNameShort,
                                    sku: getProductSku,
                                    quantity: getProductQuantity,
                                    url: getProductUrl,
                                    id: productIdLocal,
                                    options: getProductOptions,
                                }"
                            />
                        </div>
                    </div>
                    <ProductChartPositions
                        v-if="[1, 2].includes(selectedTab)"
                        :class="{ 'product-options': selectedTab === 1 }"
                        :selected-tab="selectedTab"
                        :product-sku="getProductSku"
                        :product-id-local="productIdLocal"
                        :product-positions="getProductPositions"
                    />
                </div>
            </div>
            <confirm ref="confirm" />
        </div>

        <div v-show="!isLoadingProduct && !isDepon" ref="prodActions" class="product-actions">
            <div class="product-actions__container">
                <div class="change_list" style="position: relative">
                    <ChangesList style="position: absolute; top: 0; left: 0" />
                </div>
                <VBtn
                    :class="$style.buttonSave"
                    color="accent"
                    depressed
                    class="se-btn"
                    :loading="pending"
                    :disabled="saveBtnCountdown > 0"
                    @click="getProductSaveMethod"
                >
                    {{ saveButtonText }}
                </VBtn>
                <VBtn
                    :class="$style.buttonGoTop"
                    class="default-btn default-btn--size-small default-btn--is-icon"
                    outlined
                    @click="smoothScroll"
                >
                    <SvgIcon name="outlined/chevronUp" />
                </VBtn>
            </div>
        </div>
    </div>
</template>
<script>
    import Vue from 'vue';
    import { VTooltip, VPopover, VClosePopover } from 'v-tooltip';

    Vue.directive('tooltip', VTooltip);
    Vue.directive('close-popover', VClosePopover);
    Vue.component('VPopover', VPopover);

    import FileSaver from 'file-saver';
    import { mapMutations, mapActions, mapGetters, mapState } from 'vuex';
    import EmulateData from '~/assets/js/mixins/emulate';
    import { errorHandler } from '~utils/response.utils';
    import NameProduct from '~/components/pages/product/NameProduct.vue';
    import PriceProduct from '~/components/pages/product/PriceProduct.vue';
    import MediaProduct from '~/components/pages/product/MediaProduct.vue';
    import DescProduct from '~/components/pages/product/DescProduct.vue';
    import ProductChartPositions from '~/components/pages/product/Charts/ProductChartPositions';
    import ProductChartSales from '~/components/pages/product/Charts/ProductChartSales';
    import ParamsProduct from '~/components/pages/product/Params/Index.vue';
    import Confirm from '~/components/common/Confirm.vue';
    import DeponImages from '~/components/pages/product/DeponImages.vue';
    import ProgressBar from '~/components/ui/ProgressBar.vue';

    export default {
        name: 'ProductPage',
        components: {
            NameProduct,
            PriceProduct,
            MediaProduct,
            DescProduct,
            ProductChartPositions,
            ProductChartSales,
            DeponImages,
            ParamsProduct,
            Confirm,
            ProgressBar,
        },
        mixins: [EmulateData],

        beforeRouteLeave(to, from, next) {
            this.setProductId(null);
            if (this?.changes?.length) {
                this.$refs.confirm.show({
                    title: 'Сохранить данные?',
                    text: 'У вас есть несохраненные данные. Если вы покинете эту страницу без сохранения, вы потеряете последние изменения.',
                    btn: {
                        confirm: {
                            text: 'Сохранить и выйти',
                            cls: 'primary-btn primary-btn--size-middle',
                        },
                        cancel: {
                            text: 'Выйти без сохранения',
                            cls: 'default-btn default-btn--size-middle',
                        },
                    },
                    confirm: () => {
                        this.save(true, next);
                    },
                    cancel: () => {
                        next();
                    },
                });

                next(false);
            } else {
                next();
            }
        },

        data() {
            return {
                urlTab: null,
                showErrorDownloadProduct: false,
                selectedTab: 0,
                tabItems: [
                    {
                        title: 'Сводка',
                        component: 'se-product-dashboard',
                        url: 'dashboard',
                        to: `${this.$route.path}?section=dashboard`,
                    },
                    {
                        title: 'Контентная оптимизация',
                        component: 'se-product-cont-opt',
                        url: 'content',
                        to: `${this.$route.path}?section=content`,
                        error: false,
                    },
                    {
                        title: 'Поисковая оптимизация',
                        component: 'se-product-search-opt',
                        url: 'search_opt',
                        to: `${this.$route.path}?section=search_opt`,
                    },
                    {
                        title: 'Защита авторства',
                        component: 'se-product-search-opt',
                        url: 'deposit',
                        to: `${this.$route.path}?section=deposit`,
                    },
                ],
                topScrol: 66,
                isLoadingProduct: false,
                activeTab: 1,
                section: [],
                pending: false,
                isDepon: false,
                recommendations: {
                    desc: false,
                },
                loading: {
                    export: false,
                },
                popovers: {
                    changes: false,
                },
                statisticsSlide: 0,
                saveBtnCountdown: 0,
                sections: ['dashboard', 'search_opt', 'content', 'deposit'],
            };
        },
        head() {
            return {
                htmlAttrs: {
                    class: 'static-rem',
                },
            };
        },

        computed: {
            ...mapState('product', [
                'dataWildberries',
                'infoTop36',
                'contentAlert',
                'commonData',
                'isPriceCheckAborted',
                'pickList',
            ]),
            ...mapGetters('product', [
                'getProduct',
                'getProductStartData',
                'getNameProduct',
                'getDescrProduct',
                'showRecom',
                'getActiveOptionIndex',
                'getWbImages',
            ]),
            ...mapState('user', {
                menuExpanded: 'isMenuExpanded',
            }),
            ...mapState('marketPlaceChart', ['isAnalyticsLoading']),
            ...mapGetters(['isSelectedMp', 'isSelMpIndex']),
            ...mapGetters({
                getNameProduct: 'product/getNameProduct',
                getDescrProduct: 'product/getDescrProduct',
                productId: 'product/getProductId',
                productNameOzon: 'product/GET_PRODUCT_NAME',
                productOzon: 'product/GET_PRODUCT',
                changesList: 'product/GET_CHANGES',
                productWildberries: 'product/getProductWildberries',
                changesCount: 'product/GET_CHANGE_COUNT',
                actionOzon: 'product/GET_ACTION_OZON',
                actionWildberries: 'product/GET_ACTION_WILDBERRIES',
                productHasOptions: 'isProductHasOptions',
                marketplaceSlug: 'getSelectedMarketplaceSlug',
                getEmptyCharacteristics: 'product/getEmptyCharacteristics',
                getDictionariesWildberries: 'product/getDictionariesWildberries',
                pickListSorted: 'product/getPickListSorted',
            }),
            queryForBulkCopy() {
                const { id, bread_crumbs } = this.getProduct;
                let { object: category } = this.getProduct;
                if (this.isSelectedMp.id === 1) {
                    category = bread_crumbs.split('-').map(_ => _.trim());
                    category = category[category.length - 1];
                }
                return { product_id: id, category };
            },
            // TODO: Удалить не используемые свойства
            getIconForRecom() {
                /* eslint-disable */
                const sucIcon = 'filled/checkoutlined';
                const warIcon = 'filled/warningoutlined';
                const sucClass = 'suc-icon';
                const warClass = 'warning-icon';

                const getIconAndClass = (condition, index) => {
                    const res = !condition
                        ? [sucIcon, sucClass, 'Все хорошо!']
                        : [warIcon, warClass, 'Не оптимизировано'];

                    res.push(index < 0 ? false : !this.section.includes(index));
                    return res;
                };

                const {
                    grade,
                    price,
                    photo: media,
                    char: specifications,
                    name,
                    descr,
                } = this.contentAlert;

                const stateRec = {
                    name,
                    price,
                    grade,
                    media,
                    descr,
                    specifications,
                };

                try {
                    Object.keys(stateRec).forEach((key, index) => {
                        stateRec[key] = getIconAndClass(stateRec[key], this.showRecom ? index : -1);
                    });

                    return stateRec;
                } catch (error) {
                    console.error(error);
                    return {
                        name: getIconAndClass(false, 0),
                        price: getIconAndClass(false, 1),
                        grade: getIconAndClass(false, 2),
                        media: getIconAndClass(false, 3),
                        descr: getIconAndClass(false, 4),
                        specifications: getIconAndClass(false, 5),
                    };
                }
            },
            productIdLocal() {
                return Number(this.$route.params.id);
            },
            recommendationTitle() {
                switch (this.marketplaceSlug) {
                    case 'wildberries':
                        return [];

                    default:
                        return this.productOzon?.data?.recomendations?.filter(
                            item => item.header === 'Заголовок'
                        );
                }
            },
            recommendationMedia() {
                switch (this.marketplaceSlug) {
                    case 'wildberries':
                        return [];

                    default:
                        return (
                            this.productOzon?.data?.recomendations?.filter(
                                item => item.header === 'Изображения'
                            ) || []
                        );
                }
            },
            recommendationParams() {
                switch (this.marketplaceSlug) {
                    case 'wildberries':
                        return [];

                    default:
                        return this.productOzon.data?.recomendations?.filter(
                            item => item.header === 'Характеристики'
                        );
                }
            },
            getCharacteristicsMethod() {
                switch (this.marketplaceSlug) {
                    case 'wildberries':
                        return this.characteristicsValuesWildberries;

                    default:
                        return this.characteristicsValuesOzon;
                }
            },
            characteristicsValuesWildberries() {
                const recommended = {};
                if (!this.productWildberries.data || !this.productWildberries.data.addin) {
                    return {};
                }
                const characteristicsAddin = this.productWildberries.data.addin.reduce(
                    (acc, current) => {
                        const item = { ...this.productWildberries.data.addin[current] };
                        item.key = 'characteristics';
                        item.index = current.type;
                        if (current.params[0]?.count) {
                            item.value = current.params[0]?.count;
                        } else if (current.params[0]?.value) {
                            item.value = [];
                            current.params.forEach(el => {
                                item.value.push(el.value);
                            });
                        }

                        item.id = current.type;
                        item.name = current.type;

                        acc[current.type] = item;

                        return acc;
                    },
                    {}
                );

                const characteristicsRequired =
                    this.productWildberries.required_characteristics?.reduce((acc, current) => {
                        const item = {
                            ...this.productWildberries.required_characteristics[current],
                        };
                        item.key = 'characteristics';
                        item.index = current.type;
                        item.value = '';
                        item.id = current.type;
                        item.name = current.type;

                        acc[current.type] = item;

                        return acc;
                    }, {});

                const characteristics = { ...characteristicsAddin, ...characteristicsRequired };

                characteristics['Страна-изготовитель'] = this.getCountryProductuionWildberries;

                const rules = this.productWildberries.recommended_characteristics.addin;

                Object.keys(characteristics).forEach(async (current, index) => {
                    characteristics[current].type = 'String';

                    if (characteristics[current].value === undefined) {
                        characteristics[current].value = '';
                    }

                    const propertyRules = rules.find(el => current === el.type);
                    if (propertyRules) {
                        if (propertyRules.required) {
                            characteristics[current].is_required = 1;
                        }

                        if (propertyRules.isNumber) {
                            characteristics[current].type = 'Integer';
                        }

                        if (propertyRules.dictionary) {
                            characteristics[current].dictionarySlug = propertyRules.dictionary;
                            if (propertyRules.dictionary === '/ext') {
                                characteristics[current].dictionaryType = propertyRules.type;
                            }

                            characteristics[current].options = this.getWildberriesOptions(
                                propertyRules.options
                            );
                            if (!characteristics[current].selected_options) {
                                characteristics[current].selected_options = [];
                            }

                            if (Array.isArray(characteristics[current].value)) {
                                characteristics[current].value.forEach(el => {
                                    characteristics[current].selected_options.push({
                                        id: el,
                                        value: el,
                                    });
                                });
                            }

                            characteristics[current].onlyDictionary =
                                propertyRules.useOnlyDictionaryValues;
                            characteristics[current].maxCount =
                                propertyRules.maxCount && !propertyRules.isBoolean
                                    ? propertyRules.maxCount
                                    : 0;

                            characteristics[current].category = this.productWildberries.object;
                            characteristics[current].isBoolean = propertyRules.isBoolean || false;
                        }
                    }

                    if (current === 'Описание') {
                        characteristics[current].type = 'multiline';
                    }

                    if (current === 'Тнвэд') {
                        characteristics[current].object = this.productWildberries.object;
                    }
                });

                // TODO: Потом переделаем, пока костылим)
                const sO = {
                    ...characteristics,
                    ...recommended,
                };

                try {
                    if (sO['Состав']) {
                        const selectedOptions =
                            typeof sO['Состав'].value === 'string' ||
                            typeof sO['Состав'].value === 'number'
                                ? [
                                      {
                                          id: sO['Состав'].value,
                                          value: sO['Состав'].value,
                                      },
                                  ]
                                : sO['Состав'].value.map(el => ({
                                      id: el,
                                      value: el,
                                  }));

                        sO['Состав'].dictionaryType = 'Состав';
                        sO['Состав'].onlyDictionary = true;
                        sO['Состав'].selected_options = selectedOptions;
                    }

                    if (sO['Бренд']) {
                        sO['Бренд'].maxCount = 1;
                    }

                    if (sO['Пол']) {
                        sO['Пол'].maxCount = 1;
                    }

                    if (sO['Количество предметов в упаковке']) {
                        /* eslint-disable */
                        const nameField = 'Количество предметов в упаковке';

                        const formatValue = value => {
                            if (Array.isArray(value)) value = value[0];
                            if (!value) return 0;
                            return Number((value + '').split(' ')[0]);
                        };
                        const value = formatValue(sO[nameField].value);

                        Object.keys(sO[nameField]).forEach(key => {
                            delete sO[nameField][key];
                        });
                        sO[nameField].id = nameField;
                        sO[nameField].name = nameField;
                        sO[nameField].type = 'Integer';
                        sO[nameField].value = value;
                    }

                    if (sO['Прямые поставки от производителя']) {
                        sO['Прямые поставки от производителя'].maxCount = 1;
                    }
                } catch (error) {
                    console.error(error);
                }

                return sO;
            },
            characteristicsValuesOzon() {
                if (!this.productOzon.data?.characteristics) {
                    return {};
                }

                const recommended = {};
                const characteristics = Object.keys(this.productOzon.data.characteristics).reduce(
                    (acc, current) => {
                        const item = { ...this.productOzon.data.characteristics[current] };
                        item.key = 'characteristics';
                        item.maxCount = item.is_collection > 0 ? 0 : 1;
                        item.index = current;

                        acc[current] = item;

                        return acc;
                    },
                    {}
                );
                try {
                    this.productOzon.data.recomended_characteristics.forEach((current, index) => {
                        if (!characteristics[current.id]) {
                            const item = { ...current };

                            if (item.is_reference) {
                                if (!item.options || !item.options.length) {
                                    item.options = [];
                                }
                                item.selected_options = [];
                                item.maxCount = item.is_collection > 0 ? 0 : 1;
                            }

                            item.value = '';
                            item.key = 'recomended_characteristics';
                            item.index = index;

                            recommended[current.id] = item;
                        }
                    }, {});
                } catch (error) {
                    console.error(error);
                }

                return {
                    ...characteristics,
                    ...recommended,
                };
            },
            getProductFetchMethod() {
                if (this.productIdLocal) {
                    switch (this.marketplaceSlug) {
                        case 'wildberries':
                            return this.loadProductWildberries(this.productIdLocal);

                        default:
                            return this.loadProductOzon(this.productIdLocal);
                    }
                } else {
                    return false;
                }
            },
            getProductSaveMethod() {
                switch (this.marketplaceSlug) {
                    case 'wildberries':
                        return this.saveWildberries;

                    default:
                        return this.saveOzon;
                }
            },
            product() {
                switch (this.marketplaceSlug) {
                    case 'wildberries':
                        return this.productWildberries;

                    default:
                        return this.productOzon;
                }
            },
            getProductUrl() {
                switch (this.marketplaceSlug) {
                    case 'wildberries':
                        return this.productWildberries.url || '';

                    default:
                        return this.productOzon?.data?.url || '';
                }
            },
            getProductSku() {
                switch (this.marketplaceSlug) {
                    case 'wildberries':
                        return this.productWildberries.sku || '';

                    default:
                        return this.productOzon?.data?.sku || '';
                }
            },
            getProductPhotoUrl() {
                switch (this.marketplaceSlug) {
                    case 'wildberries':
                        return this.productWildberries.image || '';

                    default:
                        return this.productOzon?.data?.photo_url || '';
                }
            },
            getProductNameShort() {
                switch (this.marketplaceSlug) {
                    case 'wildberries':
                        return this.productWildberries.title || '';

                    default:
                        return this.productNameOzon || '';
                }
            },
            getProductOptimization() {
                switch (this.marketplaceSlug) {
                    case 'wildberries':
                        return this.productWildberries.optimization || 0;

                    default:
                        return this.productOzon?.data?.optimization || 0;
                }
            },
            hideOptimizationSpinner() {
                return (
                    !isNaN(parseFloat(this.getProductOptimization)) &&
                    !isNaN(this.getProductOptimization - 0)
                );
            },
            getProductPositions() {
                switch (this.marketplaceSlug) {
                    case 'wildberries':
                        return null;

                    default:
                        return (this.productOzon.data && this.productOzon.data.positions) || null;
                }
            },
            getProductRating() {
                switch (this.marketplaceSlug) {
                    case 'wildberries':
                        return false;

                    default:
                        return this.productOzon?.data?.rating || 0;
                }
            },
            getProductMetrics() {
                switch (this.marketplaceSlug) {
                    case 'wildberries':
                        return false;

                    default:
                        return this.productOzon?.data?.metrics;
                }
            },

            getEscrow() {
                switch (this.marketplaceSlug) {
                    case 'wildberries':
                        return this.productWildberries.escrow || {};

                    default:
                        return this.productOzon?.data?.escrow || {};
                }
            },

            getImagesData() {
                switch (this.marketplaceSlug) {
                    case 'wildberries':
                        return this.productWildberries.data.nomenclatures[0].addin.images || [];

                    default:
                        return this.productOzon?.data?.images || [];
                }
            },

            getProductOptions() {
                if (this.productWildberries.data?.nomenclatures) {
                    const options = [];
                    this.productWildberries.data.nomenclatures.forEach((item, index) => {
                        const color = item.addin.find(el => el.type === 'Основной цвет');
                        const images = item.addin.find(el => el.type === 'Фото');
                        const images360 = item.addin.find(el => el.type === 'Фото 360');
                        const youtubecodes = item.addin.find(el => el.type === 'Видео');
                        options.push({
                            name: color?.params?.length ? color.params[0].value : item.nmId,
                            id: item.id || index,
                            nmId: item.nmId,
                            vendorCode: item.vendorCode,
                            price: item.price - item.price,
                            discount: item.discount,
                            categoriesNumber: 4,
                            bestPosition: index === 1 ? 100 : 500,
                            rating: index === 1 ? 4 : 4.5,
                            complect: '',
                            images: images?.params?.length
                                ? images.params.map(item => item.value)
                                : [],
                            images360: images360?.params?.length
                                ? images360.params.map(item => item.value)
                                : [],
                            youtubecodes: youtubecodes?.params?.length
                                ? youtubecodes?.params[0].value
                                : null,
                            baseColor: color?.params?.length ? [color?.params[0].value] : [],
                            additionalColors: item.additionalColors,
                            variations: item.variations,
                        });
                    });
                    return options;
                } else {
                    return [];
                }
            },
            getProductCommonArticul() {
                return this.productWildberries.sku || '';
            },
            getProductNameFull() {
                switch (this.marketplaceSlug) {
                    case 'wildberries':
                        return { title: this.productWildberries.title || '' };

                    default:
                        return { name: this.productOzon.data?.name || '' };
                }
            },
            getProductDescription() {
                const isWb = this.isSelectedMp.id === 2;
                let productData = isWb ? this.productWildberries : this.productOzon;
                productData = isWb
                    ? productData.data?.addin ?? []
                    : productData.data?.descriptions ?? '';

                return isWb
                    ? productData.find(({ type }) => type === 'Описание')?.params[0]?.value || ''
                    : productData;
            },
            getProductQuantity() {
                if (this.isSelectedMp.id === 2) {
                    return this.productWildberries.quantity;
                } else {
                    return this.productOzon?.data?.quantity;
                }
            },
            isEnableGoBack() {
                return Boolean(this.$route?.meta?.isEnableGoBack);
            },
            getStatus() {
                switch (this.marketplaceSlug) {
                    case 'wildberries':
                        return this.productWildberries.status_id || '';

                    default:
                        return false;
                }
            },
            getStatusTime() {
                switch (this.marketplaceSlug) {
                    case 'wildberries':
                        return null;

                    default:
                        return this.productOzon.data?.updated;
                }
            },
            getPositions() {
                switch (this.marketplaceSlug) {
                    case 'wildberries':
                        return null;

                    default:
                        return this.productOzon.data?.positions;
                }
            },
            keywordsFormValues() {
                switch (this.marketplaceSlug) {
                    case 'wildberries':
                        return this.keywordsFormValuesWildberries;
                    default:
                        return this.keywordsFormValuesOzon;
                }
            },

            contentOptimization() {
                switch (this.marketplaceSlug) {
                    case 'wildberries':
                        return this.productWildberries.content_optimization || 0;

                    default:
                        return this.productOzon?.data?.content_optimization || 0;
                }
            },
            searchOptimization() {
                switch (this.marketplaceSlug) {
                    case 'wildberries':
                        return this.productWildberries.search_optimization || 0;

                    default:
                        return this.productOzon?.data?.search_optimization || 0;
                }
            },
            visibilityOptimization() {
                switch (this.marketplaceSlug) {
                    case 'wildberries':
                        return this.productWildberries.visibility_optimization || 0;

                    default:
                        return this.productOzon?.data?.visibility_optimization || 0;
                }
            },
            saveButtonText() {
                if (this.saveBtnCountdown > 0) {
                    const plural = this.$options.filters.plural(this.saveBtnCountdown, [
                        'секунду',
                        'секунды',
                        'секунд',
                    ]);
                    return `Подождите ${this.saveBtnCountdown} ${plural} ...`;
                } else {
                    switch (this.marketplaceSlug) {
                        case 'wildberries':
                            return 'Отправить в WB';

                        default:
                            return 'Отправить в OZON';
                    }
                }
            },
            keywordsFormValuesWildberries() {
                const keyRequests =
                    this.productWildberries.data?.addin?.find(el => el.type === 'Ключевые слова') ||
                    [];
                const packageSet = this.getWildberriesAddinValuesAsString('Комплектация');
                const purpose = this.getWildberriesAddinValuesAsString('Назначение');
                const direction = this.getWildberriesAddinValuesAsString('Направление');

                return {
                    title: this.productWildberries.title || '',
                    keyRequest1: keyRequests?.params?.[0]?.value || '',
                    keyRequest2: keyRequests?.params?.[1]?.value || '',
                    keyRequest3: keyRequests?.params?.[2]?.value || '',
                    packageSet: packageSet || '',
                    purpose: purpose || '',
                    direction: direction || '',
                    description: this.getDescrProduct,
                };
            },
            keywordsFormValuesOzon() {
                return {
                    title: this.productOzon?.data?.name || '',
                    description: this.getDescrProduct,
                };
            },
            showErrorBanner() {
                return !this.showPending;
            },
            getCountryProductuionWildberries() {
                const selected_options = this.productWildberries.country_production?.length
                    ? [
                          {
                              id: this.productWildberries.country_production,
                              value: this.productWildberries.country_production,
                          },
                      ]
                    : [];
                const value = this.productWildberries.country_production?.length
                    ? [this.productWildberries.country_production]
                    : '';
                return {
                    id: 'Страна-изготовитель',
                    index: 'Страна-изготовитель',
                    name: 'Страна-изготовитель',
                    useOnlyDictionaryValues: false,
                    maxCount: 1,
                    dictionarySlug: '/countries',
                    key: 'characteristics',
                    is_required: true,
                    selected_options,
                    value,
                };
            },
            isProductStockAvailabile() {
                if (this.isSelectedMp.id === 2) {
                    return (
                        this.productWildberries.nomenclatures[this.getActiveOptionIndex].quantity >
                        0
                    );
                } else {
                    return this.productOzon?.data?.quantity > 0;
                }
            },
            productStockAvailabilityParams() {
                if (this.isProductStockAvailabile) {
                    return {
                        text: 'на складе',
                        color: '#20C274',
                        icon: 'outlined/stockAvailable',
                    };
                }
                return {
                    text: 'нет на складе',
                    color: '#FF3981',
                    icon: 'outlined/stockNotAvailable',
                };
            },
            getOptimizationHistory() {
                let src;
                if (this.isSelectedMp.id === 2) {
                    src = this.productWildberries.optimisationHistory || [];
                } else {
                    src = this.productOzon.data?.optimisationHistory || [];
                }

                return src.reduce((acc, el, i) => {
                    Object.keys(el).forEach(key => {
                        if (!acc[key]) {
                            acc[key] = [];
                            acc[key].push(el[key]);
                        } else {
                            Vue.set(acc[key], [i], el[key]);
                        }
                    });
                    return acc;
                }, {});
            },
            optimizationHistoryContent() {
                return this.getOptimizationHistory.content_percent || null;
            },
            optimizationHistorySearch() {
                return this.getOptimizationHistory.search_percent || null;
            },
            optimizationHistoryLabels() {
                return this.getOptimizationHistory.report_date || null;
            },
            isProd() {
                return process.env.SERVER_TYPE === 'prod';
            },
            isDemo() {
                return process.env.SERVER_TYPE === 'demo';
            },
            disabledTabs() {
                return this.isProd || this.isDemo ? [3] : [];
            },
            imagesNumber() {
                try {
                    if (this.isSelectedMp.id === 2) {
                        return this.productWildberries.data_nomenclatures[
                            this.getActiveOptionIndex
                        ].addin.find(elem => elem.type === 'Фото').params.length;
                    } else {
                        return this.productOzon.data.images.length;
                    }
                } catch {
                    return 0;
                }
            },
            isProductHasManyOptions() {
                return this.getProductOptions.length > 1;
            },
        },
        watch: {
            selectedTab(val) {
                this.isDepon = this.selectedTab === 3;
                history.pushState(null, 'title', this.tabItems[val].to);
            },
            changesCount() {
                this.hideAccordion();
            },
            '$route.params.id': {
                immediate: true,
                handler(val) {
                    this.setProductId(Number(val));
                },
            },
            '$route.query': {
                immediate: true,
                handler(val) {
                    if (val.section) {
                        this.selectedTab = this.tabItems.indexOf(
                            this.tabItems.find(item => item.url === val.section)
                        );
                    } else {
                        this.selectedTab = 0;
                    }
                },
            },
            saveBtnCountdown(val) {
                if (val > 0) {
                    setTimeout(() => {
                        this.saveBtnCountdown--;
                    }, 1000);
                }
            },
            menuExpanded() {
                // TODO: Необходимо переделать основной шаблон,
                // или написать плавную анимацию просчета, чтобы избавиться от дергания
                setTimeout(this.resetTmpl, 200);
            },
        },
        async created() {
            this.selectedTab = 0;
            this.isLoadingProduct = true;
            try {
                await this.getPickList(this.productIdLocal);

                if (this.isSelectedMp.id === 2) {
                    await this.loadProductWildberries(this.productIdLocal);
                    await this.getDataOnTop36(this.getProductSku);
                    this.isLoadingProduct = false;
                    await this.fetchDataChartWB({
                        sku: this.getProductSku,
                        params: this.chartParams,
                    });
                } else {
                    await this.getDataOnTop36(this.productIdLocal);
                    await this.loadProductOzon(this.productIdLocal);
                    this.isLoadingProduct = false;
                }
                this.setCommonField({ field: 'title', value: this.getNameProduct });
                this.setCommonField({ field: 'descr', value: this.getDescrProduct });
            } catch (error) {
                if (error === 404) {
                    return this.$nuxt.error({ statusCode: 404, message: 'Товар не найден' });
                }

                this.isLoadingProduct = false;
                this.showErrorDownloadProduct = true;
                // this.isLoadingProduct = false;
            }
        },
        mounted() {
            this.resetTmpl();
            if (this.productOzon.data || this.productWildberries) {
                this.hideAccordion();
            }
        },
        beforeDestroy() {
            window.removeEventListener('scroll', this.scrollEvent);
            this.$store.commit('product/SET_CHANGES', []);
            this.setProductId(null);
        },
        methods: {
            ...mapMutations('product', [
                'setCommonField',
                'setAdditionalPricesOzon',
                'setAdditionalPricesWildberries',
            ]),
            ...mapActions('product', ['getDataOnTop36', 'getPickList']),
            ...mapActions({
                setProductId: 'product/setProductId',
                loadProductOzon: 'product/loadProductOzon',
                loadProductWildberries: 'product/loadProductWildberries',
                setDictionaryWildberries: 'product/setDictionaryWildberries',
                setProductOptions: 'product/setProductOptions',
                setProductOzonUpdated: 'product/setProductOzonUpdated',
                setProductWildberriesUpdated: 'product/setProductWildberriesUpdated',
                fetchDataChartWB: 'marketPlaceChart/fetchDataChartWB',
                fetchDataChartPositionsOzon: 'marketPlaceChart/fetchDataChartPositionsOzon',
                fetchDataChartSalesOzon: 'marketPlaceChart/fetchDataChartSalesOzon',
            }),
            goToElem(el) {
                el.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center',
                });
            },
            resetTmpl() {
                try {
                    const { prodActions, pageContainer } = this.$refs;
                    const actionContainer = prodActions.querySelector(
                        '.product-actions__container'
                    );
                    const containerWidth = pageContainer.offsetWidth;

                    actionContainer.style.width = `${containerWidth}px`;
                } catch (error) {
                    console.error(error);
                }
            },
            scrollEvent() {
                this.topScrol = window.pageYOffset;
            },
            hideAccordion() {
                this.$nuxt.$vuetify.goTo(0);
            },
            checkRecommendation(payload) {
                this.recommendations.desc = payload;
            },
            handleGoBack() {
                this.$router.push({
                    name: 'products',
                    params: { marketplace: this.$route.params.marketplace },
                });
            },
            exportProduct() {
                this.loading.export = true;

                this.$axios
                    .$get(`/api/vp/v2/products/${this.productId}/download-pdf-recomendations`)
                    .then(response => {
                        const blob = new Blob([response], {
                            type: 'application/pdf; charset=utf-8',
                        });
                        FileSaver.saveAs(blob, 'export.pdf');
                    })
                    .finally(() => {
                        this.loading.export = false;
                    });
            },
            smoothScroll() {
                this.$nuxt.$vuetify.goTo(0);
            },
            validatReqChar() {
                const { weight, depth, height, width } = this.$store.state.product.data;
                const checkres =
                    Boolean(weight) && Boolean(depth) && Boolean(height) && Boolean(width);

                if (!checkres) {
                    this.$notify.create({
                        message: 'Необходимо заполнить вес, длину, ширину и высоту упаковки',
                        type: 'negative',
                    });
                }
                return checkres;
            },
            async saveOzon(callback = false, next = null) {
                const name = this.$refs.nameProduct.getInputs();
                const desc = this.$refs.descProduct.getInputs();
                const params = this.$refs.paramsProduct.getInputs();
                const price = this.$refs.priceProduct.getInputs();

                const values = await Promise.all([name, price, desc, params]);

                if (values.includes(false)) {
                    return;
                } else if (!this.validatReqChar()) {
                    this.$notify.create({
                        message: 'Вы не заполнили обязательные поля',
                        type: 'negative',
                    });
                } else {
                    this.pending = true;
                    const valuesToOverwrite = {};
                    const keywords = this.pickListSorted.flat().filter(el => el.isActive === true);
                    const request = values.reduce((acc, current) => {
                        if (current.keywordsForm) {
                            if (this.selectedTab === 2) {
                                valuesToOverwrite.name = current.title;
                                valuesToOverwrite.descriptions = current.description;
                            }
                        } else {
                            Object.keys(current).forEach(key => {
                                acc[key] = current[key];
                            });
                        }

                        Object.keys(valuesToOverwrite).forEach(el => {
                            acc[el] = valuesToOverwrite[el];
                        });

                        return acc;
                    }, {});

                    const { weight, depth, height, width } = this.$store.state.product.data;
                    const { title: name, descr: descriptions } =
                        this.$store.state.product.commonData;

                    const productNotChangedPrices = this.productNotChangedPrices({
                        price: request.price,
                    });

                    const isProductPriceNotChanged = productNotChangedPrices.length;

                    if (isProductPriceNotChanged && !this.isPriceCheckAborted) {
                        const externalId = this.$store.state.product.data.external_id;
                        const product = {
                            externalId,
                            price: request.price,
                            same: true,
                        };

                        const resultProduct = await this.getOzonPriceById(product);
                        const isShowSeConfirm = !resultProduct.same;

                        if (isShowSeConfirm) {
                            const settingPrice = resultProduct.price;
                            const additionalPricesOzon = [];
                            additionalPricesOzon.push({
                                externalId: resultProduct.externalId,
                                price: resultProduct.newPrice,
                            });
                            this.setAdditionalPricesOzon(additionalPricesOzon);

                            const text = `Цена и скидка товара на маркетплейсе отличается от отправляемой в Ozon.
                                    Уверены, что хотите установить цену ${settingPrice}\u00A0₽?`;

                            this.$modal.open({
                                component: 'SeConfirm',
                                attrs: {
                                    text,
                                    cancel: this.priceCancelOzon,
                                    apply: this.priceApplyOzon,
                                },
                            });
                        } else {
                            this.priceApplyOzon();
                        }

                        return;
                    }

                    await this.savePickListRequest();

                    this.$axios
                        .put(`/api/vp/v2/products/${this.productIdLocal}/modify-product`, {
                            ...request,
                            name,
                            descriptions,
                            weight,
                            depth,
                            images: this.getProduct.images,
                            images360: this.getProduct.images360,
                            premium_price: this.getProduct.premium_price,
                            height,
                            width,
                            data: keywords,
                        })
                        .then(response => {
                            this.$store.commit('product/SET_CHANGES', []);

                            this.$notify.create({
                                message:
                                    'Цена товара до скидки обновится в OZON не сразу. Скидка на товар может обнулиться на 4 минуты. Цена для покупателя при этом сохранится.',
                                type: 'positive',
                            });

                            this.$notify.create({
                                message: 'Данные товара успешно обновлены.',
                                type: 'positive',
                            });

                            if (callback && next) {
                                next();
                            }

                            this.sendToMarketplace(this.marketplaceSlug, this.productIdLocal);
                            this.saveBtnCountdown = 30;
                        })
                        .catch(({ response: error }) => {
                            errorHandler(error, this.$notify);
                        })
                        .finally(() => {
                            this.pending = false;
                        });
                }
            },
            async saveWildberries(callback = false, next = null) {
                // TODO: Нужно переписать этот метод состоящий полностью из костылей
                let nomenclatures = await this.$refs.priceColorsMediaProduct.getRefs();
                const name = this.$refs.nameProduct.getInputs();
                const desc = this.$refs.descProduct.getInputs();
                const params = this.$refs.paramsProduct.getInputs();

                nomenclatures = Array.from(nomenclatures);
                nomenclatures.push({});

                try {
                    const nomImgLink = nomenclatures[nomenclatures.length - 1];
                    const {
                        data: { nomenclatures: nom },
                    } = this.getProduct;

                    const { addin } = nom[0];
                    const photo = addin.find(({ type }) => type === 'Фото');
                    nomImgLink.images = photo.params.map(({ value }) => value);
                } catch (error) {
                    console.error(error);
                }

                const values = await Promise.all([params, name, desc]);

                if (values.includes(false) || nomenclatures.includes(false)) {
                } else {
                    this.pending = true;

                    const data = {
                        nomenclatures: [],
                        data: {
                            nomenclatures: [],
                            addin: [],
                        },
                        data_nomenclatures: [],
                    };
                    const nomenclaturesRaw = {};
                    const addin = [];

                    data.data_nomenclatures = JSON.parse(
                        JSON.stringify(this.dataWildberries.data_nomenclatures)
                    );

                    const {
                        data: { addin: addinCopy },
                    } = this.dataWildberries;

                    nomenclatures.forEach((el, index) => {
                        try {
                            if (el.images) {
                                el.images360 = [];
                                el.youtubecodes = [];
                                el.nomenclature = { index: 0 };
                            }

                            if (!nomenclaturesRaw[el.nomenclature.index]) {
                                nomenclaturesRaw[el.nomenclature.index] = {};
                            }
                            if (!nomenclaturesRaw[el.nomenclature.index].variations) {
                                nomenclaturesRaw[el.nomenclature.index].variations = [];
                            }
                            if (el.barcode) {
                                nomenclaturesRaw[el.nomenclature.index].variations.push(
                                    this.getWildberriesVariation(el)
                                );
                            } else {
                                nomenclaturesRaw[el.nomenclature.index] = {
                                    ...nomenclaturesRaw[el.nomenclature.index],
                                    ...el,
                                };
                            }
                        } catch {
                            console.error(1, 'ошибка', index, el);
                        }
                    });

                    const nomenclaturesProcessed =
                        this.getWildberriesNomenclatures(nomenclaturesRaw);

                    Object.assign(
                        data.data.nomenclatures,
                        nomenclaturesProcessed.dataNomenclatures
                    );
                    Object.assign(data.nomenclatures, nomenclaturesProcessed.nomenclatures);

                    for (let i = 0; i < values.length; i++) {
                        if (values[i].characteristics) {
                            values[i].characteristics.forEach(el => {
                                if (el.id === 'Страна-изготовитель') {
                                    data.country_production = el.selected_options[0] || '';
                                } else if (el.selected_options) {
                                    addin.push(
                                        this.getWildberriesCharacteristic(
                                            el.id,
                                            el.selected_options
                                        )
                                    );
                                } else {
                                    addin.push(this.getWildberriesCharacteristic(el.id, el.value));
                                }
                            });
                        } else if (values[i].title && this.selectedTab === 1) {
                            data.title = values[i].title;
                        } else if (values[i].descriptions && this.selectedTab === 1) {
                            addin.push(
                                this.getWildberriesCharacteristic(
                                    'Описание',
                                    values[i].descriptions
                                )
                            );
                        } else if (values[i].keywordsForm && this.selectedTab === 2) {
                            // если активна вкладка "поисковая оптимизация" - обрабатываем форму ключевых запросов
                            const keyRequests = [
                                values[i].keyRequest1,
                                values[i].keyRequest2,
                                values[i].keyRequest3,
                            ];
                            this.findAndReplaceCharacteristicWildberries(
                                'Ключевые слова',
                                keyRequests,
                                addin
                            );
                            this.findAndReplaceCharacteristicWildberries(
                                'Комплектация',
                                values[i].packageSet.match(/.{1,100}/g),
                                addin
                            );
                            this.findAndReplaceCharacteristicWildberries(
                                'Назначение',
                                values[i].purpose.match(/.{1,100}/g),
                                addin
                            );
                            this.findAndReplaceCharacteristicWildberries(
                                'Направление',
                                values[i].direction.match(/.{1,100}/g),
                                addin
                            );
                            this.findAndReplaceCharacteristicWildberries(
                                'Описание',
                                values[i].description,
                                addin
                            );
                            this.findAndReplaceCharacteristicWildberries(
                                'Наименование',
                                values[i].title,
                                addin
                            );
                        }
                    }

                    const { title, descr } = this.$store.state.product.commonData;

                    const cusFields = {
                        Описание: descr,
                        Наименование: title,
                    };
                    const statusFoundField = [];

                    for (let i = 0; i < addin.length; i++) {
                        const item = addin[i];
                        if (Object.keys(cusFields).includes(item.type)) {
                            item.params = [{ value: cusFields[item.type] }];
                            statusFoundField.push(item.type);
                        }
                    }

                    const insertedFields = Object.keys(cusFields).filter(
                        item => !statusFoundField.includes(item)
                    );

                    insertedFields.forEach(field => {
                        addin.push({
                            type: field,
                            params: [{ value: cusFields[field] }],
                        });
                    });

                    Object.assign(data.data.addin, addin);
                    const keyRequest = this.pickListSorted
                        .flat()
                        .filter(el => el.isActive === true);

                    const productMerged = {
                        ...this.productWildberries,
                        ...data,
                        key_request: keyRequest,
                        barcodes: this.getBarcodesWildberries(data),
                    };

                    productMerged.data.addin = productMerged.data.addin.filter(item => {
                        return (
                            (Array.isArray(item.params) && item.params.length) ||
                            (typeof item.params === 'string' && item.params)
                        );
                    });

                    const nomen = productMerged.data.nomenclatures;

                    nomen.forEach((item, index) => {
                        const addin = item.addin;
                        const linkPhoto = addin.find(({ type }) => type === 'Фото');

                        const wbPhotos = Object.values(this.getWbImages)[index].map(_ => ({
                            value: _,
                        }));
                        if (linkPhoto) {
                            linkPhoto.params = wbPhotos;
                        } else {
                            item.addin.push({
                                type: 'Фото',
                                params: Object.values(this.getWbImages)[index].map(_ => ({
                                    value: _,
                                })),
                            });
                        }

                        productMerged.data_nomenclatures[index].price = item.price;
                        productMerged.data_nomenclatures[index].discount = item.discount;
                        productMerged.data_nomenclatures[index].promocode = item.promocode;
                    });

                    const productNotChangedPrices = this.productNotChangedPrices({ nomen });
                    const isProductPriceNotChanged = productNotChangedPrices.length;

                    if (isProductPriceNotChanged && !this.isPriceCheckAborted) {
                        // Применяем волшебный запрос, который сделал Слава
                        const nmidArr = productNotChangedPrices.map(el => {
                            return {
                                nmid: el.nmId,
                                price: el.price,
                                discount: el.discount,
                            };
                        });

                        const resultArr = await this.getPriceByNmid(nmidArr, 0);

                        let isShowSeConfirm = false;
                        let settingPrice = -1;
                        let additionalPricesWB = [];
                        resultArr.forEach(el => {
                            if (!el.same) {
                                isShowSeConfirm = true;
                                additionalPricesWB.push({
                                    nmid: el.nmid,
                                    price: el.newPrice,
                                    discount: el.newDiscount,
                                });

                                if (settingPrice === -1) {
                                    settingPrice = el.price;
                                }
                            }
                        });

                        this.setAdditionalPricesWildberries(additionalPricesWB);

                        if (isShowSeConfirm) {
                            const text = `Цена и скидка товара на маркетплейсе отличается от отправляемой в Wildberries.
                                    Уверены, что хотите установить цену ${settingPrice}\u00A0₽?`;

                            this.$modal.open({
                                component: 'SeConfirm',
                                attrs: {
                                    text,
                                    cancel: this.priceCancelWB,
                                    apply: this.priceApplyWB,
                                },
                            });
                        } else {
                            this.priceApplyWB();
                        }

                        return;
                    }

                    await this.savePickListRequest();

                    this.$axios
                        .put(
                            `/api/vp/v2/wildberries/products/${this.productIdLocal}`,
                            productMerged
                        )
                        .then(response => {
                            this.$store.commit('product/SET_CHANGES', []);

                            this.$notify.create({
                                message:
                                    'Данные товара успешно обновлены. Информация на сайте Wildberries обновляется 24 часа',
                                type: 'positive',
                            });

                            if (callback && next) {
                                next();
                            }

                            this.saveBtnCountdown = 30;
                        })
                        .catch(({ response }) => {
                            errorHandler(response, this.$notify);
                        })
                        .finally(() => {
                            this.pending = false;
                        });
                }
            },
            async getPriceByNmid(arr, i) {
                if (i === arr.length) {
                    return arr;
                } else {
                    const requestData = {
                        query: {
                            nmid: [arr[i].nmid],
                        },
                    };

                    arr[i].same = true;

                    try {
                        const {
                            data: { data: data },
                        } = await this.$axios.post(
                            `/api/vp/v2/wildberries/platform/get-products-price`,
                            requestData
                        );

                        if (
                            parseInt(data[0].price) !==
                            arr[i].price - arr[i].price * arr[i].discount
                        ) {
                            arr[i].same = false;
                            arr[i].newPrice = parseInt(data[0].price);
                            arr[i].newDiscount = data[0].discount;
                        }
                    } catch (e) {
                        i++;
                        return this.getPriceByNmid(arr, i);
                    }

                    i++;
                    return this.getPriceByNmid(arr, i);
                }
            },
            async getOzonPriceById(product) {
                const requestData = {
                    query: {
                        external_id: [product.externalId],
                    },
                };

                try {
                    const {
                        data: { data: data },
                    } = await this.$axios.post(
                        `/api/vp/v2/ozon/platform/get-products-price`,
                        requestData
                    );

                    if (parseInt(data[product.externalId].price) !== product.price) {
                        product.same = false;
                        product.newPrice = parseInt(data[product.externalId].price);
                    }
                } catch (e) {
                    return product;
                }

                return product;
            },
            priceCheckAbort() {
                this.$store.commit('product/setPriceCheckAbort', true);
            },
            priceCancel() {
                this.priceCheckAbort();
                this.pending = false;
                this.selectedTab = 1;
                this.section = [1];
                const priceNomen = this.$refs.priceNomen.$el;
                const priceNomenBox = priceNomen.getBoundingClientRect();
                this.$nuxt.$vuetify.goTo(priceNomenBox.top - 64);
            },
            priceCancelWB() {
                this.priceCancel();
            },
            priceApplyWB() {
                this.priceCheckAbort();
                this.saveWildberries();
            },
            priceCancelOzon() {
                this.priceCancel();
            },
            priceApplyOzon() {
                this.priceCheckAbort();
                this.saveOzon();
            },
            getBarcodesWildberries(src) {
                return src.data.nomenclatures.reduce((acc, current) => {
                    current.variations.forEach(el => {
                        acc.push(el.barcodes[0]);
                    });
                    return acc;
                }, []);
            },
            sendToMarketplace(marketplace, productId) {
                const url =
                    marketplace === 'wildberries'
                        ? `/api/vp/v2/wildberries/products/${productId}/sync`
                        : `/api/vp/v2/ozon/product-send/${productId}`;
                this.pending = true;
                this.$axios
                    .get(url)
                    .then(() => {
                        // this.$notify.create({
                        //     message: 'Данные товара успешно обновлены',
                        //     type: 'positive',
                        // });
                    })
                    .catch(({ response }) => {
                        errorHandler(response, this.$notify);
                    })
                    .finally(() => {
                        this.pending = false;
                    });
            },
            getWildberriesCharacteristic(type, value) {
                const valueProcessed = [];
                if (Array.isArray(value)) {
                    value.forEach(el => {
                        valueProcessed.push({
                            value: el,
                        });
                    });
                } else if (typeof value === 'number') {
                    valueProcessed.push({
                        count: value,
                        units: this.getUnits(
                            type,
                            this.productWildberries.recommended_characteristics.addin
                        ),
                    });
                } else {
                    valueProcessed.push({
                        value,
                    });
                }
                return {
                    type,
                    params: valueProcessed,
                };
            },
            getUnits(type, source) {
                const dataObj = Object.values(source).find(el => el.type === type);
                if (dataObj?.units) {
                    return dataObj.units[0];
                } else {
                    return 0;
                }
            },
            optimizeColor(val) {
                if (!val) return '#cfcfcf';
                else if (val > 84) {
                    return '#1ee08f';
                }
                if (val > 34) {
                    return '#ffc164';
                }
                return '#f56094';
            },
            getWildberriesOptions(obj) {
                return obj ? Object.values(obj) : [];
            },
            getWildberriesNomenclatures(obj) {
                const dataNomenclatures = [];
                const nomenclatures = [];
                Object.values(obj).forEach((nom, nomIndex) => {
                    if (!nom.addin) {
                        nom.addin = [];
                    }
                    Object.keys(nom).forEach(key => {
                        if (key === 'images') {
                            nom.addin.push(this.getWildberriesCharacteristic('Фото', nom[key]));
                        } else if (key === 'images360') {
                            nom.addin.push(this.getWildberriesCharacteristic('Фото 360', nom[key]));
                        } else if (key === 'baseColor') {
                            nom.addin.push(
                                this.getWildberriesCharacteristic('Основной цвет', nom[key])
                            );
                        } else if (key === 'youtubecodes') {
                            nom.addin.push(this.getWildberriesCharacteristic('Видео', nom[key]));
                        } else if (key === 'discount' || key === 'price' || key === 'promocode') {
                            if (!nomenclatures[nomIndex]) {
                                nomenclatures[nomIndex] = {};
                            }
                            nomenclatures[nomIndex][key] = nom[key];
                        }
                    });

                    dataNomenclatures.push(nom);

                    if (!nom.variations?.length) {
                        const existingNomenclature =
                            this.productWildberries.data.nomenclatures.find(
                                el => el.nmId === nom.nmId
                            );
                        if (existingNomenclature) {
                            nom.variations = existingNomenclature.variations;

                            const barcodeNomenclatureBase = nom.barcodeBase;
                            if (barcodeNomenclatureBase?.length) {
                                nom.variations[0].barcodes[0] = barcodeNomenclatureBase;
                            }
                        }
                    }
                });

                return { nomenclatures, dataNomenclatures };
            },
            getWildberriesVariation(src) {
                const variation = {
                    addin: [],
                    barcodes: [],
                    id: src.id,
                };
                Object.keys(src).forEach(key => {
                    if (key === 'barcode') {
                        variation.barcodes.push(src[key]);
                    } else if (key === 'sizeName') {
                        variation.addin.push(this.getWildberriesCharacteristic('Размер', src[key]));
                    } else if (key === 'sizeValue') {
                        variation.addin.push(
                            this.getWildberriesCharacteristic('Рос. размер', src[key])
                        );
                    } else if (key === 'price') {
                        variation.addin.push(
                            this.getWildberriesCharacteristic('Розничная цена', src[key])
                        );
                    }
                });
                return variation;
            },
            getWildberriesAddinValuesAsString(key) {
                return this.productWildberries.data?.addin
                    ?.find(el => el.type === key)
                    ?.params?.reduce((acc, current) => {
                        acc.push(current.value);
                        return acc;
                    }, [])
                    .join(', ');
            },
            findAndReplaceCharacteristicWildberries(title, value, addin) {
                const characteristicIndex = addin.findIndex(el => el.type === title);
                if (characteristicIndex > -1) {
                    return addin.splice(
                        characteristicIndex,
                        1,
                        this.getWildberriesCharacteristic(title, value)
                    );
                } else {
                    return addin.push(this.getWildberriesCharacteristic(title, value));
                }
            },
            generateUrlWildberries(nmId) {
                return `https://www.wildberries.ru/catalog/${nmId}/detail.aspx`;
            },
            handleOpenTab(tab) {
                if (tab) {
                    this.selectedTab = tab;
                    this.$refs.sePageTab.setMenuActiveMenu(tab);
                }
            },
            productNotChangedPrices({ nomen, price }) {
                const productStartData = this.getProductStartData;
                const notChangedPrices = [];

                if (price) {
                    const startPrice = productStartData.price;

                    if (price === startPrice) {
                        notChangedPrices.push({
                            id: productStartData.id,
                            price: productStartData.price,
                        });
                    }
                } else if (nomen) {
                    const startNomen = productStartData.data_nomenclatures;
                    const nomenObj = {};

                    nomen.forEach(el => {
                        nomenObj[el.nmId] = el.price;
                    });

                    startNomen.forEach(el => {
                        if (nomenObj[el.nmId] === el.price) {
                            notChangedPrices.push({
                                nmId: el.nmId,
                                price: el.price,
                                discount: el.discount,
                            });
                        }
                    });
                }

                return notChangedPrices;
            },
            async savePickListRequest() {
                let topic = ['/api/vp/v2/add_goods_list', '/api/vp/v2/wildberries/pick_list'];

                topic = `${topic[this.isSelectedMp.id - 1]}?user[id]=1&product_id=${
                    this.productId
                }&delete_old=1`;

                try {
                    await this.$axios.post(topic, {
                        data: this.pickList.map(({ name, popularity }) => ({
                            name,
                            popularity,
                        })),
                    });
                } catch (error) {
                    console.error(error);
                }
            },
        },
    };
</script>
<style lang="scss">
    .page-app {
        &__header {
            font-size: 26px;
        }
    }

    .product-options {
        max-width: 1300px;

        &:first-child {
            margin-bottom: 8px;
        }
    }

    .product-actions {
        position: fixed;
        bottom: 0;
        z-index: 3;
        width: 100%;
        background: white;
        box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.06);

        &__container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 32px;
        }
    }

    .warning-icon {
        width: 18px;
        height: 17px;
        fill: $color-pink-dark;
        color: $color-pink-dark;
        font-weight: bold;

        &.span {
            width: 100%;
        }
    }

    .suc-icon {
        width: 20px;
        height: 20px;
        fill: $success;
        color: $success;
        font-weight: bold;

        &.span {
            width: 100%;
        }
    }

    .product-status {
        &__icon {
            width: 14px !important;
            height: 14px !important;
        }
    }
</style>
<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .ProductPage {
        display: flex;
        flex: 1;
        flex-direction: column;

        @include respond-to(md) {
            padding: 0;
        }

        & .ProductPageTitle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;

            @include respond-to(md) {
                margin-bottom: 0;
                background-color: $white;
            }

            @include phone-large {
                flex-wrap: wrap;

                span.title-h1 {
                    width: 100%;
                    margin-bottom: 8px;
                }
            }

            & .ArrowBack {
                position: relative;
                display: none;
                width: var(--header-h) !important;
                min-width: var(--header-h) !important;
                max-width: var(--header-h) !important;
                height: 100% !important;
                padding: 0 !important;

                @include respond-to(md) {
                    display: block;
                }
            }

            & .ProductPageHeaderText {
                @include respond-to(md) {
                    flex-grow: 1;
                    padding: 0 1rem;
                }
            }

            & .ProductPageTitleStatus {
                display: none;

                @include respond-to(md) {
                    display: block;
                }
            }

            & .heading {
                display: flex;
                align-items: center;
                width: 100%;

                @include respond-to(md) {
                    height: var(--header-h);
                    padding-right: 1rem;
                }
            }
        }

        & .ProductTabContainer {
            position: relative;
            display: inline-block;
            margin-bottom: 16px;
            border-radius: 16px;
            background: $black;

            & .ProductTabBox {
                display: flex;
                padding: 4px;

                & .ProductTabItem {
                    padding: 10.5px 16px;
                    border-radius: 16px;
                    border: 2px solid $black;
                    background: $white;
                    font-weight: 500;
                    font-size: 20px;
                    transition: all 0.3s ease-out;
                    cursor: pointer;

                    &:global(.inactive-tab) {
                        background: transparent;
                        color: $white;
                    }
                }
            }
        }

        & .ProductSearch {
            position: relative;
            display: flex;
            justify-content: space-between;
            border-radius: 16px;
            background-color: #fff;

            @include cardShadow2();

            @include respond-to(md) {
                flex-direction: column;
            }

            & .ProductSearchContent {
                flex: 0 1 70%;
                padding: 16px;
                border-right: 1px solid #e9edf2;

                @include respond-to(md) {
                    order: 1;
                }

                & .headingIndent {
                    margin-bottom: 15.5px;
                    font-size: 24px;
                    font-weight: 500;
                }
            }

            & .ProductSearchBar {
                flex: 0 1 30%;
            }
        }

        & :global(.v-input__append-outer) {
            margin-top: 12px !important;
        }

        & :global(.v-alert) {
            &:global(.error--text .v-alert__icon) {
                display: none;
            }

            &:global(.v-alert--outlined) {
                border: none !important;
            }
        }
    }

    .loadingWrapper {
        @include centerer;
    }

    .iconWrapper {
        display: flex;
        align-items: center;
        flex: 0;
        margin-right: 16px;
        margin-left: 16px;

        .icon {
            width: 24px;
            height: 24px;
            color: $error;

            &.iconError {
                color: $error;
            }
        }
    }

    .countersIcon {
        width: 40px !important;
        height: 40px !important;
    }

    .heading {
        display: flex;
        justify-content: space-between;
        flex: 0 1 auto !important;
        font-weight: 500;
        font-size: 20px;
        line-height: 1.25;
        color: $base-900;
    }

    .headingActions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }

    .headingArticul {
        display: flex;
        align-items: center;
    }

    .StatusButtonDesktop {
        @include respond-to(md) {
            display: none;
        }
    }

    .buttonSave {
        width: 220px;

        @include respond-to(sm) {
            width: 100%;
        }
    }

    .buttonGoTop {
        @include respond-to(sm) {
            display: none;
        }
    }

    .ExpansionPanels {
        border-radius: 0;

        .ExpansionPanel {
            border-bottom: 1px solid #e9edf2;

            &:last-child {
                border-bottom: none;
            }
        }

        .ExpansionPanelHeader {
            border-radius: 0;
            background: $white;

            &:not(:global(.v-expansion-panel-header--active)) {
                border-radius: 0;
            }

            &:global(.v-expansion-panel-header--active) {
                & ~ .ExpansionPanelContent {
                    & :global(.product-content__panel) {
                        border-radius: 0 0 8px 8px;
                    }
                }
            }
        }
    }

    .periodPickerBtn {
        flex: 1;
        font-size: 14px !important;
        font-style: normal !important;
        line-height: 19px !important;
        color: $base-600 !important;
        font-weight: bold !important;

        &:global(.v-btn--active) {
            background-color: $white !important;
            box-shadow: 0 4px 32px rgba(146, 85, 85, 0.06) !important;
            color: $base-900 !important;

            &:before {
                display: none;
            }
        }
    }
</style>

<style lang="scss">
    .product-card {
        &__title {
            margin-bottom: 16px;

            @include phone-large {
                flex-wrap: wrap;

                span.title-h1 {
                    width: 100%;
                    margin-bottom: 8px;
                }
            }
        }

        &-btns-wrapper {
            display: flex;
            gap: 16px;
        }
    }

    .product-preview {
        display: flex;
        align-items: center;
        margin-top: 16px;

        img {
            width: 80px;
            max-width: 80px;
            height: 80px;
            object-fit: contain;
        }

        &__data {
            display: flex;
            flex-direction: column;
            margin-left: 16px;

            span {
                display: block;
            }
        }

        &__sku {
            text-decoration: none;
            line-height: 1;
            color: inherit;

            svg {
                margin-bottom: -3px;
            }

            &:hover {
                color: $color-purple-primary;
            }

            &-icon {
                width: 16px !important;
                height: 16px !important;
            }

            &-title {
                margin-bottom: 4px;
            }
        }
    }

    .product-summary-title {
        margin: 8px 16px 0 0;
    }

    .product-summary-container {
        padding-top: 8px;
    }

    .product-summary {
        display: flex;
        flex-wrap: wrap;
        flex-direction: column;
        margin-top: 8px;
        margin-bottom: -16px;

        @include respond-to(sm) {
            overflow-x: auto;
            flex-direction: row;
            gap: 1rem;
            flex-wrap: nowrap;
        }

        &__item {
            display: flex;
            align-items: center;
            width: 25%;
            min-width: 240px;
            margin: 16px 0;

            @include respond-to(sm) {
                margin: 0;
            }
        }

        &__icon {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 80px;
            min-width: 80px;
            height: 80px;
            border-radius: 16px;
            background: $color-main-background;
            color: $color-gray-light-300;

            &.is-active {
                background: rgba(113, 11, 255, 0.08);
                color: $color-purple-primary;

                &:before {
                    content: '';
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    z-index: 0;
                    width: 40px;
                    height: 30px;
                    border-radius: 50%;
                    background: $color-purple-primary;
                    opacity: 0.35;
                    transform: translate(-50%, 0%);
                    filter: blur(10px);
                }
            }

            svg {
                z-index: 1;
                width: 40px;
                height: 40px;
            }
        }

        &__data {
            margin-left: 12px;

            &-note {
                display: block;
                margin-bottom: 2px;
                font-size: 12px;
                color: $color-gray-dark;
            }

            &-title {
                display: block;
                font-size: 16px;
                line-height: 1.35;
                color: $color-gray-dark-800;
            }

            &-subtitle {
                display: inline-block;
                margin-top: 4px;
                font-size: 10px;
                color: $color-gray-dark;
            }
        }

        &__values {
            display: flex;
            align-items: center;
            margin-top: 6px;
        }

        &__value {
            font-size: 20px;
            line-height: 1;
            color: $color-main-font;
            font-weight: 700;
        }

        &__direction {
            display: flex;
            align-items: center;
            height: 24px;
            margin-top: 2px;
            margin-left: 8px;
            padding: 0 10px;
            border-radius: 4px;
            background: rgba(32, 194, 116, 0.2);
            font-size: 12px;
            line-height: 1;
            color: $color-green-secondary;
            font-weight: 700;

            &.up {
                background-color: rgba(32, 194, 116, 0.06);
                color: $color-green-secondary;

                img {
                    width: 6px;
                }
            }

            &.down {
                background-color: rgba(255, 57, 129, 0.06);
                color: $color-red-secondary;

                img {
                    width: 6px;
                }
            }

            img {
                width: 16px;
                margin-left: 6px;
            }
        }
    }

    .product-content {
        @include respond-to(md) {
            max-width: 100%;
            margin: 0;
        }

        &__row {
            display: flex;
            justify-content: space-between;
            margin: -8px;

            @include respond-to(md) {
                max-width: 100%;
                margin: 0;
            }

            @include respond-to(sm) {
                flex-direction: column;
            }

            @include phone-large {
                flex-wrap: wrap;
            }
        }

        &__base-data {
            margin-bottom: 8px;

            @include respond-to(md) {
                gap: 0.5rem;
            }

            @include respond-to(sm) {
                flex-direction: column;
                gap: 0;
            }
        }

        &__panel {
            overflow: auto;
            margin: 8px;
            padding: 16px;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);

            @include md-max {
                .title-h5 {
                    font-size: size(20);
                }
            }

            @include respond-to(md) {
                margin: 8px 0;
                border-radius: 0;
            }

            &--base-data {
                width: 48%;
                min-width: 400px;

                @include md-max {
                    width: 40%;
                    min-width: 40%;
                }

                @include respond-to(md) {
                    width: calc(50% - 0.25rem);
                    min-width: calc(50% - 0.25rem);
                }

                @include respond-to(sm) {
                    width: 100%;
                    min-width: 100%;
                }
            }

            &--full-width {
                width: 100%;
            }
        }

        &__filter-top {
            position: relative;
            min-width: max-content;
            margin: 8px 0 0 0;

            &--dates {
                width: 300px;
                max-width: 100%;
                margin-right: 12px;
            }
        }

        &__charts-grid {
            display: grid;
            width: 100%;
            grid-gap: 16px;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            grid-template-rows: none;
            grid-auto-flow: row dense;
        }
    }

    .product-btn-wrap {
        display: flex;
        justify-content: flex-end;
        width: 100%;

        @include respond-to(md) {
            padding-right: 1.5rem;
        }

        @include respond-to(sm) {
            padding: 1.5rem;
        }

        .default-btn--is-icon {
            margin-left: 16px;
        }
    }

    .custom-popover-changes-log {
        max-height: 300px;
        padding: 16px;
    }

    .changes-log-product {
        padding-right: 16px;

        &__item {
            display: flex;
            margin-bottom: 16px;
            padding: 8px 12px;
            border-radius: 8px;
            background: $color-main-background;
            flex-direction: column;

            &:last-child {
                margin-bottom: 0;
            }

            button {
                width: max-content;
                text-decoration-line: underline;
                font-size: size(14);
                line-height: 1;
                color: $color-red-secondary;
                font-weight: 500;
            }
        }

        &__date {
            font-weight: 500;
            font-size: size(14);
            line-height: 1;
            color: $color-gray-dark;
        }

        &__title {
            display: block;
            margin: 8px 0;
            font-size: size(14);
            line-height: 1.35;
            color: $color-main-font;
            font-weight: 500;
        }
    }

    .progress-bar {
        flex: auto;
    }

    .product-options {
        position: relative;
        min-height: 321px;
        border-radius: 16px;

        .product-options__expansion {
            overflow: hidden;
            border-radius: 16px;
            background: $white;
            box-shadow: 0 4px 32px rgba(0, 0, 0, 0.06);

            &--full {
                width: 100%;
            }
        }

        .product-options__monitor {
            display: flex;
            min-width: 100%;
            margin-bottom: 16px;
            padding: 0 16px;
            border-radius: 16px;
            background: $white;
            box-shadow: 0 4px 32px rgba(0, 0, 0, 0.06);

            &--fixed {
                //position: fixed;
                //top: 65px;
                //right: 2rem;
            }

            .product-options__monitor-title {
                padding: 16px 0;
                font-size: 20px;
                font-style: normal;
                font-weight: 500;
                line-height: 27px;
                color: #2f3640;
            }
        }
    }

    .monitor-keys {
        padding: 16px;
        border-radius: 16px;
        background: #fff;

        @include cardShadow2();

        &__title {
            padding-bottom: 16px;
            font-size: 20px;
            font-style: normal;
            font-weight: 500;
            line-height: 27px;
            color: #2f3640;
        }
    }

    .product-status {
        &__icon {
            width: 12px;
            height: 12px;
        }
    }
</style>
