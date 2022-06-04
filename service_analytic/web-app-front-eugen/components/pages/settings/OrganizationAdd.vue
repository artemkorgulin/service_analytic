<template>
    <div :class="$style.OrganizationAdd">
        <div v-if="false" id="innInputWrapper" :class="$style.inputWrapper">
            <VTextField
                id="inninput"
                ref="input"
                v-model="searchInn"
                prepend-inner-icon="$search"
                :loading="isLoading"
                label="Поиск"
                clearable
            />
            <VMenuTransition>
                <VMenu
                    v-if="items.length"
                    ref="menu"
                    v-model="menuModel"
                    :class="$style.menu"
                    activator="#inninput"
                    nudge-bottom="12"
                    offset-y
                    content-class="autocomplete-menu"
                >
                    <VItemGroup :class="$style.menuInner">
                        <VItem
                            v-for="(item, index) in items"
                            v-slot="{ active, toggle }"
                            :key="item.value + index"
                            style="padding: 5px"
                        >
                            <div :class="active ? 'accent' : ''" @click="handlePick(item, toggle)">
                                <span>{{ item.data.inn }}</span>
                                &nbsp;
                                <span>{{ item.value }}</span>
                            </div>
                        </VItem>
                    </VItemGroup>
                </VMenu>
            </VMenuTransition>
        </div>
        <div :class="$style.inputsWrapper">
            <VTextField
                v-model="form.name.$model.value"
                :error-messages="form.name.$errorMessage.value"
                autocomplete="new-password"
                label="Наименование организации"
                dense
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
                outlined
                @blur="form.kpp.$touch"
                @input="form.kpp.$resetExtError"
            />
            <!-- v-if="selectedItem" -->
            <!-- <VTextField readonly :value="selectedItem.data.name.full" label="Название" />
            <VTextField readonly :value="selectedItem.data.inn" label="ИНН" />
            <VTextField readonly :value="selectedItem.data.kpp" label="КПП" /> -->
            <!-- <div>{{ selectedItem.data.name.full }}</div>
            <pre>{{ selectedItem }}</pre> -->
        </div>
        <VBtn color="accent" :disabled="!$changed || $invalid" @click="handleAction">
            Сохранить
        </VBtn>
    </div>
</template>

<script>
    /* eslint-disable no-empty-function */
    import { mapActions } from 'vuex';

    import { defineComponent, reactive } from '@nuxtjs/composition-api';
    import { useForm } from '~use/form';
    import { useField } from '~use/field';
    import { minLength, required } from '~utils/patterns';
    import { getErrorMessage } from '~utils/error';

    export default defineComponent({
        name: 'OrganizationAdd',
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
                    validators: { required, minLength: minLength(10) },
                    errorMessages: {
                        required: 'Введите ИНН',
                        minLength: 'Слишком короткий ИНН',
                    },
                },
                kpp: {
                    validators: { required, minLength: minLength(9) },
                    errorMessages: {
                        required: 'Введите КПП',
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
            itemsComputed() {
                return this.items.map(item => ({
                    value: item.value,
                    text: item.value,
                }));
            },
        },
        watch: {
            searchInn(payload) {
                return this.getItems(payload, 'party');
            },
        },
        methods: {
            ...mapActions('user', {
                fetchCompanies: 'fetchCompanies',
            }),
            async handleAction() {
                try {
                    this.$touch();
                    if (this.$invalid) {
                        return;
                    }
                    this.notification();
                    this.isLoading = true;
                    const response = await this.$axios.$post('/api/v1/company', {
                        name: this.form.name.$model.value,
                        inn: this.form.inn.$model.value,
                        kpp: this.form.kpp.$model.value,
                    });
                    this.isLoading = false;
                    this.notification = await this.$notify.create({
                        message: 'Компания успешно добавлена',
                        type: 'positive',
                    });
                    await this.fetchCompanies();
                    console.log(
                        '🚀 ~ file: OrganizationEdit.vue ~ line 121 ~ handleAction ~ response',
                        response
                    );
                } catch (error) {
                    this.isLoading = false;
                    const errorArr = error?.response?.data?.error?.advanced;
                    if (errorArr?.length) {
                        const errorObject = Object.fromEntries(
                            errorArr.map(item => Object.entries(item)).flat()
                        );
                        this.$setExtErrors(errorObject);
                        console.log(
                            '🚀 ~ file: OrganizationAdd.vue ~ line 181 ~ handleAction ~ errorArr.map(item => Object.entries(errorArr))',
                            errorArr.map(item => Object.entries(item))
                        );

                        //  errorArr.reduce((acc, val) => {
                        //     console.log(
                        //         '🚀 ~ file: OrganizationAdd.vue ~ line 190 ~ errorObject ~ val',
                        //         Object.entries(val),
                        //         val
                        //     );
                        //     // acc[]
                        //     return acc;
                        // }, {});
                        console.log(
                            '🚀 ~ file: OrganizationAdd.vue ~ line 197 ~ errorObject ~ errorObject',
                            errorObject
                        );
                    }
                    // console.log(
                    //     '🚀 ~ file: OrganizationAdd.vue ~ line 188 ~ handleAction ~ errorObj',
                    //     errorObj
                    // );
                    // console.log(
                    //     '🚀 ~ file: OrganizationEdit.vue ~ line 127 ~ handleAction ~ err',
                    //     getErrors(errorObj)
                    // );
                    this.notification = await this.$notify.create({
                        message: getErrorMessage(error),
                        type: 'negative',
                    });
                }
            },
            getItems(payload, field) {
                this.isLoading = true;
                return this.$dadata
                    .$post(
                        `/suggestions/api/4_1/rs/suggest/${field}`,
                        {
                            query: JSON.stringify(payload),
                        },
                        {
                            headers: {
                                'Content-Type': 'application/json',
                                Accept: 'application/json',
                                Authorization: 'Token 05b130eabd9d493a7cc5bba774e055e12360a6e2',
                            },
                        }
                    )
                    .then(res => {
                        this.isLoading = false;
                        if (!res.suggestions) {
                            this.items = [];
                        }
                        console.log('🚀 ~ file: dadata.vue ~ line 52 ~ fetchData ~ res', res);
                        this.items = res.suggestions;
                        this.menuModel = true;
                    })
                    .catch(err => {
                        this.isLoading = false;
                        this.items = [];
                        console.log('🚀 ~ file: dadata.vue ~ line 55 ~ fetchData ~ err', err);
                    });
            },
            handlePick(payload, cb) {
                console.log('🚀 ~ file: dadata.vue ~ line 172 ~ handlePick ~ payload', payload);
                this.selectedItem = payload;
                this.searchInn = payload.data.inn;
                cb();
            },
            activateMenu() {
                this.$refs.menu.isActive = false;
                this.$refs.menu.isActive = true;
            },
        },
    });
</script>

<style lang="scss" module>
    .OrganizationAdd {
        //
    }

    .inputsWrapper {
        display: flex;
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
