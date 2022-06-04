<template>
    <div :class="$style.OrganizationData">
        <h2 :class="$style.heading">Введите реквизиты вашей компании</h2>
        <div v-if="userCompanies" :class="$style.inputsWrapper">
            <VSelect
                v-model="companySelected"
                :items="userCompanies"
                outlined
                dense
                class="light-outline"
                :item-text="'name'"
                :item-value="'inn'"
                :menu-props="{ nudgeBottom: 42 }"
                label="Сохраненные компании"
            />
        </div>
        <v-autocomplete
            v-model.trim="findField"
            label="Поиск организации (Название, ИНН, Адрес)"
            prepend-inner-icon="$search"
            :search-input.sync="search"
            :items="items"
            item-text="fieldSearch"
            return-object
            :loading="isLoading"
            :auto-select-first="false"
            hide-no-data
            clear
            outlined
            dense
            class="light-outline"
        >
            <template #selection="{ item }">
                <span v-text="item.name"></span>
            </template>
            <template #item="{ item }">
                <v-list-item-content>
                    <v-list-item-title v-text="item.name"></v-list-item-title>
                    <div
                        style="display: block; max-width: 240px; font-size: 12px; line-height: 140%"
                        class="mt-2"
                        :class="$style.subItem"
                    >
                        {{ item.fieldSearch }}
                    </div>
                </v-list-item-content>
            </template>
        </v-autocomplete>
        <div>
            <VTextField
                v-model="form.name.$model.value"
                :error-messages="form.name.$errorMessage.value"
                autocomplete="new-password"
                label="Наименование организации"
                dense
                class="light-outline"
                outlined
                @blur="form.name.$touch"
                @input="form.name.$resetExtError"
            />
            <VTextField
                v-model="form.inn.$model.value"
                :error-messages="form.inn.$errorMessage.value"
                autocomplete="new-password"
                label="ИНН"
                dense
                class="light-outline"
                outlined
                @blur="form.inn.$touch"
                @input="form.inn.$resetExtError"
            />
            <VTextField
                v-model="form.kpp.$model.value"
                :error-messages="form.kpp.$errorMessage.value"
                autocomplete="new-password"
                label="КПП"
                dense
                class="light-outline"
                outlined
                :menu-props="{ nudgeBottom: 42 }"
                @blur="form.kpp.$touch"
                @input="form.kpp.$resetExtError"
            />
        </div>
    </div>
</template>

<script>
    /* eslint-disable no-empty-function */
    import { mapActions, mapGetters } from 'vuex';

    import { defineComponent, reactive } from '@nuxtjs/composition-api';
    import { useForm } from '~use/form';
    import { useField } from '~use/field';
    import { minLength, minLengthIfAny, required } from '~utils/patterns';

    export default defineComponent({
        name: 'OrganizationData',
        setup() {
            const formFields = {
                name: {
                    validators: { required, minLength: minLength(2) },
                    errorMessages: {
                        required: 'Введите навазние',
                        minLength: 'Слишком короткое навазние',
                    },
                },
                inn: {
                    validators: { minLength: minLengthIfAny(10) },
                    errorMessages: {
                        required: 'Введите ИНН',
                        minLength: 'Слишком короткий ИНН',
                    },
                },
                kpp: {
                    validators: { minLength: minLengthIfAny(9) },
                    errorMessages: {
                        minLength: 'Слишком короткий КПП',
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
        data() {
            return {
                findField: undefined,
                uniqItems: [],
                search: '',
                companySelected: '',
                model: false,
                menuModel: false,
                isLoading: false,
                searchInn: '',
                items: [],
                selectedItem: null,
                notification: () => {},
            };
        },
        computed: {
            ...mapGetters({
                userCompanies: 'user/getCompanies',
            }),
        },
        watch: {
            findField(val) {
                if (!val) {
                    return;
                }
                this.fillCompanyForm(val);
            },
            async search(val) {
                await this.getItems(val);
            },
            userCompanies: {
                immediate: true,
                handler(val, oldVal) {
                    if (val[0]?.inn && !oldVal?.length) {
                        this.companySelected = val[0].inn;
                    }
                },
            },
            companySelected() {
                return this.handleCompanySelect();
            },
        },
        async created() {
            if (!this.userCompanies.length) {
                await this.fetchCompanies();
            }
        },
        mounted() {
            this.checkFields();
        },
        methods: {
            ...mapActions('user', {
                fetchCompanies: 'fetchCompanies',
            }),

            async getItems(query) {
                this.isLoading = true;
                const topic = '/suggestions/api/4_1/rs/suggest/party';
                const dadataToken = 'Token 05b130eabd9d493a7cc5bba774e055e12360a6e2';
                const headers = {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    Authorization: dadataToken,
                };
                try {
                    const {
                        data: { suggestions },
                    } = await this.$dadata.post(topic, { query, count: 20 }, { headers });

                    suggestions.forEach(item => {
                        const {
                            data: {
                                inn,
                                kpp,
                                address: { value: address },
                            },
                            value: name,
                        } = item;

                        if (!this.uniqItems.includes(inn)) {
                            this.items.push({
                                inn,
                                kpp,
                                address,
                                name,
                                fieldSearch: [name, inn, kpp, address].join(' • '),
                            });
                            this.uniqItems.push(inn);
                        }
                    });

                    this.isLoading = false;
                } catch (error) {
                    console.error(error);
                }
            },
            handlePick(payload, cb) {
                this.selectedItem = payload;
                // this.searchInn = payload.data.inn;
                // cb();
                this.fillCompanyForm({
                    name: payload.value,
                    inn: payload.data.inn,
                    kpp: payload.data.kpp,
                });

                setTimeout(() => {
                    this.menuModel = false;
                }, 100);
            },
            activateMenu() {
                this.$refs.menu.isActive = false;
                this.$refs.menu.isActive = true;
            },
            handleCompanySelect() {
                const company = this.userCompanies.find(el => el.inn === this.companySelected);
                if (company) {
                    this.fillCompanyForm(company);
                }
            },
            fillCompanyForm(company) {
                this.$model.name.value = company.name;
                this.$model.kpp.value = company.kpp;
                this.$model.inn.value = company.inn;
            },
            getCompanyData() {
                this.$touch();
                if (this.$invalid) {
                    return;
                }

                return {
                    name: this.form.name.$model.value,
                    inn: this.form.inn.$model.value,
                    kpp: this.form.kpp.$model.value,
                };
            },
            checkFields() {
                if (
                    (!this.$model.inn.value || !this.$model.kpp.value || !this.$model.name.value) &&
                    this.userCompanies.length
                ) {
                    this.handleCompanySelect();
                }
            },
        },
    });
</script>

<style lang="scss" module>
    .subItem {
        color: $base-800;
    }

    .OrganizationData {
        padding-top: 1rem;

        .heading {
            font-weight: normal;
            font-size: 12px;
            color: $base-700;
        }
    }

    .inputsWrapper {
        display: flex;
        flex-direction: column;
        padding-top: 16px;
        gap: 8px;
    }

    .menu {
        position: absolute;
    }

    .menuInner {
        background-color: $white;
    }

    .inputWrapper {
        position: relative;
    }
</style>
