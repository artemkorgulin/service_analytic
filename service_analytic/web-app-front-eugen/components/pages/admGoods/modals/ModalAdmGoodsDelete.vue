<template>
    <BaseDialog v-model="isShow" content-class="modal-confirm-content" width="560">
        <VCard :class="$style.ModalAdmGoodsDelete">
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
                <h2 :class="$style.heading">
                    Вы уверены что хотите удалить
                    <br />
                    {{ pluralizedTitle }}?
                </h2>
                <BaseList
                    :class="$style.list"
                    :items="items"
                    max-height="calc(56px * 3)"
                    :headings="$options.HEADINGS"
                    :is-scrollable="!!items.length && items.length > 3"
                >
                    <BaseListItem
                        v-for="item in items"
                        :key="`list-item-${item.id}`"
                        :item="item"
                        :headings="$options.HEADINGS"
                    >
                        <BaseListCell width="12rem">
                            <AdmProductLink :id="item.id" :sku="item.sku" />
                        </BaseListCell>
                        <BaseListCell width="calc(100% - 12rem)" :text="item.name" />
                    </BaseListItem>
                </BaseList>
                <div :class="$style.btnWrapper">
                    <VBtn
                        :class="[$style.btn, $style.confirm]"
                        color="#FF3981"
                        dark
                        large
                        @click="handleConfirm"
                    >
                        Удалить
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
    const HEADINGS = [
        { value: 'sku', width: '12rem' },
        { value: 'name', width: 'calc(100% - 12rem)' },
    ];
    export default {
        name: 'ModalAdmGoodsDelete',
        HEADINGS,
        props: {
            items: {
                type: Array,
                default: () => [],
            },
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
                        '/api/adm/v1/campaign/goods/ids/store',
                        {
                            deleted_goods: this.items.map(
                                item => item.good_id || item.pivot.good_id
                            ),
                            campaign_id: this.$route.params.id,
                        }
                    );
                    console.log(
                        '🚀 ~ file: ModalAdmGoodsDelete.vue ~ line 77 ~ handleConfirm ~ response',
                        response
                    );
                    if (!response?.success) {
                        throw new Error('Ошибка при удалении товаров');
                    }
                    this.handleClose();
                    await this.$store.dispatch(
                        'campaign/fetchCampaignDataAndGoods',
                        this.$route.params.id
                    );
                    await this.$store.dispatch('campaign/unselectAllGoods');
                    await this.$notify.create({
                        message: `Успешно удалены ${this.pluralizedTitle}`,
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
                        message: `Ошибка при удалении ${this.pluralizedTitle}`,
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
    };
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .ModalAdmGoodsDelete {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 150px;
        border-radius: inherit;
    }

    .list {
        max-height: 56px * 4;
    }

    .inner {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        padding: 24px;
        gap: 24px;
        flex-direction: column;
        transition: $primary-transition;

        &.hidden {
            visibility: hidden;
            opacity: 0;
        }
    }

    .heading {
        @extend %text-h4;

        text-align: center;
    }

    .btnWrapper {
        gap: 16px;
        display: flex;
        width: 100%;
    }

    .btn {
        flex: 1;

        &.confirm {
            background-color: $error;
            box-shadow: 0 4px 16px rgba(255, 69, 181, 0.16);
        }
    }

    .loadingWrapper {
        @include centerer;
    }
</style>
