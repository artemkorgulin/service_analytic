<template>
    <BaseDialog v-model="isShow" content-class="modal-confirm-content" width="560">
        <VCard :class="$style.ModalAdmGoodsGroupDisband">
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
                <h2 :class="$style.heading">
                    Вы уверены что хотите расформировать группу "{{ name }}"?
                </h2>
                <BaseList
                    v-if="item.goods && item.goods.length"
                    :class="$style.list"
                    :items="item.goods"
                    :headings="$options.HEADINGS"
                    :is-scrollable="!!item.goods.length && item.goods.length > 3"
                    max-height="calc(56px * 3)"
                    height="100%"
                >
                    <BaseListItem
                        v-for="good in item.goods"
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
                    <VBtn :class="$style.btn" color="accent" large @click="handleConfirm">
                        Расформировать
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
        name: 'ModalAdmGoodsGroupDisband',
        HEADINGS,
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
        computed: {
            name() {
                return this.item?.name || 'Без названия';
            },
        },
        methods: {
            async handleConfirm() {
                try {
                    this.isLoading = true;
                    const response = await this.$axios.$put(
                        `/api/adm/v2/campaign/${this.$route.params.id}/groups/${this.item.id}`,
                        {
                            name: this.name,
                            goods: [],
                        }
                    );
                    console.log(
                        '🚀 ~ file: ModalAdmGoodsGroupDisband.vue ~ line 77 ~ handleConfirm ~ response',
                        response
                    );
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
                        message: `${this.name} была успешно расформирована`,
                        type: 'positive',
                        timeout: 5000,
                    });
                    this.isLoading = false;
                } catch (error) {
                    console.log(
                        '🚀 ~ file: ModalAdmGoodsGroupDisband.vue ~ line 84 ~ handleConfirm ~ error',
                        error
                    );
                    this.isLoading = false;
                    this.$notify.create({
                        message: `${this.name} не удалось расформировать`,
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
    .ModalAdmGoodsGroupDisband {
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

        .btn {
            flex: 1;
        }
    }

    .loadingWrapper {
        @include centerer;
    }
</style>
