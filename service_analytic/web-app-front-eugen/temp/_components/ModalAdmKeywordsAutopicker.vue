<template>
    <BaseDialog v-model="isShow" content-class="modal-autopicker" width="560">
        <VCard :class="$style.ModalAdmAutopicker">
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
                <div :class="$style.closeBtnWrapper">
                    <VBtn
                        :class="$style.closeBtn"
                        icon
                        outlined
                        color="base-900"
                        @click="handleClose"
                    >
                        <SvgIcon name="outlined/close" />
                    </VBtn>
                </div>
                <h2 :class="$style.heading">Автоподбор ключевых слов</h2>
                <FormFieldText
                    :class="[$style.input, $style.keywordInput]"
                    field="keyword"
                    label="Базовое ключевое слово"
                    hide-details
                    persistent-label
                />
                <FormFieldSelect
                    :class="[$style.input, $style.categoryInput]"
                    label="Категория"
                    field="category"
                    :items="categories"
                    item-text="name"
                    item-value="id"
                    hide-details
                    persistent-label
                    z-index="2000"
                />
                <InputDateWithActivator
                    :class="[$style.input, $style.dateInput]"
                    field="date"
                    hide-details
                    persistent-label
                    label="Временной диапазон"
                />
                <div :class="$style.btnWrapper">
                    <VBtn
                        :class="[$style.btn, $style.confirm]"
                        color="accent"
                        large
                        block
                        :disabled="isSubmitDisabled"
                        @click="handleConfirm"
                    >
                        Подобрать
                    </VBtn>
                </div>
            </div>
        </VCard>
    </BaseDialog>
</template>

<script>
    import { mapState, mapActions } from 'vuex';
    import Validator from '~mixins/validator';
    export default {
        name: 'ModalAdmKeywordsAutopicker',
        mixins: [Validator],
        props: {
            item: {
                type: Object,
                default: () => ({}),
            },
        },
        data() {
            return {
                isShow: true,
                isLoading: false,
            };
        },
        fetch() {
            return this.fetchKeywordsCategories();
        },
        computed: {
            ...mapState('keywords', {
                categories: state => state.keywordsCategories.data,
            }),
        },
        methods: {
            ...mapActions('keywords', {
                fetchKeywordsCategories: 'fetchKeywordsCategories',
            }),
            async handleConfirm() {
                //
            },
            handleClose() {
                this.isShow = false;
            },
        },
    };
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .ModalAdmAutopicker {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 150px;
        border-radius: inherit;
    }

    .dateInput {
        width: 100%;
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

        .input {
            width: 100%;
            margin-bottom: 16px;
        }
    }

    .heading {
        @extend %text-h4;

        margin-bottom: 24px;
        text-align: center;
    }

    .btnWrapper {
        display: flex;
        width: 100%;
        margin-top: 16px;
    }

    .loadingWrapper {
        @include centerer;
    }
</style>
