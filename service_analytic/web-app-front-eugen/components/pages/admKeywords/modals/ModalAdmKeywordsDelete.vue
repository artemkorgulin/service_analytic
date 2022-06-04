<template>
    <BaseDialog v-model="isShow" content-class="modal-confirm-content" width="560">
        <VCard :class="$style.wrapper">
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
                    :is-scrollable="!!items.length && items.length > 3"
                    max-height="calc(56px * 3)"
                    :headings="$options.HEADINGS"
                    is-show-headings
                >
                    <BaseListItem
                        v-for="listItem in items"
                        :key="`list-item-${listItem.id}`"
                        :headings="$options.HEADINGS"
                    >
                        <BaseListCell
                            v-for="head in $options.HEADINGS"
                            :key="listItem.id + head.value"
                            :width="head.width"
                            :text="listItem[head.value] || '-'"
                        />
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
        { text: 'Ключевое слово', value: 'name', width: 'calc(100% - 16.8rem - 12rem)' },
        { text: 'Популярность', value: 'popularity', width: '16.8rem' },
        { text: 'Ставка', value: 'bid', width: '12rem' },
    ];
    export default {
        name: 'ModalAdmKeywordsDelete',
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
                const plural = this.$options.filters.plural(this.items.length, [
                    'ключевое слово',
                    'ключевых слова',
                    'ключевых слов',
                ]);
                return this.items.length + ' ' + plural;
            },
        },
        methods: {
            async handleConfirm() {
                try {
                    this.isLoading = true;
                    const pickedObject = this.$store.getters['keywords/pickedElementObject'];
                    const response = await this.$axios.$delete(
                        `/api/adm/v2/campaign/${this.$route.params.id}/keywords`,
                        {
                            params: {
                                keyword_ids: this.items.map(item => item.keyword_id),
                                [pickedObject.isGroup ? 'group_id' : 'campaign_good_id']:
                                    this.$route.query.picked_id,
                            },
                        }
                    );
                    if (!response?.success || !response?.data?.updated_count) {
                        console.warn(
                            '🚀 ~ file: ModalAdmKeywordsDelete.vue ~ line 93 ~ handleConfirm ~ response',
                            response
                        );
                        throw new Error('err');
                    }
                    await this.$store.dispatch('keywords/unselectAllKeywords');
                    await this.$store.dispatch('keywords/fetchKeywords');
                    await this.$notify.create({
                        message: `Успешно удалены ${this.pluralizedTitle}`,
                        type: 'positive',
                        timeout: 5000,
                    });
                    this.handleClose();
                    this.isLoading = false;
                } catch (error) {
                    console.log(
                        '🚀 ~ file: ModalAdmKeywordsDelete.vue ~ line 109 ~ handleConfirm ~ error',
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
    .list {
        max-height: 56px * 4;
    }

    .wrapper {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 150px;
        border-radius: inherit;
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
