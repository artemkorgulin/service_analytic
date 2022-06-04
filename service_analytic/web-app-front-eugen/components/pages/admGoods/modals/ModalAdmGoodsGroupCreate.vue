<template>
    <BaseDialog v-model="isShow" content-class="modal-confirm-content" width="560">
        <VCard :class="$style.ModalAdmGoodsGroupCreate">
            <VFadeTransition mode="out-in" appear>
                <VProgressCircular
                    v-if="isLoading"
                    key="loading"
                    :class="$style.loadingWrapper"
                    indeterminate
                    size="50"
                    color="accent"
                />
            </VFadeTransition>
            <div key="content" :class="[$style.inner, isLoading && $style.hidden]">
                <h2 :class="$style.heading">Создание группы</h2>
                <VTextField
                    v-model="form.name.$model.value"
                    :class="$style.input"
                    :error-messages="form.name.$errorMessage.value"
                    label="Название группы"
                    outlined
                    dense
                    @blur="form.name.$touch"
                    @input="form.name.$resetExtError"
                />
                <BaseList
                    :class="$style.list"
                    :headings="$options.HEADINGS"
                    max-height="calc(56px * 3)"
                    :is-scrollable="!!items.length && items.length > 3"
                >
                    <BaseListItem
                        v-for="good in items"
                        :key="`list-item-${good.id}`"
                        :headings="$options.HEADINGS"
                    >
                        <BaseListCell width="12rem">
                            <AdmProductLink :id="good.id" :sku="good.sku" />
                        </BaseListCell>
                        <BaseListCell width="calc(100% - 12rem)" :text="good.name" />
                    </BaseListItem>
                </BaseList>
                <div :class="$style.btnWrapper">
                    <VBtn
                        :class="[$style.btn, $style.confirm]"
                        color="accent"
                        large
                        :disabled="$invalid"
                        @click="handleConfirm"
                    >
                        Создать
                    </VBtn>
                    <VBtn :class="[$style.btn, $style.cansel]" outlined large @click="handleClose">
                        Отменить
                    </VBtn>
                </div>
            </div>
        </VCard>
    </BaseDialog>
</template>

<script>
    import { defineComponent, reactive } from '@nuxtjs/composition-api';
    import { useForm } from '~use/form';
    import { useField } from '~use/field';
    import { minLength, required } from '~utils/patterns';
    const HEADINGS = [
        { value: 'sku', width: '12rem' },
        { value: 'name', width: 'calc(100% - 12rem)' },
    ];
    export default defineComponent({
        name: 'ModalAdmGoodsGroupCreate',
        HEADINGS,
        props: {
            items: {
                type: Array,
                default: () => [],
            },
        },
        setup() {
            const formFields = {
                name: {
                    validators: { required, minLength: minLength(3) },
                    errorMessages: {
                        required: 'Введите название',
                        minLength: 'Слишком короткое название',
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
                isShow: true,
                isLoading: false,
            };
        },
        computed: {
            pluralizedTitle() {
                return (
                    this.items.length +
                    ' ' +
                    this.$options.filters.plural(this.items.length, [
                        'выбранный товар',
                        'выбранных товарa',
                        'выбранных товаров',
                    ])
                );
            },
        },
        methods: {
            async handleConfirm() {
                try {
                    this.isLoading = true;
                    const response = await this.$axios.$post(
                        `/api/adm/v2/campaign/${this.$route.params.id}/groups`,
                        {
                            name: this.form.name.$model.value,
                            goods: this.items.map(item => item.good_id),
                        }
                    );
                    console.log(
                        '🚀 ~ file: ModalAdmGoodsDelete.vue ~ line 77 ~ handleConfirm ~ response',
                        response
                    );
                    if (!response?.success) {
                        throw new Error('Ошибка при создании группы');
                    }
                    this.handleClose();
                    await this.$store.dispatch(
                        'campaign/fetchCampaignDataAndGoods',
                        this.$route.params.id
                    );
                    await this.$store.dispatch('campaign/unselectAllGoods');
                    await this.$notify.create({
                        message: 'Группа успешно создана',
                        type: 'positive',
                        timeout: 5000,
                    });
                    this.isLoading = false;
                } catch (error) {
                    console.log(
                        '🚀 ~ file: ModalAdmGoodsDelete.vue ~ line 84 ~ handleConfirm ~ error',
                        error
                    );
                    this.isLoading = false;
                    this.$notify.create({
                        message: 'Ошибка при создании группы',
                        type: 'negative',
                        timeout: 5000,
                    });
                    return this?.$sentry?.captureException(error);
                }
            },
            handleClose() {
                this.isShow = false;
            },
        },
    });
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .ModalAdmGoodsGroupCreate {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 150px;
        border-radius: inherit;
    }

    .list {
        max-height: 56px * 4;
        margin-bottom: 24px;
    }

    .input {
        width: 100%;
        margin-bottom: 24px;
    }

    .inner {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        padding: 24px;
        flex-direction: column;
        transition: $primary-transition;

        &.hidden {
            visibility: hidden;
            opacity: 0;
        }
    }

    .heading {
        @extend %text-h4;

        margin-bottom: 24px;
        text-align: center;
    }

    .btnWrapper {
        gap: 16px;
        display: flex;
        width: 100%;
    }

    .btn {
        flex: 1;
    }

    .loadingWrapper {
        @include centerer;
    }
</style>
