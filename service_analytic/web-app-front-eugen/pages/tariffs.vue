<template>
    <div class="tarrifs-page">
        <Page :title="title">
            <v-row>
                <v-col lg="6" sm="12">
                    <div class="se-card">
                        <div class="card-body">
                            <v-expansion-panels accordion flat multiple>
                                <template v-for="item in servicesSorted.noCorp">
                                    <v-expansion-panel
                                        v-if="item.alias !== 'ad'"
                                        :key="item.alias"
                                        class="tariff-item p-0 m-0"
                                        :class="{ disabled: item.active === 0 }"
                                    >
                                        <v-expansion-panel-header style="padding: 0">
                                            <div class="se-card__serv-header">
                                                <div class="se-card__title d-flex align-center">
                                                    <span>
                                                        {{ item.name }}
                                                    </span>
                                                </div>
                                                <span
                                                    v-if="item.active === 0"
                                                    class="tariff-item__soon"
                                                >
                                                    Скоро
                                                </span>
                                            </div>
                                        </v-expansion-panel-header>
                                        <v-expansion-panel-content class="p-0 m-0">
                                            <ul class="se-card__list">
                                                <li
                                                    v-for="subItem in item.description"
                                                    :key="subItem"
                                                >
                                                    {{ subItem }}
                                                </li>
                                            </ul>
                                        </v-expansion-panel-content>
                                    </v-expansion-panel>
                                </template>

                                <v-expansion-panel class="tariff-item p-0 m-0">
                                    <v-expansion-panel-header style="padding: 0">
                                        <div class="se-card__serv-header">
                                            <div
                                                class="se-card__title d-flex align-center"
                                                :class="{ 'visually-disabled': !isDev }"
                                            >
                                                <v-checkbox
                                                    v-model="corpActive"
                                                    class="mt-0 pt-0"
                                                    hide-details
                                                    dense
                                                    :disabled="!isDev"
                                                    @click.stop="getPrice"
                                                ></v-checkbox>
                                                <span class="mr-2">Корпоративный доступ</span>
                                                <span class="text_green">
                                                    +{{ priceCorpHeader }}₽
                                                </span>
                                            </div>
                                        </div>
                                    </v-expansion-panel-header>
                                    <v-expansion-panel-content class="p-0 m-0">
                                        <ul class="se-card__list">
                                            <li
                                                v-for="subItem in servicesSorted.corp.description"
                                                :key="subItem"
                                            >
                                                {{ subItem }}
                                            </li>
                                        </ul>
                                    </v-expansion-panel-content>
                                </v-expansion-panel>
                            </v-expansion-panels>
                            <div
                                v-for="item in tarrifsList"
                                :key="item.name"
                                class="tariff-item"
                                :class="{ disabled: item.active === 0 }"
                            ></div>
                            <div v-if="timePeriod.length" class="se-slider-sel pr-4 pl-4 mt-5">
                                <div class="se-slider-sel__title mb-4">
                                    <span>Период, мес.</span>
                                </div>
                                <div ref="thumbsPeriod" class="d-flex justify-space-between">
                                    <span
                                        v-for="el in timePeriod"
                                        :key="el.period"
                                        class="se-slider-sel__thumb"
                                        :class="{ 'se-slider-sel__thumb_large-pr': !el.discount }"
                                    >
                                        <span class="se-slider-sel__thumb-number">
                                            {{ el.period }}
                                            <span class="se-slider-sel__thumb-number-background" />
                                        </span>
                                        <span
                                            v-if="el.discount"
                                            class="se-slider-sel__thumb-discount ml-2"
                                        >
                                            {{ `${roundNumber(el.discount, 0)}%` }}
                                        </span>
                                    </span>
                                </div>
                                <v-slider
                                    ref="sliderTerm"
                                    v-model="selectedTimePeriod"
                                    class="de-term"
                                    hide-details
                                    :max="timePeriod.length - 1"
                                    step="1"
                                    ticks="always"
                                    tick-size="5"
                                    track-color="#E9EDF2"
                                    @change="handlePeriodChange($event, 'thumbsPeriod')"
                                ></v-slider>
                            </div>
                            <div
                                v-if="skuOptions.length"
                                ref="thumbsSku"
                                class="se-slider-sel pr-4 pl-4 mt-5"
                            >
                                <div
                                    class="se-slider-sel__title d-flex justify-space-between align-center mb-4"
                                >
                                    <span>Количество SKU</span>
                                </div>
                                <div class="d-flex justify-space-between">
                                    <span
                                        v-for="el in skuOptions"
                                        :key="el.sku"
                                        class="se-slider-sel__thumb"
                                        :class="{ 'se-slider-sel__thumb_large-pr': !el.discount }"
                                    >
                                        <span class="se-slider-sel__thumb-number">
                                            до {{ el.sku }}
                                            <span class="se-slider-sel__thumb-number-background" />
                                        </span>
                                        <span
                                            v-if="el.discount"
                                            class="se-slider-sel__thumb-discount ml-2"
                                        >
                                            {{ `${roundNumber(el.discount, 1)}%` }}
                                        </span>
                                    </span>
                                </div>
                                <v-slider
                                    ref="sliderTerm"
                                    v-model="selectedSku"
                                    class="de-term"
                                    hide-details
                                    :max="skuOptions.length - 1"
                                    step="1"
                                    ticks="always"
                                    tick-size="5"
                                    track-color="#E9EDF2"
                                    @change="handleSkuChange($event, 'thumbsSku')"
                                ></v-slider>
                            </div>
                            <div class="need-more pt-4 pl-5 pr-5">
                                У вас больше 1000 товаров в кабинете маркетплейса?
                                <a @click.prevent="handleClickOver10K">
                                    Подберем для вас индивидуальные условия.
                                </a>
                            </div>
                            <div class="promocode pa-5 pb-8">
                                <div class="promocode__expand-trigger-wrapper">
                                    <span
                                        class="promocode__expand-trigger"
                                        @click.prevent="togglePromocode"
                                    >
                                        Есть промокод?
                                    </span>
                                </div>
                                <template v-if="promocode.show">
                                    <VTextField
                                        v-model="promocode.value"
                                        label="Введите промокод"
                                        placeholder="Промокод"
                                        persistent-placeholder
                                        outlined
                                        hide-details
                                        dense
                                        :error="promocode.status === 'error'"
                                        :background-color="promocodeInputBackground"
                                        @keydown="handleIPromocodeInputKeydown"
                                    />

                                    <template v-if="!promocode.status">
                                        <a
                                            class="promocode__apply"
                                            @click.prevent="handlePromocode"
                                        >
                                            Применить
                                        </a>
                                    </template>
                                    <template
                                        v-else-if="
                                            promocode.status && promocode.status === 'success'
                                        "
                                    >
                                        <span class="promocode__message promocode__message_success">
                                            Промокод верный
                                        </span>
                                    </template>
                                    <template
                                        v-else-if="promocode.status && promocode.status === 'error'"
                                    >
                                        <span class="promocode__message promocode__message_error">
                                            Промокод неверный
                                        </span>
                                    </template>
                                </template>
                            </div>
                            <div class="tariff-selected ma-3 mb-12">
                                <div class="tariff-selected__row">
                                    <span>Всего:</span>
                                    <span class="tariff-selected__value">
                                        {{
                                            roundNumber(tariffCalculation.begin_price || 0, 0)
                                                | splitThousands
                                        }}
                                        ₽
                                    </span>
                                </div>
                                <div class="tariff-selected__row">
                                    <span>Скидка за период:</span>
                                    <span class="tariff-selected__value">
                                        <span
                                            v-if="currentDiscounts.duration.percentage"
                                            class="tariff-selected__discount"
                                        >
                                            -{{
                                                `${roundNumber(
                                                    currentDiscounts.duration.percentage,
                                                    0
                                                )}%`
                                            }}
                                        </span>
                                        <span>
                                            {{
                                                roundNumber(currentDiscounts.duration.value, 1)
                                                    | splitThousands
                                            }}
                                            ₽
                                        </span>
                                    </span>
                                </div>
                                <div class="tariff-selected__row">
                                    <span>Скидка за количество:</span>
                                    <span class="tariff-selected__value">
                                        <span
                                            v-if="currentDiscounts.sku.percentage"
                                            class="tariff-selected__discount"
                                        >
                                            -{{
                                                `${roundNumber(
                                                    currentDiscounts.sku.percentage,
                                                    1
                                                )}%`
                                            }}
                                        </span>
                                        <span>
                                            {{
                                                roundNumber(currentDiscounts.sku.value, 0)
                                                    | splitThousands
                                            }}
                                            ₽
                                        </span>
                                    </span>
                                </div>
                                <div
                                    v-if="currentDiscounts.promocode.value"
                                    class="tariff-selected__row"
                                >
                                    <span>Промокод:</span>
                                    <span class="tariff-selected__value">
                                        <span
                                            v-if="currentDiscounts.promocode.percentage"
                                            class="tariff-selected__discount"
                                        >
                                            -{{ `${currentDiscounts.promocode.percentage}%` }}
                                        </span>
                                        <span>
                                            {{
                                                roundNumber(currentDiscounts.promocode.value, 0)
                                                    | splitThousands
                                            }}
                                            ₽
                                        </span>
                                    </span>
                                </div>
                                <div v-if="priceCorp" class="tariff-selected__row">
                                    <span>Корпоративный доступ:</span>
                                    <span class="tariff-selected__value">
                                        {{ priceCorp | splitThousands }} ₽
                                    </span>
                                </div>
                                <div class="tariff-selected__row">
                                    <span class="font-weight-bold">Итого:</span>
                                    <span
                                        class="tariff-selected__value tariff-selected__big font-weight-bold"
                                    >
                                        {{
                                            roundNumber(tariffCalculation.total_price || 0, 0)
                                                | splitThousands
                                        }}
                                        ₽
                                    </span>
                                </div>
                            </div>
                            <SeAlert v-if="isCardPaymentLimitExceeded" type="alert" class="mb-4">
                                Оплата подписки более чем на 250 000 рублей возможна только по счету
                            </SeAlert>
                            <div class="tarif-actions">
                                <v-row dense>
                                    <v-col v-for="item in paymentType" :key="item.name">
                                        <div
                                            class="se-tab"
                                            :class="{
                                                active: selectedPaymentType === item.name,
                                                inactive: isPaymentTypeButtonDisabled(item.id),
                                            }"
                                            :disabled="isPaymentTypeButtonDisabled(item.id)"
                                            @click="setPaymentType(item.id)"
                                        >
                                            {{ item.title }}
                                        </div>
                                    </v-col>
                                </v-row>
                            </div>
                            <v-expand-transition>
                                <OrganizationData
                                    v-if="showCompanyForm"
                                    ref="organizationData"
                                    class="mt-5"
                                ></OrganizationData>
                            </v-expand-transition>
                            <div class="bye-btn">
                                <v-btn
                                    ref="byeBtn"
                                    block
                                    color="primary"
                                    depressed
                                    class="se-btn mt-3 mb-2"
                                    @click="handlePay()"
                                >
                                    {{ paymentBtnName }}
                                </v-btn>
                            </div>
                        </div>
                    </div>
                </v-col>
                <v-col lg="4" sm="12">
                    <div class="se-card pa-4">
                        <UserPlan :services-sorting-array="servicesOrder"></UserPlan>
                    </div>
                </v-col>
            </v-row>
        </Page>
    </div>
</template>
<script>
    /* eslint-disable */
    import { mapGetters } from 'vuex';
    import debounce from 'lodash/debounce';
    import formatText from '~mixins/formatText.mixin';
    import { roundNumber } from '~utils/numbers.utils';
    import onboarding from '~mixins/onboarding.mixin';
    import Page from '~/components/ui/SeInnerPage';

    export default {
        name: 'Tariffs',
        components: {
            Page,
        },
        mixins: [formatText, onboarding],
        data() {
            return {
                title: {
                    isActive: true,
                    text: 'Тарифы',
                },
                selectedPaymentType: undefined,
                paymentType: [
                    { id: 1, name: 'card', title: 'По карте' },
                    { id: 2, name: 'bank_card', title: 'По счету' },
                ],
                selectedServices: [],
                nfTariffsList: [],
                tarrifsList: [],
                activeSlider: '',
                checkedInterval: undefined,
                uniqSku: [],
                selectedSku: 0,
                selectedTimePeriod: 0,
                timePeriod: [{ period: 1, discount: 0 }],
                skuOptions: [
                    {
                        discount: 0,
                        sku: 100,
                        title: '100',
                    },
                    {
                        discount: 2.5,
                        sku: 500,
                    },
                    {
                        discount: 5,
                        sku: 1000,
                    },
                ],
                promocode: {
                    show: false,
                    value: null,
                    status: null,
                    discountPercentage: null,
                },
                services: [],
                servicesOrder: ['analytics', 'semantic', 'depo', 'ad'],
                corpActive: false,
                currentTariff: null,
                tariffCalculation: {},
                pending: false,
                phoneModalShown: false,
            };
        },
        computed: {
            ...mapGetters(['userPlan']),
            paymentBtnName() {
                const btnTextList = {
                    bank_card: 'Выставить счет',
                    card: 'Оплатить',
                };
                return btnTextList[this.selectedPaymentType];
            },
            currentDiscounts() {
                const result = {
                    duration: {
                        value: 0,
                        percentage: 0,
                    },
                    sku: {
                        value: 0,
                        percentage: 0,
                    },
                    promocode: {
                        value: 0,
                        percentage: 0,
                    },
                };

                if (this.tariffCalculation.discounts?.duration) {
                    result.duration = this.tariffCalculation.discounts.duration;
                }

                if (this.tariffCalculation.discounts?.sku) {
                    result.sku = this.tariffCalculation.discounts.sku;
                }

                if (this.tariffCalculation.discounts?.promocode) {
                    result.promocode = this.tariffCalculation.discounts.promocode;
                }

                return result;
            },
            tariffEstimates() {
                const basePriceTimesSkuCount = this.basePricePerSku * this.calculatedSkuNumber;
                const discountPeriodPercentage =
                    this.timePeriod[this.selectedTimePeriod].discount / 100 || 0;
                const discountPeriodValue = basePriceTimesSkuCount * discountPeriodPercentage;
                const discountNumberPercentage = this.getDiscountForSku(
                    this.skuOptions,
                    this.calculatedSkuNumber
                );
                const discountNumberValue = basePriceTimesSkuCount * discountNumberPercentage;
                const months = this.timePeriod[this.selectedTimePeriod].period || 1;
                const corp = this.corpActive ? this.priceCorp : null;
                const tariffId = this.currentTariff?.id;
                const discountPromocodePercentage = this.promocode.discountPercentage || 0;
                const discountPromocodeValue = basePriceTimesSkuCount * discountPromocodePercentage;
                return {
                    tariffId,
                    months,
                    total: basePriceTimesSkuCount * months,
                    discountPeriod: {
                        percentage: discountPeriodPercentage,
                        value: discountPeriodValue,
                    },
                    discountNumber: {
                        percentage: discountNumberPercentage,
                        value: discountNumberValue,
                    },
                    discountPromocode: {
                        percentage: discountPromocodePercentage,
                        value: discountPromocodeValue,
                    },
                    corp,
                    final:
                        basePriceTimesSkuCount * months +
                        corp -
                        discountPeriodValue -
                        discountNumberValue -
                        discountPromocodeValue,
                };
            },
            promocodeInputBackground() {
                return this.promocode.status === 'success' ? 'rgba(32, 194, 116, 0.25)' : '';
            },
            servicesSorted() {
                const noCorp = [];
                let corp = {};
                this.services.forEach(el => {
                    if (el.alias === 'corp') {
                        corp = el;
                    } else {
                        noCorp.push(el);
                    }
                });
                noCorp.sort((a, b) =>
                    this.servicesOrder.indexOf(a.alias) > this.servicesOrder.indexOf(b.alias)
                        ? 1
                        : this.servicesOrder.indexOf(b.alias) > this.servicesOrder.indexOf(a.alias)
                        ? -1
                        : 0
                );
                return { corp, noCorp };
            },
            basePricePerSku() {
                return this.skuOptions[0]?.price_per_item || 0;
            },
            priceCorp() {
                return this.tariffCalculation.corp &&
                    !Array.isArray(this.tariffCalculation.corp) &&
                    this.tariffCalculation.corp.total_price
                    ? this.tariffCalculation.corp.total_price
                    : 0;
            },
            priceCorpHeader() {
                return (
                    this.priceCorp ||
                    this.getServicePrice(
                        this.services.find(el => el.alias === 'corp')?.price || 0,
                        this.calculatedSkuNumber
                    )
                );
            },
            showCompanyForm() {
                return this.selectedPaymentType === 'bank_card' || this.corpActive;
            },
            isCardPaymentLimitExceeded() {
                return this.tariffCalculation.total_price > 250000;
            },
            servicesToOrder() {
                return [];
            },
            isDev() {
                return process.env.SERVER_TYPE === 'dev';
            },
            isProdOrDemo() {
                return ['prod', 'demo'].includes(process.env.SERVER_TYPE);
            },
            needToShowPhoneModal() {
                return Boolean(!this.$auth.user.phone?.length && !this.$auth.user['tariff_phone_modal_shown'] && !this.phoneModalShown && !this.isProdOrDemo);
            },
            calculatedSkuNumber() {
                return this.skuOptions[this.selectedSku].sku;
            }
        },
        watch: {
            'tariffCalculation.total_price'(val) {
                if (val > 250000) {
                    this.setPaymentType(2);
                }
            },
        },
        async created() {
            this.debounceGetPrice = debounce(this.getPrice, 300);
            this.setPaymentType(1);

            await this.getServices();

            await this.getTariffs();
            await this.getDiscounts();
            this.createOnBoarding();
        },
        beforeMount() {
            this.$emitter.on(`modal-close-${this.$route.name}`, this.handlePhoneModalClose);
        },
        beforeDestroy() {
            this.$emitter.off(`modal-close-${this.$route.name}`, this.handlePhoneModalClose);
        },
        methods: {
            roundNumber,
            createOnBoarding() {
                this.$store.commit('onBoarding/setOnboardActive', true);
                const tabs = document.querySelectorAll('.se-tab');
                const routeName = this.$route.name;

                const elements = [
                    {
                        el: tabs[0],
                        intro: 'Выберите, если хотите оплатить картой',
                        pos: 'top',
                        callback: () => {
                            const completedRoutes =
                                JSON.parse(localStorage.getItem('completedRoutes')) || {};
                            completedRoutes[routeName] = 1;

                            const completedRoutesStr = JSON.stringify(completedRoutes);
                            localStorage.setItem('completedRoutes', completedRoutesStr);
                        },
                    },
                    {
                        el: tabs[1],
                        intro: 'Выберите, если хотите оплатить по счету',
                        pos: 'top',
                    },
                    {
                        el: document.querySelectorAll('.bye-btn')[0],
                        intro: 'Нажмите, чтобы перейти к оплате картой или сформировать счет',
                        pos: 'top',
                        callback: () => {
                            this.$store.commit('onBoarding/setOnboardActive', false);
                        },
                    },
                ];

                const isDisplayOnboard = !this.checkRouteForOnboarding();

                const createOnBoardingParams = {
                    elements,
                    routeNameFirstStep: routeName,
                    isDisplayOnboard,
                };

                this.createOnBoardingByParams(createOnBoardingParams);
            },
            async getServices() {
                const topic = '/api/v2/billing/services';

                try {
                    const {
                        data: { data },
                    } = await this.$axios.get(topic);
                    data.forEach(el => {
                        const descr = el.description;
                        el.description = descr.split(/\r?\n/).filter(e => !e.match(/^\s*$/));
                    });
                    this.services = data;
                    await this.getPrice();
                } catch (error) {
                    console.error(error);
                }
            },
            async getTariffs() {
                const topic = '/api/v2/billing/tariffs';

                try {
                    const {
                        data: { data },
                    } = await this.$axios.get(topic);
                    // temporary, until more tariff selection mechanics implemented
                    this.currentTariff = data.find(el => el.id === 1);
                } catch (error) {
                    console.error(error);
                }
            },
            async getDiscounts() {
                const topic = '/api/v2/billing/discounts';

                try {
                    const {
                        data: {
                            data: { months, sku },
                        },
                    } = await this.$axios.get(topic);
                    this.timePeriod = months;
                } catch (error) {
                    console.error(error);
                }
            },
            async getPrice() {
                const topic = '/api/v2/billing/orders/check-final-price';
                const servicesSrc = this.corpActive ? this.services : this.servicesSorted.noCorp;
                const services = servicesSrc.map(el => ({
                    id: el.id,
                    alias: el.alias,
                    amount: Number(this.calculatedSkuNumber),
                }));
                const axiosData = {
                    tariff_id: this.tariffEstimates.tariffId,
                    duration: this.tariffEstimates.months,
                    services,
                    sku: Number(this.calculatedSkuNumber),
                };

                if (this.promocode.value?.length) {
                    axiosData.promocode = this.promocode.value;
                }

                this.pending = true;
                try {
                    const {
                        data: { data },
                    } = await this.$axios.post(topic, axiosData);
                    this.tariffCalculation = data;
                    this.pending = false;
                } catch (error) {
                    console.error(error);
                }
            },
            setPaymentType(id) {
                try {
                    const { name } = this.paymentType.find(({ id: idPt }) => idPt === id);
                    this.selectedPaymentType = name;
                } catch {
                    this.selectedPaymentType = this.paymentType[0].name;
                }
            },
            async handlePay() {
                if (this.pending) {
                    return false;
                }

                if (this.needToShowPhoneModal) {
                    this.$modal.open({
                        component: 'ModalPhoneNumber',
                        attrs: {
                            srcPage: this.$route.name,
                        }
                    });
                    return false;
                }

                const topic = '/api/v2/billing/orders';
                const servicesSrc = this.corpActive ? this.services : this.servicesSorted.noCorp;
                const services = servicesSrc.map(el => ({
                    id: el.id,
                    alias: el.alias,
                    amount: this.calculatedSkuNumber,
                    total_price: this.getServiceTotalPrice(el.alias, this.tariffCalculation),
                }));
                const paymentMethod = this.selectedPaymentType === 'bank_card' ? 'bank' : 'card';
                const {
                    $refs: { organizationData },
                } = this;

                const axiosData = {
                    tariff_id: this.tariffEstimates.tariffId,
                    duration: this.tariffEstimates.months,
                    services,
                    paymentMethod,
                    sku: this.calculatedSkuNumber,
                    promocode: this.promocode.value,
                };
                try {
                    if (this.showCompanyForm) {
                        axiosData.company = organizationData.getCompanyData();
                    }
                    const {
                        data: { data },
                    } = await this.$axios.post(topic, axiosData);

                    const executionFunctions = {
                        bank_card: () => {
                            const { orderId } = data;
                            axiosData.orderId = orderId;

                            this.$modal.open({
                                component: 'ModalPaymentSuccessInvoice',
                                attrs: {
                                    requestData: axiosData,
                                    tariffCalculation: {
                                        begin_price: this.tariffCalculation.begin_price,
                                        begin_price_with_sku_discount:
                                            this.tariffCalculation.begin_price_with_sku_discount,
                                        begin_price_with_sku_period_discount:
                                            this.tariffCalculation
                                                .begin_price_with_sku_period_discount,
                                        begin_price_with_sku_period_promo_discount:
                                            this.tariffCalculation
                                                .begin_price_with_sku_period_promo_discount,
                                        total_price: this.tariffCalculation.total_price,
                                    },
                                },
                            });
                            this.$sendGtm('ch_cashless_pay');
                        },
                        card: () => {
                            const { url } = data;
                            location.href = url;
                        },
                    };

                    executionFunctions[this.selectedPaymentType]();
                } catch (error) {
                    console.error('Error', error);
                }
            },
            handlePeriodChange(num, ref) {
                this.handleThumbsChange(num, ref);
                this.getPrice();
            },
            handleSkuChange(num, ref) {
                this.handleThumbsChange(num, ref);
                this.debounceGetPrice();
            },
            handleThumbsChange(num, ref) {
                const thumbs = Array.from(
                    this.$refs[ref].getElementsByClassName('se-slider-sel__thumb')
                );
                thumbs.forEach((el, i) => {
                    if (num === i) {
                        el.classList.add('active');
                        const [number] = el.getElementsByClassName('se-slider-sel__thumb-number');
                        const [background] = number.getElementsByClassName(
                            'se-slider-sel__thumb-number-background'
                        );
                        background.style.width = `${number.getBoundingClientRect().width + 12}px`;
                    } else {
                        el.classList.remove('active');
                    }
                });
            },
            async handlePromocode() {
                try {
                    const {
                        data: { data },
                    } = await this.$axios.post('/api/v1/promocodes/apply', {
                        code: this.promocode.value,
                    });
                    this.promocode.discountPercentage = data.discount / 100 || 0;
                    this.promocode.status = 'success';
                    await this.getPrice();
                } catch (error) {
                    this.promocode.status = 'error';
                    console.error(error);
                }
            },
            handleIPromocodeInputKeydown() {
                if (this.promocode.status) {
                    this.promocode.status = null;
                }
            },
            togglePromocode() {
                this.promocode.show = !this.promocode.show;
            },
            handleClickOver10K() {
                return this.$modal.open({
                    component: 'ModalTariffOver10KRequest',
                });
            },
            getServicePrice(arr, skuNumber) {
                if (!arr.length) {
                    return 0;
                } else if (arr.length === 1) {
                    return arr[0].price_per_item || 0;
                } else {
                    return arr.reduce(
                        (prev, current) => (current.min_amount < skuNumber ? current : prev),
                        arr[0]
                    ).price_per_item;
                }
            },
            getDiscountForSku(arr, value) {
                let discount = 0;
                for (let i = arr.length - 1; i >= 0; i--) {
                    if (arr[i].min_amount <= value) {
                        discount = arr[i].discount;
                        break;
                    }
                }
                return discount / 100;
            },
            isPaymentTypeButtonDisabled(id) {
                return id === 1 && this.isCardPaymentLimitExceeded;
            },
            getServiceTotalPrice(alias, src) {
                if (!src.services) {
                    return false;
                }

                const service = src.services.find(el => el.alias === alias);
                if (!service) {
                    return false;
                }
                return service.total_price;
            },
            async handlePhoneModalClose() {
                try {
                    const response = await this.$axios.$post('/api/v1/set-settings', {
                        'tariff_phone_modal_shown': true,
                    });
                    this.phoneModalShown = true;
                    console.log('handlePhoneModalClose', response);
                } catch (error) {
                    console.error(error);
                }
            }
        },
    };
</script>

.
<style lang="scss" scoped>
    /* stylelint-disable selector-pseudo-element-no-unknown */

    .fix-tarif {
        display: flex;
        justify-content: space-between;
        min-height: 105px;
        margin-top: 32px;
        border-radius: 12px;
        border: 1px solid $border-color;

        &__text {
            font-size: 26px;
            font-weight: 600;
            color: $base-900;
        }

        &__row {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex: auto;
            font-size: 20px;

            &:first-child {
                border-right: 1px solid $border-color;
            }
        }

        &__point {
            font-size: 20px;
        }

        &__prod {
            font-size: 26px;
            color: $secondaryGreen;
        }

        &__unit {
            font-weight: 600;
            color: $base-700;
        }
    }

    .primary-12 {
        margin-right: 6px;
        font-size: 12px;
        font-weight: bold;
        color: $primary-500;
    }

    .se-card {
        @include cardShadow;

        &__title {
            @include subtitle-1;
        }

        &__inner-body {
            padding: 16px;
        }

        &__chip {
            padding: 3px 8px;
            border-radius: 10px;
            border: 1px solid $color-purple-primary-700;
            font-size: 12px;
            color: $color-purple-primary-700;
        }

        &__serv-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-right: 8px;
            font-size: 24px;
        }

        &__first-stroke {
            margin-top: 24px;
            margin-bottom: 8px;
            font-size: 16px;
        }

        &__list {
            font-size: 16px;

            li {
                list-style: disc;
            }
        }

        & .text {
            &_green {
                color: $success;
            }
        }

        &::v-deep .v-expansion-panel-header__icon {
            border-radius: 4px;
            background-color: $color-main-background;
        }
    }

    .se-slider-sel {
        &__roll {
            position: relative;
            min-height: 40px;
        }

        &__thumb {
            &-number {
                position: relative;
                z-index: 1;
                display: inline-block;
                font-size: 14px;
                font-weight: bold;
                color: $color-main-font;
                transition: all cubic-bezier(0.25, 0.8, 0.5, 1) 0.1s;

                &-background {
                    @include centerer;

                    z-index: -1;
                    display: none;
                    width: 27px;
                    height: 25px;
                    border-radius: 200px;
                    background: #f4ebff;
                }
            }

            &-discount {
                padding: 0 3px;
                border-radius: 2px;
                background-color: $success;
                font-size: 12px;
                line-height: 16px;
                font-weight: bold;
                color: $color-white-font;
            }

            &_large-pr {
                padding-right: 38px;
            }

            &.active {
                & .se-slider-sel__thumb-number {
                    color: $primary-500;

                    &-background {
                        display: block;
                    }
                }
            }
        }

        &__title {
            @include subtitle-1;

            &-input {
                max-width: 147px;
            }
        }
    }

    .calc-block {
        @include inter-block-card;

        padding: 20px 16px;
        font-size: 16px;

        &__list {
            margin: 0;
            padding: 0;

            li {
                display: flex;
                align-items: center;
                justify-content: space-between;
                min-height: 32px;

                &:last-child {
                    font-weight: bold;

                    & .calc-block__value {
                        font-size: 20px;
                    }
                }
            }
        }

        &__value {
            display: flex;
            align-items: center;
        }
    }

    .tariff-item {
        position: relative;
        margin-bottom: 8px;
        padding: 16px 16px 0 16px;
        border-radius: 8px;

        &__soon {
            right: 55px;
            font-size: 12px;
            font-weight: bold;
            color: $neutral-600;
        }

        &__price {
            font-size: 26px;
            font-weight: 500;
            color: $secondaryGreen;
        }

        &.disabled {
            border: none;
            background: #f9f9f9;

            & > * {
                color: #7e8793 !important;
            }
        }
    }

    .se-tab {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        border-radius: 8px;
        border: 2px solid $border-color;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;

        &.active {
            /* stylelint-disable */
            border: 2px solid $primary-500;
            color: $primary-500;
            box-shadow: 0px 12px 16px rgba(95, 69, 255, 0.16);
        }

        &.inactive {
            border: 1px solid $base-600;
            color: $base-600;
        }

        &.disabled {
            color: $border-color;
            box-shadow: none;
            border: 2px solid $border-color;
        }
    }

    .promocode {
        @include flex-grid-x;

        flex-wrap: wrap;
        align-items: center;
        gap: 24px;

        &__expand-trigger {
            font-size: 16px;
            line-height: 22px;
            font-weight: bold;
            color: $primary-500;
            text-decoration: underline dashed;
            text-underline-offset: 4px;
            cursor: pointer;
            user-select: none;

            &-wrapper {
                width: 100%;
            }
        }

        &__apply {
            font-size: 16px;
            font-weight: bold;
            line-height: 22px;
            color: $primary-500;
        }

        &__message {
            font-size: 16px;
            font-weight: bold;
            line-height: 22px;

            &.promocode__message_error {
                color: $error;
            }

            &.promocode__message_success {
                color: $secondaryGreen;
            }
        }

        &::v-deep .theme--light.v-label {
            color: #000;
        }
    }

    .need-more {
        @include subtitle-1;

        font-weight: normal;

        & a {
            color: $primary-500;
        }
    }

    .tariff-selected {
        @include flex-grid-y;
        @include subtitle-1;

        gap: 8px;
        padding: 16px 21px;
        font-weight: normal;
        color: $black;
        border: 1px solid $border-color;
        border-radius: 8px;

        &__row {
            @include flex-grid-x;

            align-items: center;
            justify-content: space-between;
        }

        &__value {
            @include flex-grid-x;

            gap: 4px;
        }

        &__discount {
            font-size: 12px;
            color: $secondaryGreen;
        }

        &__big {
            font-size: 20px;
        }
    }

    .tarrifs-page {
        &::v-deep
            .v-expansion-panel-header:not(.v-expansion-panel-header--mousedown):focus::before {
            opacity: 0;
        }
    }

    .visually-disabled {
        filter: grayscale(1);
        opacity: 0.7;
    }
</style>
