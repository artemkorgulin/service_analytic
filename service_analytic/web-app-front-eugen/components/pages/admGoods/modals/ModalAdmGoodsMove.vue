<template>
    <BaseDialog v-model="isShow" content-class="modal-confirm-content" width="560">
        <VCard :class="$style.ModalAdmGoodsMove">
            <div :class="$style.closeBtnWrapper">
                <VBtn :class="$style.closeBtn" icon outlined color="base-900" @click="handleClose">
                    <SvgIcon name="outlined/close" />
                </VBtn>
            </div>
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
                <h2 :class="$style.heading">Переместить {{ pluralizedTitle }} в группу</h2>
                <VSelect
                    v-model="form.group.$model.value"
                    :class="$style.input"
                    :error-messages="form.group.$errorMessage.value"
                    :items="options"
                    :menu-props="{
                        'offset-y': true,
                        'z-index': 1000,
                        'content-class': 'formFieldSelectMenu',
                    }"
                    label="Группа"
                    item-text="text"
                    item-value="value"
                    dense
                    outlined
                    @blur="form.group.$touch"
                    @input="form.group.$resetExtError"
                />
                <BaseList
                    :class="$style.list"
                    :items="items"
                    :headings="$options.HEADINGS"
                    :is-scrollable="!!items.length && items.length > 3"
                    max-height="calc(56px * 3)"
                    height="100%"
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
                        :class="$style.btn"
                        color="accent"
                        block
                        :disabled="$invalid"
                        @click="handleConfirm"
                    >
                        Переместить
                    </VBtn>
                </div>
            </div>
        </VCard>
    </BaseDialog>
</template>

<script>
    import { mapState } from 'vuex';
    import { defineComponent, reactive } from '@nuxtjs/composition-api';
    import { useForm } from '~use/form';
    import { useField } from '~use/field';
    import { required } from '~utils/patterns';
    const HEADINGS = [
        { value: 'sku', width: '12rem' },
        { value: 'name', width: 'calc(100% - 12rem)' },
    ];
    export default defineComponent({
        name: 'ModalAdmGoodsMove',
        HEADINGS,
        props: {
            items: {
                type: Array,
                default: () => [],
            },
            item: {
                type: Object,
                default: () => ({}),
            },
        },
        setup() {
            const formFields = {
                group: {
                    validators: { required },
                    errorMessages: {
                        required: 'Укажите группу',
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
            ...mapState('campaign', {
                groups: state => state.groups,
            }),
            options() {
                return this.groups.map(({ id, name }) => ({
                    value: id,
                    text: name || `Без названия - ID:${id}`,
                }));
            },
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
                    this.$touch();
                    if (this.$invalid) {
                        return;
                    }
                    this.isLoading = true;
                    const response = await this.$axios.$post(
                        '/api/adm/v1/campaign/goods/ids/store',
                        {
                            with_group: {
                                group: {
                                    id: this.form.group.$model.value,
                                },
                                goods: this.items.map(item => item.good_id),
                            },
                            campaign_id: this.$route.params.id,
                        }
                    );
                    this.isLoading = false;
                    if (!response?.success) {
                        throw new Error('err');
                    }
                    this.handleClose();
                    await this.$store.dispatch(
                        'campaign/fetchCampaignDataAndGoods',
                        this.$route.params.id
                    );
                    await this.$store.dispatch('campaign/unselectAllGoods');
                    await this.$notify.create({
                        message: `${this.pluralizedTitle} были успешно перемещены в группу`,
                        type: 'positive',
                        timeout: 5000,
                    });
                } catch (error) {
                    console.log(
                        '🚀 ~ file: ModalAdmGoodsDelete.vue ~ line 84 ~ handleConfirm ~ error',
                        error
                    );
                    this.isLoading = false;
                    this.$notify.create({
                        message: `Ошибка при перемещении ${this.pluralizedTitle} в группу`,
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
    .ModalAdmGoodsMove {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 150px;
        border-radius: inherit;
    }

    .closeBtnWrapper {
        position: absolute;
        top: 24px;
        right: 24px;

        .closeBtn {
            width: 32px;
            height: 32px;
        }
    }

    .list {
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
        padding-right: 32px;
        padding-left: 32px;
        text-align: center;
    }

    .btnWrapper {
        gap: 16px;
        display: flex;
        width: 100%;
    }

    .loadingWrapper {
        @include centerer;
    }
</style>
