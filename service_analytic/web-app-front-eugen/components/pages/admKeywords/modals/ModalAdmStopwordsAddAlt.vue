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
                    Вы уверены что хотите добавить
                    <br />
                    "{{ item }}" в минус слова?
                </h2>
                <BaseList
                    :class="$style.list"
                    max-height="calc(56px * 3)"
                    :headings="$options.HEADINGS"
                    is-show-headings
                >
                    <BaseListItem :headings="$options.HEADINGS">
                        <BaseListCell width="100%" :text="item" />
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
                        добавить
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
    const HEADINGS = [{ text: 'Стоп слово', value: 'name', width: '100%' }];
    export default {
        name: 'ModalAdmStopwordsAddAlt',
        HEADINGS,
        props: {
            item: {
                type: [String, Object],
                default: '',
            },
        },
        data() {
            return {
                isShow: true,
                isLoading: false,
            };
        },
        // computed: {
        //     pluralizedTitle() {
        //         const plural = this.$options.filters.plural(this.items.length, ['стоп слово', 'стоп слова', 'стоп слов']);
        //         return this.items.length + ' ' + plural;
        //     },
        // },
        methods: {
            async handleConfirm() {
                try {
                    this.isLoading = true;
                    const pickedObject = this.$store.getters['keywords/pickedElementObject'];
                    const response = await this.$axios.$post(
                        `/api/adm/v2/campaign/${this.$route.params.id}/stop-words`,
                        {
                            stop_words: [
                                {
                                    stop_word_name: this.item,
                                    [pickedObject.isGroup ? 'group_id' : 'campaign_good_id']:
                                        this.$route.query.picked_id,
                                },
                            ],
                        }
                    );
                    console.log(
                        '🚀 ~ file: ModalAdmStopwordsAddAlt.vue ~ line 87 ~ handleConfirm ~ response',
                        response
                    );
                    if (!response?.success) {
                        throw new Error('err');
                    }
                    await this.$store.dispatch('keywords/unselectAllStopwords');
                    await this.$store.dispatch('keywords/fetchStopwords');
                    await this.$notify.create({
                        message: `Успешно добавлено минус слово "${this.item}"`,
                        type: 'positive',
                        timeout: 5000,
                    });
                    this.handleClose();
                    this.isLoading = false;
                } catch (error) {
                    console.log(
                        '🚀 ~ file: ModalAdmStopwordsAddAlt.vue ~ line 109 ~ handleConfirm ~ error',
                        error
                    );

                    this.isLoading = false;
                    this.$notify.create({
                        message: `Ошибка при добавлении минус слова "${this.item}"`,
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
