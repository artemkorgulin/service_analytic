<template>
    <div :class="$style.CampaignEditPageSettings">
        <div :class="$style.inputsWrapper">
            <VTextField
                v-model="form.name.$model.value"
                :class="[$style.formField, $style.grow, $style.name]"
                :error-messages="form.name.$errorMessage.value"
                autocomplete="new-password"
                label="Название кампании"
                dense
                outlined
                @blur="form.name.$touch"
                @input="form.name.$resetExtError"
            />
            <VSelect
                v-model="form.campaign_status_id.$model.value"
                :class="[$style.formField, $style.status]"
                :items="$options.statuses"
                :error-messages="form.campaign_status_id.$errorMessage.value"
                :menu-props="{
                    'offset-y': true,
                    'z-index': 100,
                    'content-class': 'formFieldSelectMenu',
                }"
                label="Статус"
                dense
                outlined
                @blur="form.campaign_status_id.$touch"
                @input="form.campaign_status_id.$resetExtError"
            />
            <VSelect
                v-model="form.placement_id.$model.value"
                :class="[$style.formField, $style.placement]"
                :items="[
                    { value: '1', text: 'Карточка товара' },
                    { value: '2', text: 'Поиск' },
                ]"
                :menu-props="{
                    'offset-y': true,
                    'z-index': 100,
                    'content-class': 'formFieldSelectMenu',
                }"
                label="Место размещения"
                dense
                outlined
                @blur="form.placement_id.$touch"
                @input="form.placement_id.$resetExtError"
            />
            <InputDate
                v-model="form.date.$model.value"
                :class="[$style.formField, $style.date]"
                label="Даты проведения"
                hide-details
                :facet="dateFacet"
                @blur="form.date.$touch"
            />
            <VSelect
                v-model="form.payment_type_id.$model.value"
                :class="[$style.formField, $style.paymentType]"
                :items="[
                    { value: '1', text: 'Показы' },
                    { value: '2', text: 'Клики' },
                ]"
                :menu-props="{
                    'offset-y': true,
                    'z-index': 100,
                    'content-class': 'formFieldSelectMenu',
                }"
                label="Тип оплаты"
                dense
                outlined
                @blur="form.payment_type_id.$touch"
                @input="form.payment_type_id.$resetExtError"
            />
            <VTextField
                v-model="form.budget.$model.value"
                :class="[$style.formField, $style.budget]"
                :error-messages="form.budget.$errorMessage.value"
                autocomplete="new-password"
                label="Дневной бюджет"
                dense
                outlined
                @blur="form.budget.$touch"
                @input="form.budget.$resetExtError"
            />
            <div :class="$style.inputsRow">
                <VDivider :class="$style.divider" />
                <VSelect
                    v-model="form.strategy_type_id.$model.value"
                    :class="[$style.formField, $style.strategyType]"
                    :items="$options.strategies"
                    :error-messages="form.strategy_type_id.$errorMessage.value"
                    :menu-props="{
                        'offset-y': true,
                        'z-index': 100,
                        'content-class': 'formFieldSelectMenu',
                    }"
                    label="Примененная стратегия"
                    dense
                    outlined
                    @blur="form.strategy_type_id.$touch"
                    @input="form.strategy_type_id.$resetExtError"
                />
                <VSelect
                    v-model="form.strategy_status_id.$model.value"
                    :class="[$style.formField, $style.strategyStatus]"
                    :error-messages="form.strategy_status_id.$errorMessage.value"
                    :items="$options.strategyStatuses"
                    :menu-props="{
                        'offset-y': true,
                        'z-index': 100,
                        'content-class': 'formFieldSelectMenu',
                    }"
                    label="Статус стратегии"
                    dense
                    outlined
                    @blur="form.strategy_status_id.$touch"
                    @input="form.strategy_status_id.$resetExtError"
                />
            </div>
            <div v-if="strategyTypeRef" :class="$style.strategyBlock">
                <!-- <VFadeTransition mode="out-in"> -->
                <div
                    v-if="strategyTypeRef === '1'"
                    :key="`strategy_type_id-1-${strategyFormKey}`"
                    :class="$style.strategyWrapper"
                >
                    <!-- v-if="form.threshold"  v-if="form.step"  v-if="form.threshold1" v-if="form.threshold2" v-if="form.threshold3"-->
                    <InputNumber
                        v-model="form.threshold.$model.value"
                        :class="[$style.formField, $style.strategy]"
                        :error-messages="form.threshold.$errorMessage.value"
                        label="Пороговое значение"
                        dense
                        outlined
                        @blur="form.threshold.$touch"
                        @input="form.threshold.$touchAndResetExtErr"
                    />
                    <InputNumber
                        v-model="form.step.$model.value"
                        :class="[$style.formField, $style.strategy]"
                        :error-messages="form.step.$errorMessage.value"
                        label="Шаг"
                        dense
                        outlined
                        @blur="form.step.$touch"
                        @input="form.step.$touchAndResetExtErr"
                    />
                </div>
                <div
                    v-else-if="strategyTypeRef === '2'"
                    :key="`strategy_type_id-2-${strategyFormKey}`"
                    :class="$style.strategyWrapper"
                >
                    <InputNumber
                        v-model="form.threshold1.$model.value"
                        :class="[$style.formField, $style.numberFieldLarge]"
                        :error-messages="form.threshold1.$errorMessage.value"
                        label="Пороговое значение 1"
                        dense
                        outlined
                        @blur="form.threshold1.$touch"
                        @input="form.threshold1.$touchAndResetExtErr"
                    />
                    <InputNumber
                        v-model="form.threshold2.$model.value"
                        :class="[$style.formField, $style.numberFieldLarge]"
                        :error-messages="form.threshold2.$errorMessage.value"
                        label="Пороговое значение 2"
                        dense
                        outlined
                        @blur="form.threshold2.$touch"
                        @input="form.threshold2.$touchAndResetExtErr"
                    />
                    <InputNumber
                        v-model="form.threshold3.$model.value"
                        :class="[$style.formField, $style.numberFieldLarge]"
                        :error-messages="form.threshold3.$errorMessage.value"
                        label="Пороговое значение 3"
                        dense
                        outlined
                        @blur="form.threshold3.$touch"
                        @input="form.threshold3.$touchAndResetExtErr"
                    />
                </div>
                <!-- </VFadeTransition> -->
            </div>
        </div>
        <div :class="$style.buttonsWrapper">
            <div v-if="updatedAt" :class="$style.updatedAt">Изменено: {{ updatedAt }}</div>
            <div :class="$style.buttonsInner">
                <VBtn
                    :class="$style.btn"
                    :disabled="!$changed"
                    text
                    color="accent"
                    @click="$setInitialValue"
                >
                    Отменить изменения
                </VBtn>
                <VBtn
                    :class="$style.btn"
                    color="accent"
                    :disabled="!isMounted || !$changed || $invalid"
                    :loading="isLoading"
                    @click="handleAction"
                >
                    Сохранить изменения
                </VBtn>
            </div>
        </div>
    </div>
</template>
<router>
{
  name: 'campaign-settings',
  path: '/:marketplace/campaign/:id/settings',
  meta:{
    pageGroup: "perfomance",
    redirectOnChangeMarketplace: true,
    isEnableGoBackOnMobile:true,
    fallbackRoute: {
      name: "adm-campaigns"
    },
    name: "settings"
  }
}
</router>
<script>
    /* eslint-disable no-empty-function,camelcase,no-unused-vars,no-extra-parens */
    import { mapGetters } from 'vuex';
    import {
        inject,
        defineComponent,
        useContext,
        computed,
        watch,
        ref,
        reactive,
    } from '@nuxtjs/composition-api';
    import { useForm } from '~use/form';
    import { useField } from '~use/field';
    import { minLength, required, notLessThen, numeric } from '~utils/patterns';
    import { isSet } from '~utils/helpers';
    import { unrefObject } from '~utils/composition';
    import { getErrorMessage } from '~utils/error';

    import PageErrors from '~mixins/pageErrors';
    const statuses = [
        {
            value: '1',
            code: 'RUNNING',
            ozon_code: 'CAMPAIGN_STATE_RUNNING',
            text: 'Активна',
            sort: 10,
        },
        {
            value: '2',
            code: 'INACTIVE',
            ozon_code: 'CAMPAIGN_STATE_INACTIVE',
            text: 'Неактивна',
            sort: 20,
        },
        {
            value: '3',
            code: 'PLANNED',
            ozon_code: 'CAMPAIGN_STATE_PLANNED',
            text: 'Запланирована',
            sort: 30,
        },
        {
            value: '4',
            code: 'STOPPED',
            ozon_code: 'CAMPAIGN_STATE_STOPPED',
            text: 'Приостановлена',
            sort: 40,
        },
        { value: '5', code: 'DRAFT', ozon_code: '', text: 'Черновик', sort: 50 },
        {
            value: '6',
            code: 'ARCHIVED',
            ozon_code: 'CAMPAIGN_STATE_ARCHIVED',
            text: 'Архив',
            sort: 60,
        },
    ];
    const strategies = [
        {
            value: '1',
            text: 'Оптимальное количество показов',
        },
        {
            value: '2',
            text: 'Оптимизация по CPO',
        },
    ];
    const strategyStatuses = [
        { value: '1', text: 'Активна' },
        { value: '2', text: 'Неактивна' },
    ];
    function toStringWithDefault(value, defaultValue = '') {
        if (!value) {
            return defaultValue;
        }
        return String(value);
    }
    function isValidDate(payload) {
        if (!isSet(payload)) {
            return false;
        }
        return !isNaN(Date.parse(payload));
    }
    const THRESHOLD_2_LIMIT = 50;
    const THRESHOLD_3_LIMIT = 50;
    export default defineComponent({
        name: 'AdmSettingsPage',
        mixins: [PageErrors],

        transition: {
            name: 'fade',
            mode: 'out-in',
        },
        setup() {
            const { store } = useContext();
            const campaign = store.getters['campaign/getCampaignData'];
            const isDatesValid = computed(() =>
                [campaign?.start_date, campaign?.end_date].every(item => isValidDate(item))
            );
            const initialFormValues = computed(() => {
                const date = isDatesValid.value ? [campaign.start_date, campaign.end_date] : [];
                const strategyTypeId = toStringWithDefault(campaign?.strategy?.strategy_type_id);
                let strategyFields = {};
                if (strategyTypeId === '1') {
                    strategyFields = {
                        step: campaign?.strategy_show_counts?.step || 0,
                        threshold: campaign?.strategy_show_counts?.threshold || 0,
                    };
                } else if (strategyTypeId === '2') {
                    strategyFields = {
                        threshold1: campaign?.strategy_cpo_counts?.threshold1 || 0,
                        threshold2: campaign?.strategy_cpo_counts?.threshold2 || 0,
                        threshold3: campaign?.strategy_cpo_counts?.threshold3 || 0,
                        tcpo: campaign?.strategy_cpo_counts?.tcpo || 0,
                        vr: campaign?.strategy_cpo_counts?.vr || 0,
                    };
                }
                return {
                    name: campaign.name,
                    placement_id: toStringWithDefault(campaign.placement_id),
                    payment_type_id: toStringWithDefault(campaign?.payment_type_id),
                    date,
                    budget: toStringWithDefault(campaign?.budget),
                    campaign_status_id: toStringWithDefault(campaign?.campaign_status_id),
                    strategy_type_id: strategyTypeId,
                    strategy_status_id: toStringWithDefault(campaign?.strategy?.strategy_status_id),
                    ...strategyFields,
                };
            });
            const baseFields = {
                name: {
                    value: initialFormValues.value.name,
                    validators: { required, minLength: minLength(2) },
                    errorMessages: {
                        required: 'Введите название кампании',
                        minLength: 'Слишком короткое название кампании',
                    },
                },
                date: {
                    value: initialFormValues.value.date,
                    validators: { required },
                    errorMessages: {
                        required: 'Введите date',
                    },
                },
                placement_id: {
                    value: initialFormValues.value.placement_id,
                    validators: { required },
                    errorMessages: {
                        required: 'Укажите тип кампании',
                    },
                },
                payment_type_id: {
                    value: initialFormValues.value.payment_type_id,
                    validators: { required },
                    errorMessages: {
                        required: 'Укажите payment_type_id',
                    },
                },
                budget: {
                    value: initialFormValues.value.budget,
                    validators: {
                        required,
                        minValue(val) {
                            const minSize = 500;
                            if (typeof val === 'undefined' || val === null || val === '') {
                                return true;
                            }
                            return val >= minSize;
                        },
                    },
                    errorMessages: {
                        required: 'Укажите бюджет',
                        minValue: 'Минимальный бюджет - 500 руб.',
                    },
                },
                campaign_status_id: {
                    value: initialFormValues.value.campaign_status_id,
                    validators: { required },
                    errorMessages: {
                        required: 'Укажите статус кампании',
                    },
                },
                strategy_type_id: {
                    value: initialFormValues.value.strategy_type_id,
                },
                strategy_status_id: {
                    value: initialFormValues.value.strategy_status_id,
                },
            };
            const statusFields1 = {
                threshold: {
                    value: initialFormValues.value.threshold,
                    validators: { required, numeric, positive: notLessThen(0) },
                    errorMessages: {
                        required: 'Укажите пороговое значение',
                        numeric: 'Значение должно быть числом',
                        positive: 'Значение должно быть положительным',
                    },
                },
                step: {
                    value: initialFormValues.value.step,
                    validators: { required, numeric, positive: notLessThen(0) },
                    errorMessages: {
                        required: 'Укажите шаг',
                        numeric: 'Шаг должен быть числом',
                        positive: 'Шаг должен быть положительным',
                    },
                },
            };
            const statusFields2 = {
                threshold1: {
                    value: initialFormValues.value.threshold,
                    validators: {
                        required,
                        numeric,
                        minValue: notLessThen(50),
                    },
                    errorMessages: {
                        required: 'Укажите threshold1',
                        numeric: 'Значение должно быть числом',
                        minValue: 'Не может быть меньше 50',
                    },
                },
                threshold2: {
                    value: initialFormValues.value.threshold,
                    validators: {
                        required,
                        numeric,
                        lessThenFirst(val, field, formInstance) {
                            const controlValue = formInstance.threshold1.$model.value;
                            return val - THRESHOLD_2_LIMIT >= controlValue;
                        },
                    },
                    errorMessages: {
                        required: 'Укажите threshold1',
                        numeric: 'Значение должно быть числом',
                        lessThenFirst: `Не может быть меньше чем порог 1 на ${THRESHOLD_2_LIMIT}`,
                    },
                },
                threshold3: {
                    value: initialFormValues.value.threshold,
                    validators: {
                        required,
                        numeric,
                        lessThenSecond(val, field, formInstance) {
                            const controlValue = formInstance.threshold2.$model.value;
                            return val - THRESHOLD_2_LIMIT >= controlValue;
                        },
                    },
                    errorMessages: {
                        required: 'Укажите threshold1',
                        numeric: 'Значение должно быть числом',
                        lessThenSecond: `Не может быть меньше чем порог 2 на ${THRESHOLD_3_LIMIT}`,
                    },
                },
            };
            const strategyTypeRef = ref(initialFormValues.value.strategy_type_id);

            const formFields = computed(() => ({
                ...baseFields,
                ...(strategyTypeRef.value === '1' ? statusFields1 : null),
                ...(strategyTypeRef.value === '2' ? statusFields2 : null),
            }));
            const formObject = reactive({});
            for (const field in formFields.value) {
                formObject[field] = useField(formFields.value[field], formObject);
            }

            const $validation = useForm(formObject, formFields);

            const strategyType = computed(() => $validation.form.strategy_type_id.$model.value);
            watch(
                () => strategyType.value,
                (val, oldVal) => {
                    strategyTypeRef.value = val;
                }
            );
            const strategyFormKey = ref(2);
            // watch(() => Object.keys(formFields.value), (newFormFields, oldFormFields) => {
            //     for (const formField of oldFormFields) {
            //         if (!newFormFields.includes(formField)) {
            //             delete formObject[formField];
            //         }
            //     }

            //     for (const formField of newFormFields) {
            //         if (!oldFormFields.includes(formField)) {
            //             formObject[formField] = useField(formFields.value[formField], formObject);
            //         }
            //     }
            //     strategyFormKey.value += 1;
            // });
            const formatDateTime = inject('formatDateTime');
            return {
                ...$validation,
                formFields,
                strategyTypeRef,
                isDatesValid,
                formatDateTime,
                strategyFormKey,
            };
        },
        statuses,
        strategies,
        strategyStatuses,
        data() {
            return {
                isLoading: false,
                isMounted: false,
                isDeleteLoading: false,
                dateFacet: {
                    min: this.formatDateTime(new Date(), '$y-$m-$d'),
                },
                notificationLazy: () => ({}),
            };
        },
        computed: {
            ...mapGetters('campaign', {
                campaign: 'getCampaignData',
            }),
            // account_id() {
            //     return this.$store.getters['user/ozonPerfomanceSelectedAccount']?.id;
            // },
            notification: {
                get() {
                    return this.notificationLazy;
                },
                set(val) {
                    this.notificationLazy();
                    this.notificationLazy = val;
                },
            },
            updatedAt() {
                if (!this.campaign.updated_at) {
                    return null;
                }
                return this.formatDateTime(this.campaign.updated_at, '$y.$m.$d, $H:$I');
            },
        },
        watch: {
            isDatesValid: {
                handler(val) {
                    if (!val) {
                        return this.pageErrors.push('Дата не валидна');
                    }
                },
                immediate: true,
            },
        },
        mounted() {
            this.isMounted = true;
        },
        beforeDestroy() {
            return this.notification();
        },
        methods: {
            async handleAction() {
                try {
                    this.$touch();
                    if (this.$invalid) {
                        return;
                    }
                    this.notification();
                    this.$_hideErrors();
                    this.isLoading = true;
                    const id = this.$route.params.id;
                    if (!this.account_id) {
                        throw new Error('No account id');
                    }
                    const { date, ...dataOther } = unrefObject(this.$model);
                    let start_date;
                    let end_date;
                    if (date && date?.length === 2) {
                        start_date = date[0];
                        end_date = date[1];
                    }
                    const params = {
                        account_id: this.account_id,
                        ...dataOther,
                    };
                    if (start_date && end_date) {
                        params.start_date = start_date;
                        params.end_date = end_date;
                    }
                    const data = await this.$axios.$put(
                        `/api/adm/v2/campaigns/${this.$route.params.id}`,
                        params
                    );
                    this.isLoading = false;
                    if (!data?.success) {
                        if (data.errors) {
                            this.serverErrors = data.errors;
                        }
                        return;
                    }
                    await this.$store.dispatch('campaign/fetchCampaignData', id);
                    this.notification = await this.$notify.create({
                        message: 'Кампания успешно изменена',
                        timeout: 3000,
                        type: 'positive',
                    });
                } catch (error) {
                    this.isLoading = false;
                    this.notification = await this.$notify.create({
                        message: getErrorMessage(error),
                        type: 'negative',
                    });
                }
            },
        },
    });
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .CampaignEditPageSettings {
        --text-field-details-margin-bottom: 0;

        position: relative;
        // overflow-x: hidden;
        // overflow-y: auto;
        display: flex;
        flex: 1;
        height: 100%;
        padding-top: 16px;
        padding-bottom: 0;
        flex-direction: column;
    }

    .updatedAt {
        @extend %text-body-3;

        font-size: 16px;
        color: $base-900;
        font-weight: 500;
    }

    .inputsRow {
        display: flex;
        flex-wrap: wrap;
        width: 100%;
        gap: 16px;

        .divider {
            display: flex;
            width: 100%;
            margin-bottom: 20px;
            flex-basis: 100%;
        }
    }

    .strategyBlock {
        width: 100%;
        flex-basis: 100%;
    }

    .strategyWrapper {
        display: flex;
        flex-wrap: wrap;
        width: 100%;
        gap: 16px;
    }

    .loadingWrapper {
        @include centerer;
    }

    .pageHeading {
        @extend %text-h1;

        user-select: none;
        margin-bottom: 2.2rem;
    }

    // .mainAreaWrapper {
    //     flex-direction: column;
    //     display: flex;
    //     flex: 1;
    //     margin-bottom: 2.4rem;
    //     padding: 40px;
    // }

    .inputsWrapper {
        display: flex;
        flex-wrap: wrap;
        width: 100%;
        max-width: 100%;
        gap: 16px;

        @include respond-to(md) {
            flex-wrap: wrap;
        }
    }

    .formField {
        &.numberField {
            flex-basis: 180px;
            max-width: 180px;
        }

        &.name {
            flex-basis: 514px;
            max-width: 514px;
        }

        &.placement {
            flex-basis: 220px;
            max-width: 220px;
        }

        &.date {
            flex-basis: 315px;
            max-width: 315px;
        }

        &.paymentType {
            flex-basis: 200px;
            max-width: 200px;
        }

        &.budget {
            flex-basis: 200px;
            max-width: 200px;
        }

        &.status {
            flex-basis: 200px;
            max-width: 200px;
        }

        &.strategyType {
            flex-basis: 325px;
            max-width: 325px;
        }

        &.strategyStatus {
            flex-basis: 200px;
            max-width: 200px;
        }

        &.numberFieldLarge {
            flex-basis: 325px;
            max-width: 325px;
        }

        &.strategy {
            flex-basis: 200px;
            max-width: 200px;
        }

        @include sm {
            flex-basis: 100% !important;
            max-width: 100% !important;
        }
    }

    .label {
        @extend %text-body-3;

        margin-bottom: 0.4rem;
        white-space: nowrap;
        font-size: 1.4rem;
        color: $base-800;
    }

    .buttonsWrapper {
        position: relative;
        display: flex;
        // justify-content: flex-end;
        align-items: center;
        flex-wrap: wrap;
        margin-top: auto;
        gap: 16px;
        padding-top: 16px;

        @include borderLine(true, false);

        .btn {
            min-width: 256px !important;

            @include sm {
                min-width: 100% !important;
                max-width: 100% !important;
                flex-basis: 100% !important;
            }
        }

        // @include md {
        //     margin-top: 40px;
        // }
        .buttonsInner {
            margin-left: auto;
        }
    }
</style>
