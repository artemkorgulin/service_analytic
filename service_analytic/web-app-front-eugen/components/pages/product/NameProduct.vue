<template>
    <div
        class="product-content__panel product-content__panel--full-width"
        :class="$style.NameProduct"
    >
        <SeAlert v-if="showError" type="alert" class="mb-2">
            Укажите больше особенностей товара в названии. Максимальное количество символов —
            {{ [250, 100][isSelectedMp.id - 1] }}
        </SeAlert>
        <SeAlert class="mb-8">
            Используйте шаблон
            <b>Тип + Характеристики.</b> Постарайтесь перечислить как можно больше особенностей
            товара в характеристиках, укажите: назначение, модель, материалы, цвет, вес и размеры.
            Названия в некоторых категориях товаров не отображаются на сайте полностью, но влияют на
            поисковую оптимизацию карточки
        </SeAlert>
        <VForm ref="formAction" class="custom-form custom-form--not-required-symbol">
            <VTextField
                v-if="marketplaceSlug === 'wildberries'"
                v-model="title"
                :rules="[...form.rules.name, v => v.length <= 100 || 'Максимум 100 символов']"
                label="Название"
                dense
                class="light-inline"
                color="#710bff"
                counter="100"
                @change="onChange('title', 'Изменено имя товара')"
            />
            <VTextField
                v-else
                v-model="title"
                :rules="[...form.rules.name, v => v.length <= 250 || 'Максимум 250 символов']"
                label="Название"
                class="light-inline"
                dense
                counter="250"
                color="#710bff"
                @change="onChange('name', 'Изменено имя товара')"
            />
        </VForm>
    </div>
</template>

<script>
    /* eslint-disable no-extra-parens */
    import { mapMutations, mapGetters, mapState } from 'vuex';
    import formMixin from '~mixins/form.mixin';
    import productMixin from '~mixins/product.mixin';
    // import NoteItem from '~/components/common/NoteItem.vue';

    export default {
        name: 'NameProduct',
        mixins: [formMixin, productMixin],
        props: {
            values: {
                type: Object,
                default: null,
            },
            recommendations: {
                type: Array,
                default: () => [],
            },
        },
        data() {
            return {
                form: {
                    fields: {
                        name: '',
                        title: '',
                    },
                    rules: {
                        name: [
                            val => Boolean(val) || 'Пожалуйста, укажите название',
                            val =>
                                (val && val.length > 5) || 'Имя должно содержать более 5 символов',
                        ],
                    },
                },
            };
        },
        computed: {
            ...mapState('product', ['dataWildberries', 'commonData']),
            ...mapGetters('product', ['getCategory']),
            ...mapGetters({
                marketplaceSlug: 'getSelectedMarketplaceSlug',
                isSelectedMp: 'isSelectedMp',
                getNameWb: 'product/getNameWb',
            }),
            title: {
                get() {
                    return this.commonData.title;
                },
                set(value) {
                    const field = this.isSelectedMp.id === 1 ? 'name' : 'title';
                    this.form.fields[field] = value;
                    this.setCommonField({ field: 'title', value });

                    this.$store.commit('product/setSignalAlert', {
                        field: 'name',
                        value: this.showError,
                    });
                },
            },
            showError() {
                /* eslint-disable */
                try {
                    const { name, title } = this.form.fields;
                    const index = this.isSelectedMp.id - 1;
                    const value = [name, title][index];
                    return [230, 80][index] > value.length;
                } catch {
                    return false;
                }
            },
            maxChar() {
                return this.isSelectedMp.id === 1 ? 250 : 100;
            },
        },
        mounted() {
            this.$store.commit('product/setSignalAlert', { field: 'name', value: this.showError });
        },
        watch: {
            'values.name': {
                handler() {
                    this.reloadField('name');
                },
            },
            'values.title': {
                handler() {
                    this.$store.commit('');
                    this.reloadField('title');
                },
            },
        },
        methods: {
            ...mapMutations('product', ['setCommonField']),
            setFields() {
                if (this.isSelectedMp.id === 2) {
                    this.form.fields.title = this.getNameWb;
                } else {
                    Object.keys(this.values).forEach(key => {
                        const value = this.getValue(key);
                        this.setValue(value, key);
                    });
                }
            },
        },
    };
</script>

<style lang="scss" module>
    .NameProduct {
        padding-top: 0;
    }
</style>
