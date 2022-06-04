<template>
    <VForm ref="formAction" class="custom-form">
        <VContainer>
            <VRow class="mt-0">
                <VCol cols="12" md="4" xs="12" class="pb-0">
                    <v-autocomplete
                        v-model="form.fields.category"
                        :items="searchResults.category"
                        :search-input.sync="form.query.category.val"
                        :loading="form.query.category.pending"
                        label="Тип товара"
                        outlined
                        dense
                        hide-no-data
                        color="#710bff"
                        item-text="name"
                        item-value="id"
                        @update:search-input="setValue($event, 'category')"
                        @change="setCategory"
                    />
                </VCol>
            </VRow>
        </VContainer>
    </VForm>
</template>

<script>
    import { mapGetters } from 'vuex';
    import formMixin from '~mixins/form.mixin';
    import { debounce } from '~utils/helper.utils';
    import { errorHandler } from '~utils/response.utils';

    export default {
        name: 'StepOne',
        mixins: [formMixin],
        data() {
            return {
                searchResults: {
                    category: [],
                },
                form: {
                    query: {
                        category: {
                            val: null,
                            pending: false,
                        },
                    },
                    fields: {
                        category: null,
                    },
                    rules: {},
                },
            };
        },
        computed: {
            ...mapGetters({
                paramsData: 'products/GET_NEW_PRODUCT',
            }),
        },
        watch: {
            invalid(val) {
                this.$emit('setValid', !val);
            },
        },
        methods: {
            setValue(val, key) {
                if (val && val.length > 2 && val !== this.form.fields[key]) {
                    debounce({
                        time: 500,
                        collBack: () => {
                            this.form.query[key].pending = true;
                            const endpoint =
                                key === 'category'
                                    ? '/api/vp/v2/wildberries/categories/'
                                    : '/api/vp/v2/wildberries/directories/' + key;
                            const params = {
                                search: val,
                            };

                            if (key === 'tved') {
                                params.object = this.form.fields.tved;
                            }

                            this.$axios
                                .$get(endpoint, { params })
                                .then(({ data }) => {
                                    this.searchResults[key] = data.map(item => {
                                        const newItem = item;
                                        const name = newItem.name
                                            ? newItem.name + ' (' + newItem.parent + ') '
                                            : item.title;

                                        return {
                                            id: newItem.id,
                                            name,
                                        };
                                    });
                                })
                                .catch(({ response }) => {
                                    errorHandler(response, this.$notify);
                                })
                                .finally(() => {
                                    this.form.query[key].pending = false;
                                });
                        },
                    });
                } else {
                    this.form.query[key].pending = false;
                }
            },
            setCategory() {
                if (this.form.fields.category) {
                    this.$store.commit(
                        'products/SET_NEW_PRODUCT_CATEGORY',
                        this.form.fields.category
                    );
                    this.$store.dispatch('products/LOAD_NEW_PRODUCT_PARAMS', 'wildberries');
                }
            },
        },
    };
</script>
