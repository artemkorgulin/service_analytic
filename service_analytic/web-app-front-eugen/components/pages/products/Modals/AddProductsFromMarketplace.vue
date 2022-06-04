<template>
    <BaseDrawer v-model="isShow" width="600px">
        <div :class="$style.AddProductsFromMarketplace" class="custom-scrollbar">
            <div class="wrap-head d-flex justify-space-between align-center">
                <h3 :class="$style.heading">Выберите товары</h3>
                <VImg aspect-ratio="1" max-width="40" contain :src="isSelectedMp.image" />
            </div>
            <div v-if="overflowMessage" ref="overflowMessage" :class="$style.skuLimitWarning">
                <span>Вы можете добавить до {{ userPlan.sku }} товаров.</span>
                <br />
                <span>
                    Чтобы добавить больше --
                    <a href="/tariffs">смените тарифный план.</a>
                </span>
                <br />
                <span>Вы также можете вернуть в отслеживание ранее удаленные товары.</span>
            </div>
            <div v-if="totalProducts === 0" :class="$style.skuLimitWarning">
                <span>
                    Обратите внимание, после ввода Api-ключа, загрузка товаров может занять
                    некоторое время
                </span>
            </div>
            <div :class="$style.selectAll"></div>
            <div :class="$style.searchCategories">
                <SearchInput
                    v-model="search"
                    color="#710bff"
                    background-color="#f9f9f9"
                    :placeholder="searchPlaceholder"
                    outline-light
                    @input="searchProd"
                />
                <VBtn :outlined="allProducts" color="primary" @click="resetTmpl()">
                    Выбрано: {{ addedProducts.length }}/ {{ canChoose }}
                </VBtn>
            </div>
            <div class="filter-settings d-flex">
                <div
                    class="filter-stock"
                    @click.stop="filterStockAvailability = !filterStockAvailability"
                >
                    <BaseCheckbox :value="filterStockAvailability"></BaseCheckbox>
                    <span class="filter-stock__label">В продаже</span>
                </div>
                <div
                    v-if="isSelectedMp.id === 1"
                    class="filter-settings__fbo-fbs fbo-fbs-switcher d-flex align-center"
                >
                    <span class="fbo-fbs-switcher__title">FBS</span>
                    <VSwitch
                        v-model="isEnableOzonFbo"
                        style="margin: 0"
                        class="ml-4 p-0 m-0"
                        inset
                        flat
                        dense
                        hide-details
                        @change="setOzonEnableFBO()"
                    ></VSwitch>
                    <span class="fbo-fbs-switcher__title">FBO</span>
                </div>
            </div>
            <perfect-scrollbar
                ref="scroll"
                class="product-scroll"
                @ps-scroll-y="onScrollMP"
                @ps-y-reach-end="actionEndScroll"
            >
                <div
                    class="ob-selected"
                    style="
                        position: absolute;
                        z-index: -1;
                        width: 100%;
                        height: 100%;
                        background: none;
                        pointer-events: none;
                    "
                ></div>
                <div v-if="allProducts" class="products-list">
                    <div
                        class="products-list__actions d-flex justify-space-between pr-3 align-center"
                        :class="{ active: isShowGTUbtn }"
                    >
                        <div class="products-list__total-list">
                            <ul class="inline-list" style="font-size: 14px">
                                <li class="mr-3">Бренды: {{ totalBrands }}</li>
                                <li>Товары: {{ totalProducts }}</li>
                            </ul>
                        </div>
                        <VBtn
                            text
                            small
                            color="primary"
                            :disabled="!openedCategory.length"
                            @click="openedCategory = []"
                        >
                            Свернуть все
                        </VBtn>
                    </div>
                    <div
                        v-for="(item, index) in brandsList"
                        :key="item.brand + index"
                        class="products-list__item-cat"
                    >
                        <div
                            v-ripple
                            class="products-list__ic-header"
                            @click="openBrand(item.brand, $event)"
                        >
                            <BaseCheckbox
                                :value="setStateCheckBoxByBrand(item.brand) === 'All'"
                                auto-size
                                class="mr-4 cat-cb"
                            />
                            <span class="ic-header__title" style="flex: auto">
                                {{ item.brand }}
                            </span>
                            <v-progress-circular
                                v-if="isLoadingCat && item.brand === nameLoadingCat"
                                indeterminate
                                :size="18"
                                color="#dedede"
                            ></v-progress-circular>
                            <SvgIcon
                                v-else
                                :name="`outlined/${
                                    openedCategory.includes(item.brand)
                                        ? 'chevronUp'
                                        : 'chevronDown'
                                }`"
                                style="width: 18px"
                            />
                        </div>
                        <div
                            v-if="openedCategory.includes(item.brand)"
                            class="products-list__ic-content"
                        >
                            <div
                                v-for="subItem in downloadedCategory[item.brand]"
                                :key="subItem.id"
                                class="products-list__sub-item sub-item"
                                :class="{ selected: subItem.isSelected }"
                            >
                                <BaseCheckbox
                                    :value="subItem.isSelected"
                                    auto-size
                                    class="mr-4"
                                    @click="addOrRemProd(subItem)"
                                />
                                <div class="sub-item__img ml-1 mr-5">
                                    <VImg
                                        :src="
                                            subItem.image || 'https://i.stack.imgur.com/GNhxO.png'
                                        "
                                        lazy-src="https://i.stack.imgur.com/GNhxO.png"
                                        max-width="30"
                                        aspect-ratio="1"
                                        style="width: 100px"
                                    />
                                </div>
                                <span class="ic-header__title" style="flex: auto">
                                    {{ subItem.title }}
                                </span>
                                <div v-if="isSelectedMp.id === 1">
                                    <div v-if="Number(subItem.sku)">
                                        <VChip
                                            label
                                            :color="
                                                getOzonProductAvailabilitySettings(subItem.quantity)
                                                    .bgColor
                                            "
                                            :text-color="
                                                getOzonProductAvailabilitySettings(subItem.quantity)
                                                    .color
                                            "
                                            small
                                            style="font-weight: 700"
                                        >
                                            {{
                                                getOzonProductAvailabilitySettings(subItem.quantity)
                                                    .label
                                            }}
                                            <SvgIcon
                                                v-if="
                                                    getOzonProductAvailabilitySettings(
                                                        subItem.quantity
                                                    ).icon
                                                "
                                                :name="`filled/${
                                                    getOzonProductAvailabilitySettings(
                                                        subItem.quantity
                                                    ).icon
                                                }`"
                                                style="margin-left: 8px"
                                            />
                                        </VChip>
                                    </div>
                                </div>
                                <div v-else>
                                    <ProductsListIconWithTooltip
                                        :status="subItem.quantity ? 'success' : 'fail'"
                                        :icon="
                                            subItem.quantity
                                                ? 'outlined/stockAvailable'
                                                : 'outlined/stockNotAvailable'
                                        "
                                        :message="subItem.quantity ? 'Продаётся' : 'Нет на складе'"
                                    />
                                </div>

                                <a
                                    :href="subItem.url"
                                    target="_blank"
                                    :class="$style.productLineLink"
                                    class="ml-2"
                                >
                                    {{ subItem.sku }}
                                    <SvgIcon
                                        name="outlined/link"
                                        style="min-width: 18px; padding-top: 4px"
                                    />
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="text-center pt-3 pb-3">
                        <v-progress-circular
                            v-if="isLoadingBrand"
                            indeterminate
                            :size="40"
                        ></v-progress-circular>
                    </div>
                </div>
                <SelectedProducts
                    v-else
                    :selected-products="addedProducts"
                    :search="search"
                    @delProd="addOrRemProd"
                ></SelectedProducts>
                <v-scroll-y-transition>
                    <div v-if="isShowGTUbtn" class="scroll-btn-up" @click="goToUpMP()">
                        <VBtn color="#E9EDF2" depressed block large>Наверх</VBtn>
                    </div>
                </v-scroll-y-transition>
            </perfect-scrollbar>
        </div>
        <div class="draw-actions">
            <VBtn
                :color="succProductAddition ? '#1EE08F' : 'primary'"
                :style="succProductAddition ? 'color: #fff' : ''"
                :disabled="!addedProducts.length"
                block
                large
                :loading="isLoadingActAddProd"
                @click="actionAddProducts()"
            >
                {{ succProductAddition ? 'Успешно' : 'Добавить товары' }}
            </VBtn>
        </div>
    </BaseDrawer>
</template>

<script>
    /* eslint-disable */
    import { mapActions, mapGetters, mapMutations } from 'vuex';
    import { defineComponent } from '@nuxtjs/composition-api';
    import debounce from 'lodash/debounce';
    import InfiniteLoading from 'vue-infinite-loading';
    import onboarding from '~mixins/onboarding.mixin';
    import { setStorage, getStorage } from '~utils/storage.utils.js';

    export default defineComponent({
        name: 'AddProductsFromMarketplace',
        mixins: [onboarding],
        components: {
            InfiniteLoading,
        },
        data() {
            return {
                overflowMessage: false,
                allProducts: true,
                selBrandPage: 1,
                lastPage: undefined,
                openedCategory: [],
                brandsList: [],
                downloadedCategory: {},
                isLoadingBrand: false,
                isLoadingCat: false,
                isLoadingActAddProd: false,
                succProductAddition: false,
                nameLoadingCat: '',
                addedProducts: [],
                totalProducts: undefined,
                totalBrands: undefined,
                isShow: true,
                search: null,
                brandCheckboxes: [],
                isShowGTUbtn: false,
                timeDebounceSearch: 500,
                serverSearch: false,
                debounceSearchByBrand: null,
                notification: () => {},
                searchPlaceholder: 'Поиск',
                filterStockAvailability: false,
                isEnableOzonFbo: true,
            };
        },
        computed: {
            ...mapGetters(['isSelectedMp', 'userPlan', 'isSelMpIndex']),
            ...mapGetters('products', ['getTotalProducts', 'getDeletedProducts']),

            canChoose() {
                return this.userPlan.sku - this.getTotalProducts + this.getDeletedProducts;
            },
            requestParams() {
                const req = {
                    availability: this.filterStockAvailability ? 1 : 0,
                };
                return [
                    {
                        ...req,
                        type: ['fbs', 'fbo'][Number(this.isEnableOzonFbo)],
                    },
                    req,
                ][this.isSelMpIndex];
            },
        },
        watch: {
            async filterStockAvailability() {
                await applyFilter();
            },
        },
        async created() {
            this.debounceSearchByBrand = debounce(this.getBrands, this.timeDebounceSearch);
            await this.getBrands();
            await this.getNotActiveProducts();

            this.setDrawerAfterEnter(this.createOnBoarding);
        },
        methods: {
            ...mapActions({
                loadProducts: 'products/LOAD_PRODUCTS',
            }),
            ...mapMutations('onBoarding', ['setActiveElements']),
            ...mapMutations('modal', ['setDrawerAfterEnter']),

            async applyFilter() {
                this.brandsList = [];
                this.downloadedCategory = {};
                this.openedCategory = [];
                this.totalBrands = 0;
                this.totalProducts = 0;
                await this.getBrands();
                await this.getNotActiveProducts();
            },
            createOnBoarding() {
                if (this.brandsList.length > 0) {
                    const elements = [
                        {
                            el: document.getElementsByClassName('ob-selected')[0],
                            intro: '<ol class="se-ob-num-list"><li> Найдите нужный бренд и раскройте список</li><li>Выберите нужный товар</li></ol>',
                            pos: 'left',
                        },
                        {
                            el: document.querySelector('.draw-actions'),
                            intro: 'Нажмите, чтобы добавить товары в отслеживание',
                            pos: 'top',
                            notTheEnd: true,
                            clickToNext: true,
                        },
                    ];

                    const createOnBoardingParams = {
                        elements,
                        isDisplayOnboard: true,
                        timeout: 0,
                    };

                    this.createOnBoardingByParams(createOnBoardingParams);
                }
            },

            createOnBoarding2() {
                const elements = [
                    {
                        el: document.getElementsByClassName('custom-search')[0],
                        intro: 'Используйте поиск по названию, артикулу маркетплейса или категории, чтобы найти нужный товар',
                        pos: 'right',
                    },
                ];

                const createOnBoardingParams = {
                    elements,
                    isDisplayOnboard: true,
                };

                this.createOnBoardingByParams(createOnBoardingParams);
            },

            async getBrands() {
                try {
                    this.isLoadingBrand = true;
                    const { key } = this.isSelectedMp;
                    let topic = `/api/vp/v2/${key}/select-not-active-brands?page=${this.selBrandPage}`;
                    if (this.search) topic += `&search=${this.search}`;
                    const {
                        data: { data: brands, last_page: lastPage, total },
                    } = await this.$axios.get(topic, { params: this.requestParams });

                    this.lastPage = lastPage;
                    this.brandsList = [...this.brandsList, ...brands];
                    this.totalBrands = this.totalBrands || total;
                    this.isLoadingBrand = false;
                } catch (error) {
                    console.error('Error retrieving brand list', error);
                }
            },
            async getProductsByBrand(brand, checkAll = false) {
                try {
                    if (brand === this.nameLoadingCat && this.isLoadingCat) {
                        return;
                    }

                    this.switchLoadingCat(true, brand);
                    const { key } = this.isSelectedMp;
                    let topic = `/api/vp/v2/${key}/select-not-active-products?brand=${encodeURIComponent(
                        brand
                    )}`;

                    if (this.search) topic += `&search=${encodeURIComponent(this.search)}`;

                    const {
                        data: { data },
                    } = await this.$axios.get(topic, { params: this.requestParams });

                    this.downloadedCategory[brand] = data;

                    const canBeMarkedAndAdded =
                        checkAll && this.checkOnPossibAdd(this.downloadedCategory[brand], true);

                    if (canBeMarkedAndAdded) {
                        this.downloadedCategory[brand].forEach(item => {
                            item.isSelected = checkAll;
                        });

                        this.addedProducts = [
                            ...this.addedProducts,
                            ...this.downloadedCategory[brand],
                        ];
                    }

                    this.switchLoadingCat(false);
                } catch (error) {
                    console.error('Error retrieving products by brand', error);
                }
            },
            async getNotActiveProducts() {
                try {
                    const { key } = this.isSelectedMp;
                    const topic = `/api/vp/v2/${key}/select-not-active-products`;
                    const {
                        data: { totalCount },
                    } = await this.$axios.get(topic, { params: this.requestParams });

                    this.totalProducts = totalCount;
                } catch (error) {
                    console.error('Error getting the number of goods', error);
                }
            },
            async openBrand(brand, e) {
                const checkboxClass = 'cat-cb';
                const classHeader = 'products-list__ic-header';
                let headerTab;

                (function findHeaderEl(el) {
                    if (Array.from(el.classList).includes(classHeader)) {
                        headerTab = el;
                    } else {
                        findHeaderEl(el.parentNode);
                    }
                })(e.target);

                const baseCheckBox = headerTab.getElementsByClassName(checkboxClass)[0];

                if (baseCheckBox.contains(e.target)) {
                    this.setActionСheckboxByBrand(brand);
                    return;
                }

                if (this.openedCategory.includes(brand)) {
                    const index = this.openedCategory.indexOf(brand);
                    this.openedCategory.splice(index, 1);
                    return;
                }

                const dlCatKeys = Object.keys(this.downloadedCategory);
                if (!dlCatKeys.includes(brand)) {
                    await this.getProductsByBrand(brand);
                }
                this.openedCategory.push(brand);
            },
            async actionAddProducts() {
                try {
                    this.isLoadingActAddProd = true;
                    const { key } = this.isSelectedMp;
                    const topic = `/api/vp/v2/${key}/activate-not-active-products`;
                    const ids = this.addedProducts.map(({ id }) => id);
                    await this.$axios.post(topic, {
                        ids,
                    });
                    this.succProductAddition = true;
                    this.isLoadingActAddProd = false;
                    setTimeout(() => {
                        this.loadProducts({
                            type: 'common',
                            reload: true,
                            marketplace: key,
                        });
                        this.$sendGtm('add_prod');
                        if (this.userPlan.price) {
                            this.$sendGtm('sendprod_paid');
                        } else {
                            this.$sendGtm('sendprod_free');
                        }
                        this.$modal.close();
                        const isOnboardActive = this.$store.getters['onBoarding/isOnboardActive'];
                        if (isOnboardActive) {
                            this.createOnBoarding2();
                        }
                    }, 1000);
                } catch (error) {
                    console.error('Error while adding products to tracked', error);
                    this.$notify.create({
                        message: error.response?.data?.error?.message || error,
                        type: 'negative',
                    });
                    this.isLoadingActAddProd = false;
                }
            },
            async addOrRemProd(item) {
                if (!item.isSelected && !this.checkOnPossibAdd([item])) {
                    return;
                }

                item.isSelected = !item.isSelected;

                if (item.isSelected) {
                    this.addedProducts.push(item);
                } else {
                    this.removeProducts(item);
                }
            },
            addProducts(item) {
                // TODO: Две нижнии функции выполняют одно и то же действие
                // TODO: Обдумать излишнюю проверку
                const index = this.addedProducts.findIndex(({ id }) => item.id === id);
                if (index === -1) {
                    this.addedProducts.push(item);
                }
            },
            removeProducts(item) {
                // TODO: Обдумать излишнюю проверку
                const index = this.addedProducts.findIndex(({ id }) => item.id === id);
                if (index !== -1) {
                    this.addedProducts.splice(index, 1);
                }
            },
            async setActionСheckboxByBrand(brand) {
                // Если товары не загружены, то загрузить их и отметить
                const dlCatKeys = Object.keys(this.downloadedCategory);
                if (!dlCatKeys.includes(brand)) {
                    await this.getProductsByBrand(brand, true);
                    this.openedCategory.push(brand);
                    return;
                }

                const stateOfAllCheckedCB = this.setStateCheckBoxByBrand(brand);
                const stateMarked = stateOfAllCheckedCB !== 'All';

                if (stateMarked && !this.checkOnPossibAdd(this.downloadedCategory[brand])) {
                    return;
                }

                this.downloadedCategory[brand].forEach((item, index) => {
                    item.isSelected = stateMarked;
                    if (stateMarked) {
                        this.addProducts(item);
                    } else {
                        this.removeProducts(item);
                    }
                });

                this.$forceUpdate();
            },
            setStateCheckBoxByBrand(brand) {
                // TODO: Поменять когда переведу на счетчик

                if (!this.downloadedCategory[brand] || !this.downloadedCategory[brand].length)
                    return 'Nothing';
                const downloadCat = this.downloadedCategory[brand] || [];
                const markedItems = downloadCat.filter(({ isSelected }) => isSelected);

                if (downloadCat.length === markedItems.length) {
                    // Если отмечены все товары
                    return 'All';
                } else if (markedItems.length > 0) {
                    // Если отмечены некоторые
                    return 'Some';
                } else {
                    // Если ничего не отмечено
                    return 'Nothing';
                }
            },
            switchLoadingCat(state, brand) {
                this.isLoadingCat = state;
                if (state) {
                    this.nameLoadingCat = brand;
                } else {
                    this.nameLoadingCat = '';
                }
            },
            goToUpMP() {
                this.$refs.scroll.$el.scrollTop = 0;
            },
            onScrollMP() {
                const { scrollTop } = this.$refs.scroll.$el;
                this.isShowGTUbtn = scrollTop > 10;
            },
            actionEndScroll() {
                if (
                    this.selBrandPage !== this.lastPage &&
                    !this.isLoadingBrand &&
                    this.allProducts &&
                    this.brandsList.length
                ) {
                    this.selBrandPage += 1;
                    this.getBrands();
                }
            },
            searchProd() {
                this.brandsList = [];
                this.openedCategory = [];

                if (this.allProducts) {
                    this.selBrandPage = 1;
                    this.lastPage = undefined;
                    this.debounceSearchByBrand();
                }
            },
            resetTmpl() {
                this.allProducts = !this.allProducts;
                this.search = '';
                this.searchProd();
            },
            checkOnPossibAdd(arrProducts, isDownloaded = false) {
                const { canChoose, addedProducts } = this;
                let checkValue;

                if (isDownloaded) {
                    checkValue = addedProducts.length + arrProducts.length;
                } else {
                    const notIsMarked = arrProducts.filter(({ isSelected }) => !isSelected);
                    checkValue = addedProducts.length + notIsMarked.length;
                }

                if (checkValue > canChoose) {
                    this.showNotif();
                    return false;
                }

                return true;
            },
            showNotif() {
                this.overflowMessage = true;
                const interval = setInterval(() => {
                    const { overflowMessage } = this.$refs;

                    if (overflowMessage) {
                        overflowMessage.style.transition = 'all ease 0.2s';
                        overflowMessage.style.background = '#FFC24D';

                        setTimeout(() => {
                            overflowMessage.style.background = '#F9F9F9';
                            overflowMessage.style.color = '#000';
                        }, 400);
                        clearInterval(interval);
                    }
                }, 100);
            },
            getOzonProductAvailabilitySettings(quantity) {
                return {
                    color: quantity ? '#20c274' : '#fc6e90',
                    bgColor: quantity ? 'rgba(32, 194, 116, 0.08)' : 'rgba(255, 11, 153, 0.08)',
                    icon: quantity ? 'check' : 'close',
                    label: this.isEnableOzonFbo ? 'FBO' : 'FBS',
                };
            },
            setOzonEnableFBO() {
                let data = {};
                getStorage({
                    key: 'OzonEnableFBO',
                    callBack: returndata => {
                        data = returndata;
                    },
                });

                if (data === false) {
                    data = {};
                }

                data[this.isSelectedMp.userMpId] = this.isEnableOzonFbo;
                setStorage({ data, key: 'OzonEnableFBO' });
                this.applyFilter();
            },
        },
        mounted() {
            let storage = {};

            getStorage({
                key: 'OzonEnableFBO',
                callBack: data => {
                    storage = data;
                },
            });

            if (storage && storage.hasOwnProperty(this.isSelectedMp.userMpId)) {
                this.isEnableOzonFbo = storage[this.isSelectedMp.userMpId];
            }
        },
    });
</script>
<style lang="scss" scoped>
    .ob-selected {
        pointer-events: none;
    }

    .products-list {
        font-size: 14px;

        &__item-cat {
            &:first-child .products-list__ic-header {
                border-top: 1px solid $border-color;
            }
        }

        &__ic-header {
            display: flex;
            align-items: center;
            padding: 20px 16px;
            border-bottom: 1px solid $border-color;
            cursor: pointer;
            font-weight: 700;
        }

        &__actions {
            position: sticky;
            top: 0;
            z-index: 10;
            padding: 16px;
            border-top: 1px solid $border-color;
            border-bottom: 1px solid $border-color;
            background: #fff;
            transition: all 0.2s ease;

            &.active {
                /* stylelint-disable */
                box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.3);
            }
        }

        &__sub-item {
            display: flex;
            align-items: center;
            padding: 20px 16px;
            font-weight: 500;
            &.selected {
                background: $selected-item-color;
            }
        }

        &__ic-content {
            border-bottom: 1px solid $border-color;
        }
    }

    .draw-actions {
        padding: 0px 16px 24px 16px;
    }

    .wrap-head {
        padding: 0px 16px;
    }

    .scroll-btn-up {
        position: sticky;
        bottom: 0;
        width: 100%;
        padding: 0 24px 16px 24px;
        text-align: center;
        opacity: 0.8;
        transition: 0.3s all ease;
        cursor: pointer;

        &:hover {
            opacity: 1;
        }
    }

    .filter-settings {
        font-size: 14px;
        font-weight: 500;
        gap: 16px;
        justify-content: flex-end;
        padding: 0 16px;
    }

    .filter-stock {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-switch-wrapper {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-switch {
        margin: 0;
        padding: 0;
    }
</style>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .AddProductsFromMarketplace {
        overflow: auto;
        display: flex;
        flex-direction: column;
        gap: 1rem;
        height: 100%;
        max-height: 100%;
        padding: 24px 0;
        color: $base-900;
    }

    .productLine {
        display: flex;
        align-items: center;
        gap: size(8);
        font-size: size(14);
        line-height: 1.36;

        &.productLineList {
            padding: size(8) 0;
        }

        & .productLineImage {
            flex: none;
            width: size(32);
            height: size(32);
        }

        & .productLineBold {
            flex-grow: 1;
            font-weight: bold;
        }

        & .productLineText {
            flex-grow: 1;
            font-weight: 500;
        }

        &Link {
            display: flex;
            align-items: center;
            gap: 8px;

            &:hover {
                color: #710bff;
            }
        }
    }

    .heading {
        text-align: center;
        font-size: 26px;
        font-weight: 600;
    }

    .blockBorder {
        padding: size(16);
        border-radius: size(8);
        border: 1px solid $base-400;
    }

    .marketplaceItemTop {
        display: flex;
        align-items: center;

        & :global(.v-image) {
            flex: none;
            width: 32px;
            height: 32px;
            margin-right: 14px;
        }

        .marketplaceItemTopTitle {
            font-weight: 600;
            font-size: 20px;
        }

        .marketplaceItemTopStatus {
            margin-left: auto;
            padding: size(4) size(8);
            border-radius: size(8);
            background-color: $success;
            font-size: 12px;
            line-height: 1.3;
            font-weight: bold;
            color: $white;
        }
    }

    .skuLimitWarning {
        border-radius: 8px;
        margin: 0 16px;
        padding: size(8);
        background-color: $base-100;
        text-align: center;

        & > span {
            font-size: size(14);
            line-height: 1.36;
            font-weight: 500;
        }

        & a {
            color: $primary-500;
        }
    }

    .selectAll {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 1rem;

        & .selectAllBtn {
            margin-right: auto;
        }
    }

    .searchCategories {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0 16px;
    }

    .searchCategoriesFlexEnd {
        justify-content: flex-end;
    }

    .productsAvailable {
        flex-grow: 1;
        padding: 2px 1px;
    }

    .buttonProceed {
        flex: none;
    }

    .wrapper {
        max-height: calc(100% - 40px) !important;
        margin-top: 0;
        gap: 16px;
    }
</style>

<style lang="scss">
    .fbo-fbs-switcher .v-input,
    .fbo-fbs-switcher .v-input--selection-controls__ripple,
    .fbo-fbs-switcher .v-input--switch__track,
    .fbo-fbs-switcher .v-input--switch__thumb {
        color: #710bff !important;
        caret-color: #710bff !important;
    }
</style>
