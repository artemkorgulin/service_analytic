<template>
    <BaseChildPage :class="$style.AdCreatePage" width="600px" @close="handleClose">
        <h1 :class="$style.pageHeading">{{ pageTitle }}</h1>
        <div :class="$style.inputsWrapper">
            <VTextField
                v-model="form.name.$model.value"
                :class="[$style.formField, $style.grow, $style.name]"
                :error-messages="form.name.$errorMessage.value"
                autocomplete="new-password"
                label="Название рекламной кампании"
                dense
                outlined
                @blur="form.name.$touch"
                @input="form.name.$resetExtError"
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
                    'z-index': 1000,
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
                    'z-index': 1000,
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
                hint="Минимум 500 ₽"
                dense
                outlined
                @blur="form.budget.$touch"
                @input="form.budget.$resetExtError"
            />
        </div>
        <div :class="$style.buttonsWrapper">
            <VBtn
                :class="$style.btn"
                color="accent"
                block
                :disabled="$invalid"
                :loading="isLoading"
                @click="handleAction"
            >
                Создать
            </VBtn>
        </div>
    </BaseChildPage>
</template>
<router>
{
  name: 'campaign-create',
  path: '/:marketplace/campaign/create',
  meta: {
    pageGroup: "perfomance",
    redirectOnChangeMarketplace: true,
    isEnableGoBack:true,
    fallbackRoute: {
      name: "adm-campaigns"
    },
  }
}
</router>
<script>
    /* eslint-disable no-empty-function,camelcase */
    import { defineComponent, reactive } from '@nuxtjs/composition-api';
    import { useForm } from '~use/form';
    import { useField } from '~use/field';
    import { minLength, required } from '~utils/patterns';
    import { unrefObject } from '~utils/composition';
    import { getErrorMessage } from '~utils/error';
    function getErrors(error) {
        if (typeof error !== 'object' || !Object.keys(error).length) {
            return [];
        }
        return Object.entries(error).reduce((acc, val) => {
            if (val?.length > 1 && val[1].length && val[1][0]) {
                acc.push(val[1][0]);
            }
            return acc;
        }, []);
    }

    export default defineComponent({
        name: 'CampaignCreatePage',

        transition: {
            name: 'drawer',
            mode: 'out-in',
            duration: 1000,
        },
        setup() {
            const formFields = {
                name: {
                    validators: { required, minLength: minLength(2) },
                    errorMessages: {
                        required: 'Введите название кампании',
                        minLength: 'Слишком короткое название кампании',
                    },
                },
                date: {},
                placement_id: {
                    validators: { required },
                    errorMessages: {
                        required: 'Укажите тип кампании',
                    },
                },
                payment_type_id: {
                    validators: { required },
                    errorMessages: {
                        required: 'Укажите payment_type_id',
                    },
                },
                budget: {
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
            };
            const formObject = reactive({});
            for (const field in formFields) {
                formObject[field] = useField(formFields[field], formObject);
            }
            const $validation = useForm(formObject);
            return {
                ...$validation,
            };
        },
        data() {
            return {
                pageTitle: 'Создание рекламной кампании',
                isLoading: false,
                notification: () => {},
                dateFacet: {
                    min: this.$options.filters.formatDateTime(new Date(), '$y-$m-$d'),
                },
            };
        },
        head() {
            return {
                title: this.pageTitle,
            };
        },
        // computed: {
        //     account_id() {
        //         return this.$store.getters['user/ozonPerfomanceSelectedAccount']?.id;
        //     },
        // },
        methods: {
            handleClose() {
                return this.$router.push({
                    name: 'adm-campaigns',
                    params: this.$route.params,
                    query: this.$route.query,
                });
            },

            async handleAction() {
                try {
                    this.$touch();
                    if (this.$invalid) {
                        return;
                    }
                    this.notification();
                    this.isLoading = true;
                    if (!this.account_id) {
                        throw new Error('No account id');
                    }

                    const { date, budget, ...dataOther } = unrefObject(this.$model);
                    let start_date;
                    let end_date;
                    if (date && date?.length === 2) {
                        start_date = date[0];
                        end_date = date[1];
                    }
                    const params = {
                        account_id: this.account_id,
                        budget: Number(budget),
                        ...dataOther,
                    };
                    if (start_date && end_date) {
                        params.start_date = start_date;
                        params.end_date = end_date;
                    }
                    const data = await this.$axios.$post('/api/adm/v2/campaigns', params);
                    console.log('🚀 ~ file: create.vue ~ line 166 ~ handleAction ~ data', data);
                    this.isLoading = false;
                    const id = data?.data?.id;
                    console.log('🚀 ~ file: create.vue ~ line 159 ~ handleAction ~ id', id);
                    if (!data?.success || !id) {
                        if (data.errors) {
                            this.serverErrors = data.errors;
                        }
                        throw getErrors(data?.errors) || 'Произошла ошибка';
                    }
                    this.notification = await this.$notify.create({
                        message: `Кампания ${data?.data?.name || id} успешно создана`,
                        timeout: 3000,
                        type: 'positive',
                    });
                    return this.$router.push({
                        name: 'campaign-goods',
                        params: { id, ...this.$route.params },
                    });
                } catch (error) {
                    const errorMessage = getErrorMessage(error);
                    this.isLoading = false;
                    this.notification = await this.$notify.create({
                        message: errorMessage,
                        type: 'negative',
                    });
                    return this?.$sentry?.captureException(errorMessage);
                }
            },
        },
    });
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .AdCreatePage {
        padding: 24px 32px;
    }

    .sheet {
        padding: 24px;
        border-radius: 24px;
        background: #fff;
        box-shadow: var(--shadow-primary);
    }

    .pageHeading {
        @extend %text-h1;

        margin-bottom: 2.2rem;
        font-size: 26px;
        color: $base-900;
        user-select: none;
    }

    .inputsWrapper {
        display: flex;
        flex-wrap: wrap;
        // max-width: 790px;
        margin-right: -8px;
        margin-left: -8px;

        @include respond-to(md) {
            flex-wrap: wrap;
        }
    }

    .formField {
        padding-right: 8px !important;
        padding-left: 8px !important;
        flex-basis: 100%;
    }

    .label {
        @extend %text-body-3;

        margin-bottom: 0.4rem;
        white-space: nowrap;
        font-size: 1.4rem;
        color: $base-800;
    }

    .buttonsWrapper {
        width: 100%;
        margin-top: auto;
        gap: 8px;
    }
</style>
