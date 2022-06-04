<template>
    <VForm ref="formAction" class="custom-form">
        <div class="new-product-options">
            <div v-for="field in characteristics" :key="field.id" :class="fieldClass(field)">
                <template v-if="field.type === 'multiline'">
                    <VTextarea
                        v-model="form.fields[field.id]"
                        :rules="form.rules[field.id]"
                        :label="field.name"
                        rows="4"
                        outlined
                        dense
                        no-resize
                        color="#710bff"
                    />
                </template>
                <template v-else-if="field.is_reference">
                    <select-remote-search
                        v-model="form.fields[field.id]"
                        :items="field.options"
                        :rules="form.rules[field.id]"
                        :label="field.name"
                        :field-id="String(field.id)"
                    />
                </template>
                <template v-else-if="field.type === 'String'">
                    <VTextField
                        v-model="form.fields[field.id]"
                        :rules="form.rules[field.id]"
                        :label="field.name"
                        outlined
                        dense
                        color="#710bff"
                    />
                </template>
                <template v-else-if="field.type === 'Boolean'">
                    <v-checkbox
                        v-model="form.fields[field.id]"
                        label="Да"
                        color="#710bff"
                        hide-details
                    />
                </template>
                <template v-else>
                    <VTextField
                        v-model.number="form.fields[field.id]"
                        :rules="form.rules[field.id]"
                        :label="field.name"
                        outlined
                        dense
                        color="#710bff"
                    />
                </template>
            </div>
        </div>
    </VForm>
</template>

<script>
    import { mapGetters } from 'vuex';
    import formMixin from '~mixins/form.mixin';
    import { debounce } from '~utils/helper.utils';
    import { errorHandler } from '~utils/response.utils';

    export default {
        name: 'StepTwoOzon',
        mixins: [formMixin],
        data() {
            return {
                form: {
                    fields: {},
                    rules: {},
                },
                searchResult: {},
                loadingSearch: null,
            };
        },
        computed: {
            ...mapGetters({
                paramsData: 'products/GET_NEW_PRODUCT',
            }),
            characteristics() {
                const params = [...this.paramsData.params.characteristics];

                return params.sort((a, b) => {
                    const rating = {
                        a: a.type === 'multiline' ? 0 : 1,
                        b: b.type === 'multiline' ? 0 : 1,
                    };

                    return rating.b - rating.a;
                });
            },
        },
        watch: {
            characteristics() {
                this.setFields();
            },
            invalid(val) {
                this.$emit('setValid', !val);
            },
        },
        mounted() {
            this.setFields();
        },
        methods: {
            setFields() {
                const fields = {};

                this.characteristics.forEach(item => {
                    const defaultVal = null;
                    const key = String(item.id);
                    const rules = [];

                    if (item.is_required) {
                        rules.push(val => Boolean(val) || 'пожалуйста, заполните это поле');
                    }

                    if (!item.is_reference) {
                        switch (item.type) {
                            case 'Integer':
                            case 'Decimal':
                            case 'Double':
                                rules.push(val => typeof val === 'number' || 'введите число');
                                break;
                            case 'Boolean':
                                break;
                            case 'URL':
                            case 'ImageURL':
                                rules.push(val => {
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
                    }

                    fields[key] = defaultVal;

                    this.$set(this.form.rules, key, rules);
                });

                this.$set(this.form, 'fields', fields);
            },
            fieldClass(field) {
                if (field.type === 'multiline') {
                    return 'new-product-options__full';
                }
                return 'new-product-options__grid';
            },
            searchParams(query) {
                if (query.length > 2) {
                    debounce({
                        time: 500,
                        collBack: () => {
                            this.loadingSearch = this.isFocus;
                            this.$axios
                                .$get('/api/vp/v2/oz/directories/' + this.isFocus, {
                                    params: {
                                        search: query,
                                    },
                                })
                                .then(({ data }) => {
                                    this.$set(
                                        this.searchResult,
                                        this.isFocus,
                                        data.map(item => {
                                            const key = Object.keys(item)[0];

                                            return {
                                                id: key,
                                                value: item[key],
                                            };
                                        })
                                    );
                                })
                                .catch(({ response }) => {
                                    errorHandler(response, this.$notify);
                                })
                                .finally(() => {
                                    this.loadingSearch = null;
                                });
                        },
                    });
                } else {
                    this.$set(this.searchResult, this.isFocus, null);
                    this.loadingSearch = null;
                }
            },
            getOptions(id) {
                return this.searchResult[id];
            },
        },
    };
</script>

<style lang="scss">
    .new-product-options {
        display: flex;
        flex-wrap: wrap;

        &__grid {
            width: calc(50% - 16px);
            margin: 0 8px;

            @include phone-large {
                width: calc(100% - 16px);
                margin: 0 auto;
            }
        }

        &__full {
            width: calc(100% - 16px);
            margin: 0 auto;
        }
    }
</style>
