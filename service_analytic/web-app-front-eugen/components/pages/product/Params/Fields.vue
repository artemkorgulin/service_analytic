<template>
    <VForm ref="formAction" class="custom-form pl-3 pr-1">
        <div
            v-for="(field, index) in values"
            :key="`field-${index}-${field.name}-${field.id}`"
            class="mb-3 product-options__input-full"
        >
            <template v-if="field.type === 'multiline'">
                <VTextarea
                    v-model="form.fields[field.id]"
                    :class="getColorClass(form.fields[field.id])"
                    :rules="form.rules[field.id]"
                    :label="field.name"
                    rows="1"
                    auto-grow
                    no-resize
                    color="#710bff"
                    class="light-inline mt-0 pt-0"
                    @change="
                        onChange(
                            `${field.key}.${field.index}.value`,
                            'Обновлена характеристика «' + field.name + '»',
                            field.id
                        )
                    "
                >
                    <template #append-outer>
                        <div :class="$style.Label">
                            <IconTooltip
                                v-if="isSelectedMp.id !== 2"
                                :message="getTooltipMessage(field)"
                                margin-right
                                :icon-color="colors.tooltipIconColor"
                            />
                        </div>
                    </template>
                </VTextarea>
            </template>
            <template v-else-if="field.selected_options">
                <select-remote-search
                    v-model="form.fields[field.id]"
                    :class="getColorClass(form.fields[field.id])"
                    :items="field.options"
                    :rules="form.rules[field.id]"
                    :label="field.name"
                    :field-id="field.id"
                    :dictionary-slug="field.dictionarySlug"
                    :only-dictionary="field.onlyDictionary"
                    :max-selected="field.maxCount"
                    :dictionary-search-object="field.object"
                    :dictionary-search-type="field.dictionaryType"
                    :category="field.category"
                    :is-boolean="field.isBoolean"
                    auto-row
                    no-outlined
                    no-dense
                    has-tooltip
                    @change="
                        onChange(
                            `${field.key}.${field.index}.value`,
                            'Обновлена характеристика «' + field.name + '»',
                            field.id
                        )
                    "
                >
                    <template #append-outer>
                        <div :class="$style.SelectLabel">
                            <IconTooltip
                                v-if="isSelectedMp.id !== 2"
                                :message="getTooltipMessage(field)"
                                margin-right
                                :icon-color="colors.tooltipIconColor"
                            />
                        </div>
                    </template>
                </select-remote-search>
            </template>
            <template v-else-if="field.type === 'String'">
                <VTextField
                    v-model="form.fields[field.id]"
                    :class="getColorClass(form.fields[field.id])"
                    :rules="form.rules[field.id]"
                    :label="field.name"
                    color="#710bff"
                    class="light-inline mt-0 pt-0"
                    @change="
                        onChange(
                            `${field.key}.${field.index}.value`,
                            'Обновлена характеристика «' + field.name + '»',
                            field.id
                        )
                    "
                >
                    <template #append-outer>
                        <div :class="$style.Label">
                            <IconTooltip
                                v-if="isSelectedMp.id !== 2"
                                :message="getTooltipMessage(field)"
                                margin-right
                                :icon-color="colors.tooltipIconColor"
                            />
                        </div>
                    </template>
                </VTextField>
            </template>
            <template v-else-if="field.type === 'Boolean'">
                <v-checkbox
                    v-model="form.fields[field.id]"
                    :label="field.name"
                    color="#710bff"
                    dense
                    hide-details
                    class="p-0 mt-0 pt-0 mb-7"
                    style="margin-left: -6px"
                    @change="
                        onChange(
                            `${field.key}.${field.index}.value`,
                            'Обновлена характеристика «' + field.name + '»',
                            field.id
                        )
                    "
                />
            </template>
            <template v-else>
                <VTextField
                    v-model.number="form.fields[field.id]"
                    :class="getColorClass(form.fields[field.id])"
                    :rules="form.rules[field.id]"
                    :label="field.name"
                    class="light-inline mt-0 pt-0"
                    color="#710bff"
                    @change="
                        onChange(
                            `${field.key}.${field.index}.value`,
                            'Обновлена характеристика «' + field.name + '»',
                            field.id
                        )
                    "
                >
                    <template #append-outer>
                        <div :class="$style.Label">
                            <IconTooltip
                                v-if="isSelectedMp.id !== 2"
                                :message="getTooltipMessage(field)"
                                margin-right
                                :icon-color="colors.tooltipIconColor"
                            />
                        </div>
                    </template>
                </VTextField>
            </template>
            <div
                v-if="field.name === 'Комплектация' && isSelectedMp.id === 2"
                class="field-description d-flex"
                :class="{ 'mt-2': !showHint }"
            >
                <SvgIcon
                    :name="'filled/info'"
                    class="mr-1"
                    color="#710bff"
                    style="min-width: 12px; min-height: 12px"
                />
                <div>
                    Обязательно заполните Комплектацию по шаблону:
                    <strong>Предмет + Количество в упаковке, шт.</strong>
                    , чтобы избежать ошибок при приёмке товара. Например: Ножницы 1 шт.
                </div>
            </div>
        </div>
    </VForm>
</template>

<script>
    /* eslint-disable no-extra-parens */

    import { mapMutations, mapGetters } from 'vuex';
    import formMixin from '~mixins/form.mixin';
    import productMixin from '~mixins/product.mixin';
    import SelectRemoteSearch from '~/components/common/SelectRemoteSearch.vue';

    export default {
        name: 'Fields',
        components: { SelectRemoteSearch },
        mixins: [formMixin, productMixin],
        props: {
            noFilled: Boolean,
            values: {
                required: true,
                type: Object,
                default: null,
            },
            seekAttention: {
                type: Boolean,
                default: false,
            },
        },
        data() {
            return {
                inputsList: {},
                showHint: true,
                isNotCommonBehavior: true,
                isObjectValue: true,
                form: {
                    fields: {},
                    rules: {},
                },
                colors: {
                    tooltipIconColor: '#a6afbd',
                },
                tooltipForbiddenStrings: ['Подробнее по ссылке'],
            };
        },
        fetch() {
            this.getValues();
        },
        computed: {
            ...mapGetters('product', ['getProduct', 'resetChar']),
            ...mapGetters(['isSelectedMp']),
            ...mapGetters({
                marketplaceSlug: 'getSelectedMarketplaceSlug',
            }),
        },

        watch: {
            'form.fields': {
                deep: true,
                handler(value) {
                    /* eslint-disable */
                    // console.log(value);
                    if (this.inputsList['Комплектация']) {
                        const complEl = this.inputsList['Комплектация'];
                        setTimeout(() => {
                            this.showHint = !Boolean(complEl.querySelector('.error--text'));
                        }, 100);
                    }
                    const { searchOptimizationFields = [] } = this.getProduct;
                    if (this.isSelectedMp.id !== 2 && !searchOptimizationFields.length) return;

                    try {
                        Object.keys(value).forEach(key => {
                            if (searchOptimizationFields.includes(key)) {
                                const params = Array.isArray(value[key])
                                    ? value[key].map(word => ({ value: word.trim() }))
                                    : [{ value: value[key] }];
                                this.setWbAddin({ field: key, value: params });
                            }
                        });
                    } catch (error) {
                        console.log(error);
                    }
                },
            },
            values: {
                deep: true,
                handler() {
                    this.getValues();
                },
            },
        },
        mounted() {
            const inputsList = Array.from(this.$el.getElementsByClassName('v-input'));
            const elementsData = {};

            inputsList.forEach(el => {
                const label = el.querySelector('label');
                elementsData[label.textContent] = el;
            });

            this.inputsList = elementsData;
        },
        methods: {
            ...mapMutations('product', ['setWbAddin']),
            fieldClass(field) {
                return 'product-options__input-full';
            },
            async getInputs() {
                const check = await this.$refs.formAction.validate();
                if (!check) this.notifyUnvalidatedFields(this.$refs.formAction.inputs);
                return check ? { ...this.form.fields } : false;
            },
            getOutlineColor(value) {
                if (this.seekAttention && !value.length) {
                    return '#ff3981';
                } else {
                    return '#710bff';
                }
            },
            getColorClass(val) {
                if (this.seekAttention && (!val || (typeof val !== 'number' && !val.length))) {
                    return this.$style.BorderRed;
                } else {
                    return false;
                }
            },
            getValues() {
                Object.values(this.values).forEach(field => {
                    const key = String(field.id);
                    const validator = [];

                    if (field.is_required) {
                        validator.push(val => {
                            const condition = Array.isArray(val) ? Boolean(val.length) : val !== '';
                            return condition || 'Заполните это поле';
                        });
                    }

                    if (!field.selected_options) {
                        let value = field.value;

                        switch (field.type) {
                            case 'Integer':
                            case 'Decimal':
                            case 'Double':
                                validator.push(
                                    val =>
                                        Boolean(Number(val)) || Number(val) === 0 || 'введите число'
                                );

                                value = Number(value);
                                break;
                            case 'Boolean':
                                value = Boolean(value);
                                break;
                            case 'URL':
                            case 'ImageURL':
                                validator.push(val => {
                                    if (val) {
                                        const RegExp =
                                            /^((ftp|http|https):\/\/)?(www\.)?([A-Za-zА-Яа-я0-9]{1}[A-Za-zА-Яа-я0-9\\-]*\.?)*\.{1}[A-Za-zА-Яа-я0-9-]{2,8}(\/([\w#!:.?+=&%@!\-\\/])*)?/;

                                        if (RegExp.test(val)) {
                                            return true;
                                        } else {
                                            return 'значение должно быть ссылкой';
                                        }
                                    } else {
                                        return true;
                                    }
                                });
                                break;
                        }

                        this.$set(this.form.fields, key, value);
                        this.$set(this.oldValues, key, value);
                    } else {
                        const values = field.selected_options.map(option => option.id);

                        this.$set(this.form.fields, key, values);
                        this.$set(this.oldValues, key, values);
                    }

                    this.$set(this.form.rules, key, validator);
                });
            },
            getTooltipMessage(data) {
                if (this.marketplaceSlug === 'ozon' && data.description) {
                    return this.checkTooltipMessage(data.description);
                } else if (data.name) {
                    return this.checkTooltipMessage(data.name);
                } else {
                    return '';
                }
            },
            checkTooltipMessage(message) {
                let _message = message;
                this.tooltipForbiddenStrings.forEach(el => {
                    if (message.includes(el)) {
                        _message = _message.replace(el, '');
                    }
                });
                return _message;
            },
        },
    };
</script>

<style lang="scss" scoped>
    .field-description {
        margin-top: -16px;
        margin-bottom: 18px;
        font-size: 12px;
        color: $base-800;
    }
</style>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .BorderRed {
        &:global(:not(.v-input--is-focused)) {
            &:global(.v-text-field--outlined fieldset) {
                border-color: $color-pink-dark !important;
            }
        }

        & :global(:not(.v-input--is-focused)) {
            &:global(.v-text-field--outlined fieldset) {
                border-color: $color-pink-dark !important;
            }
        }
    }

    .SelectLabel {
        margin: 16px 0 4px 9px;
    }
</style>

<style lang="scss">
    .custom-form .v-input__append-outer {
        width: 25px;
    }
</style>
