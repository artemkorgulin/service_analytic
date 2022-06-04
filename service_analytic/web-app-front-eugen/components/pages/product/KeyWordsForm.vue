<template>
    <div class="product-content__panel--full-width" :class="$style.KeyWordsForm">
        <perfect-scrollbar style="height: 100%">
            <VForm ref="formAction" class="custom-form custom-form--not-required-symbol">
                <div
                    class="se-form__item"
                    :class="activeField === 'title' ? 'se-form__item_active' : ''"
                    @click="selectedActive('title')"
                >
                    <VTextField
                        v-if="marketplaceSlug === 'wildberries'"
                        ref="title"
                        v-model="title"
                        :class="[$style.field, getActiveField === 'title' && $style.fieldActive]"
                        :rules="[
                            ...form.rules.name,
                            v => maxCountForField(v, 'title', 'Название'),
                            v => v.length <= 100 || 'Максимум 100 символов',
                        ]"
                        label="Название"
                        row="1"
                        counter="100"
                        class="light-inline"
                        dense
                        @focus="handleFieldFocus('title')"
                    />
                    <VTextField
                        v-else
                        ref="title"
                        v-model="title"
                        :class="[$style.field, getActiveField === 'title' && $style.fieldActive]"
                        :rules="[
                            ...form.rules.name,
                            v => maxCountForField(v, 'title', 'Название'),
                            v => v.length <= 255 || 'Максимум 255 символов',
                        ]"
                        label="Название"
                        row="1"
                        counter="255"
                        class="light-inline"
                        dense
                        @focus="handleFieldFocus('title')"
                        @change="$emit('getFieldValue', title)"
                    />
                </div>
                <template v-if="marketplaceSlug === 'wildberries'">
                    <SoTextArea
                        v-for="(item, index) in wbField"
                        :key="item"
                        :ref="`wbItem${index}`"
                        :field="item"
                        :active-field="activeField === item"
                        @selectedActive="selectedActive($event)"
                    ></SoTextArea>
                </template>
                <div
                    class="se-form__item"
                    :class="activeField === 'description' ? 'se-form__item_active' : ''"
                    @click="selectedActive('description')"
                >
                    <VTextarea
                        v-if="marketplaceSlug === 'wildberries'"
                        ref="description"
                        v-model="descr"
                        label="Описание"
                        :rules="[
                            v => maxCountForField(v, 'description', 'Описание'),
                            v => v.length <= 1000 || 'Максимум 1000 символов',
                        ]"
                        grow="3"
                        counter="1000"
                        class="light-inline"
                        @focus="handleFieldFocus('description')"
                    ></VTextarea>
                    <VTextarea
                        v-else
                        ref="description"
                        v-model="descr"
                        label="Описание"
                        :rules="[v => maxCountForField(v, 'description', 'Описание')]"
                        grow="3"
                        class="light-inline"
                        @focus="handleFieldFocus('description')"
                    ></VTextarea>
                </div>
            </VForm>
        </perfect-scrollbar>
    </div>
</template>

<script>
    /* eslint-disable */
    import { mapState, mapMutations, mapGetters, mapActions } from 'vuex';
    import SoTextArea from '~/components/common/SoTextArea.vue';
    import formMixin from '~mixins/form.mixin';
    import productMixin from '~mixins/product.mixin';
    import productPickList from '~mixins/productPickList.mixin';

    export default {
        components: { SoTextArea },
        name: 'KeyWordsForm',
        mixins: [formMixin, productMixin, productPickList],
        props: {
            values: {
                type: Object,
                default: () => ({}),
            },
            recommendations: {
                type: Object,
                default: () => ({}),
            },
        },
        data() {
            return {
                wbField: [],
                activeField: 'title',
                counterWordInsert: {
                    description: 0,
                    title: 0,
                    purpose: 0,
                    direction: 0,
                },
                form: {
                    fields: {
                        title: null,
                        purpose: null,
                        direction: null,
                        description: null,
                        keywordsForm: true,
                    },
                    rules: {
                        name: [val => Boolean(val) || 'Пожалуйста, укажите название'],
                    },
                },
                charactersLimit: {
                    wildberries: {
                        title: 100,
                        purpose: 100,
                        direction: 100,
                        description: 1000,
                    },
                    ozon: {
                        title: 250,
                        description: 0,
                    },
                },
                fieldsOrder: {
                    wildberries: ['title', 'purpose', 'direction', 'description'],
                    ozon: ['title', 'description'],
                },
                descriptionAutofilled: false,
            };
        },
        computed: {
            ...mapState('product', ['commonData']),
            ...mapGetters('product', ['getProduct']),
            ...mapGetters(['isSelectedMp']),
            ...mapGetters({
                marketplaceSlug: 'getSelectedMarketplaceSlug',
                productOzon: 'product/GET_PRODUCT',
                productWildberries: 'product/getProductWildberries',
                getActiveField: 'product/getActiveField',
                getActiveFieldNewValue: 'product/getActiveFieldNewValue',
                pickListSorted: 'product/getPickListSorted',
            }),
            title: {
                get() {
                    return this.commonData.title;
                },
                set(value) {
                    this.form.fields.title = value;
                    this.setCommonField({ field: 'title', value });
                },
            },
            descr: {
                get() {
                    return this.commonData.descr;
                },
                set(value) {
                    this.form.fields.description = value;
                    this.setCommonField({ field: 'descr', value });
                },
            },
            newValueTrigger() {
                return (
                    JSON.stringify(this.getActiveField) +
                    JSON.stringify(this.getActiveFieldNewValue)
                );
            },
            pickListFlattened() {
                return this.pickListSorted.flat();
            },
        },

        watch: {
            newValueTrigger() {
                if (this.getActiveField && this.getActiveFieldNewValue) {
                    this.handleFieldAutofill(this.getActiveField, this.getActiveFieldNewValue);
                }
            },
            values: {
                deep: true,
                immediate: true,
                handler(val, oldVal) {
                    Object.keys(val).forEach(key => {
                        if (!oldVal?.[key] || oldVal[key] !== val[key]) {
                            this.reloadField(key);
                        }
                    });
                },
            },
        },

        mounted() {
            if (this.isSelectedMp.id === 2) {
                let { searchOptimizationFields } = this.getProduct;
                this.wbField = Array.isArray(searchOptimizationFields)
                    ? searchOptimizationFields
                    : [];
            }
        },

        methods: {
            ...mapActions({
                setActiveField: 'product/setActiveField',
                setActiveFieldNewValue: 'product/setActiveFieldNewValue',
                setOpenPanels: 'product/setOpenPanels',
                setKeywordActiveness: 'product/setKeywordActiveness',
            }),
            ...mapMutations('product', ['setCommonField']),
            crossroads(nameFunc) {
                const { addWordInField } = this;
                const methodList = {
                    addWordInField,
                };
                return methodList[nameFunc];
            },
            addWordInField(value) {
                try {
                    const { $el } = this;
                    const getActiveEl = $el.querySelector('.se-form__item_active');
                    let inputEl;

                    ['textarea', 'input'].forEach(el => {
                        const res = getActiveEl.querySelector(el);
                        if (res) {
                            inputEl = res;
                        }
                    });

                    /* eslint-disable */
                    const isOzon = this.isSelectedMp.id === 1;
                    const insertNewLine = value => `\n ${value}`;
                    const insertSemicolon = value => `, ${value}`;
                    const insertWithSpace = value => ` ${value}`;

                    const counterCurrentField = this.counterWordInsert[this.activeField];

                    const insertDescr = value => {
                        if (isOzon) {
                            return !counterCurrentField
                                ? insertNewLine(value)
                                : insertSemicolon(value);
                        } else {
                            return !counterCurrentField
                                ? insertWithSpace(value)
                                : insertSemicolon(value);
                        }
                    };

                    const formatValues = {
                        description: insertDescr,
                        title: insertWithSpace,
                        packageSet: insertWithSpace,
                        purpose: insertWithSpace,
                        direction: insertWithSpace,
                    };

                    this.counterWordInsert[this.activeField] += 1;

                    const wbIndexField = this.wbField.findIndex(item => item === this.activeField);

                    if (wbIndexField >= 0) {
                        const wbField = this.$refs[`wbItem${wbIndexField}`][0];
                        wbField.pasteText(insertWithSpace(value));
                    } else {
                        const exclusionFields = ['title', 'description'];
                        const text = inputEl.value
                            ? `${inputEl.value}${formatValues[this.activeField](value)}`
                            : value;

                        if (exclusionFields.includes(this.activeField)) {
                            const nameField = ['title', 'descr'][
                                exclusionFields.indexOf(this.activeField)
                            ];

                            this.setCommonField({ field: nameField, value: text });
                        }
                        this.form.fields[this.activeField] = text;
                    }

                    setTimeout(() => {
                        inputEl.focus();
                        inputEl.selectionStart = inputEl.value.length;
                    }, 500);
                } catch (error) {
                    console.error(error);
                }
            },
            selectedActive(field) {
                this.activeField = field;
            },
            handleFieldFocus(field) {
                this.setActiveField(field);
            },
            handleFieldBlur() {
                return this.setActiveField(null);
            },
            handleFieldAutofill(activeField, newValue) {
                const targetField = this.getFieldForAutoFill(activeField, newValue);
                const fieldToScrollDown = this.$refs[activeField] || null;
                let valuesDivider = activeField === 'description' ? ', ' : ' ';
                let newValueLocal = newValue;

                if (!targetField) {
                    return false;
                }

                if (activeField === 'description' && !this.descriptionAutofilled) {
                    newValueLocal = `${newValueLocal[0].toUpperCase()}${newValueLocal.slice(1)}`;

                    if (this.marketplaceSlug === 'ozon') {
                        valuesDivider = '\r\n';
                    } else {
                        valuesDivider = ' ';
                    }

                    this.descriptionAutofilled = true;
                }

                this.setFieldValue(targetField, valuesDivider, newValueLocal).then(() => {
                    if (fieldToScrollDown) {
                        this.scrollToBottom(fieldToScrollDown);
                    }
                });
                // this.setActiveField(null);
                this.setActiveFieldNewValue(null);
            },
            async setFieldValue(targetField, valuesDivider, newValueLocal) {
                const value = this.form.fields[targetField];
                return new Promise(resolve => {
                    resolve(
                        (this.form.fields[targetField] = value + valuesDivider + newValueLocal)
                    );
                });
            },
            scrollToBottom(el) {
                setTimeout(() => {
                    el.$refs.input.scrollTop = el.$refs.input.scrollHeight;
                }, 100);
            },
            getFieldForAutoFill(field, value) {
                const sortedFiledNames = this.fieldsOrder[this.marketplaceSlug];
                const limits = this.charactersLimit[this.marketplaceSlug];
                const indexStart = sortedFiledNames.findIndex(el => el === field);

                for (let i = indexStart; i < sortedFiledNames.length; i++) {
                    if (this.checkFieldCapacity(sortedFiledNames[i], value, limits)) {
                        return sortedFiledNames[i];
                    }
                }

                return false;
            },
            checkFieldCapacity(name, newValue, limits) {
                if (limits[name] === 0) {
                    return true;
                }
                const currentValue = this.form.fields[name];
                return currentValue.length + newValue.length <= limits[name];
            },
            maxCountForField(v, field, fieldName) {
                const limit = this.charactersLimit[this.marketplaceSlug][field];
                if (!limit) {
                    return true;
                }
                return (
                    v.length <= limit ||
                    `${v.length} / ${limit}! В характеристике "${fieldName}" не осталось места для ключевого слова. Удалите введенное ранее значение или добавьте слово в другую характеристику`
                );
            },
        },
    };
</script>

<style lang="scss" module>
    .KeyWordsForm {
        & .field {
            &.fieldActive {
                & :global(fieldset) {
                    border: 2px $primary-500 solid;
                }
            }
        }
    }
</style>
