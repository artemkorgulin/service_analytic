<template>
    <BaseDrawer v-model="isShow" width="600px">
        <VFadeTransition mode="out-in" appear>
            <VProgressCircular
                v-if="isLoading"
                key="loading"
                :class="$style.loadingWrapper"
                indeterminate
                size="50"
                color="accent"
            />
            <div v-else key="content" :class="[$style.inner, isLoading && $style.hidden]">
                <h2 :class="$style.heading">Изменение ставки</h2>
                <VTextField
                    v-model="form.cpm.$model.value"
                    :class="$style.input"
                    :error-messages="form.cpm.$errorMessage.value"
                    autocomplete="new-password"
                    label="Ставка"
                    dense
                    outlined
                    @blur="form.cpm.$touch"
                    @input="form.cpm.$resetExtError"
                />
                <BaseList
                    :class="$style.list"
                    :headings="$options.HEADINGS"
                    :is-scrollable="!!innerItems.length && innerItems.length > 3"
                    is-show-headings
                    max-height="calc(100% - 56px)"
                >
                    <template #heading-append>
                        <BaseTableHeadingCell width="56px" max-width="56px" />
                    </template>
                    <BaseListItem
                        v-for="listItem in innerItems"
                        :key="`list-item-${listItem.id}`"
                        :headings="$options.HEADINGS"
                        icon="outlined/close"
                        @eventEmitted="handleKeywordDelete(listItem)"
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
                        :class="$style.btn"
                        color="accent"
                        block
                        :disabled="$invalid"
                        @click="handleConfirm"
                    >
                        Изменить
                    </VBtn>
                </div>
            </div>
        </VFadeTransition>
    </BaseDrawer>
</template>

<script>
    import { mapGetters } from 'vuex';
    import { defineComponent, reactive } from '@nuxtjs/composition-api';
    import { useForm } from '~use/form';
    import { useField } from '~use/field';
    import { required } from '~utils/patterns';
    import { unrefObject } from '~utils/composition';

    const HEADINGS = [
        { text: 'Ключевое слово', value: 'name', width: 'calc(100% - 250px - 56px)' },
        { text: 'Популярность', value: 'popularity', width: '150px' },
        { text: 'Ставка', value: 'bid', width: '100px' },
    ];
    const BID_MIN = 35;
    export default defineComponent({
        name: 'ModalAdmKeywordsChangeCPM',
        props: {
            items: {
                type: Array,
                default: () => [],
            },
        },
        setup() {
            const formFields = {
                cpm: {
                    validators: {
                        required,
                        minValue(val) {
                            if (typeof val === 'undefined' || val === null || val === '') {
                                return true;
                            }
                            return val >= BID_MIN;
                        },
                    },
                    errorMessages: {
                        required: 'Укажите ставку',
                        minValue: `Ставка не может быть меньше ${BID_MIN} руб.`,
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
        HEADINGS,
        data() {
            return {
                isShow: true,
                isLoading: false,
                innerItems: [...this.items],
            };
        },
        computed: {
            ...mapGetters('keywords', {
                pickedElementObject: 'pickedElementObject',
            }),
            pluralizedTitle() {
                const plural = this.$options.filters.plural(this.innerItems.length, [
                    'ключевое слово',
                    'ключевых слова',
                    'ключевых слов',
                ]);
                return this.innerItems.length + ' ' + plural;
            },
            selectedIds() {
                return this.innerItems.map(item => item.id);
            },
        },
        methods: {
            async handleConfirm() {
                try {
                    this.isLoading = true;
                    const formModel = unrefObject(this.$model);
                    const response = await this.$axios.$put(
                        `/api/adm/v2/campaign/${this.$route.params.id}/keywords`,
                        {
                            keyword_ids: this.selectedIds,
                            bid: formModel.cpm,
                        }
                    );
                    if (!response?.success) {
                        throw new Error('err');
                    }
                    await this.$store.dispatch('keywords/unselectAllKeywords');
                    await this.$store.dispatch('keywords/fetchKeywords');
                    await this.$notify.create({
                        message: `Успешно изменен CPM ${this.pluralizedTitle}`,
                        type: 'positive',
                        timeout: 5000,
                    });
                    this.handleClose();
                    this.isLoading = false;
                } catch (error) {
                    this.isLoading = false;
                    this.$notify.create({
                        message: `Ошибка при изменении CPM ${this.pluralizedTitle}`,
                        type: 'negative',
                        timeout: 5000,
                    });
                    return this?.$sentry?.captureException(error);
                }
            },
            handleClose() {
                this.isShow = false;
            },
            handleKeywordDelete(val) {
                const index = this.innerItems.indexOf(val);
                this.innerItems.splice(index, 1);
            },
        },
    });
</script>

<style lang="scss" module>
    .list {
        flex: 1;
        max-height: 100%;
    }

    .input {
        width: 100%;
    }

    .inner {
        display: flex;
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
