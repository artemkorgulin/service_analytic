<template>
    <VForm :class="$style.OrganizationEdit">
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
        </div>
        <div :class="$style.buttonsWrapper">
            <VBtn
                color="accent"
                :loading="isLoading"
                :disabled="!$changed || $invalid"
                @click="handleAction"
            >
                Сохранить
            </VBtn>
            <VBtn color="error" outlined :loading="isDeleteLoading" @click="handleDelete">
                Удалить
            </VBtn>
        </div>
    </VForm>
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
        name: 'OrganizationEdit',
        props: {
            item: {
                type: Object,
                default: () => ({}),
            },
        },
        setup({ item }) {
            const formFields = {
                name: {
                    value: item.name,
                    validators: { required, minLength: minLength(2) },
                    errorMessages: {
                        required: 'Введите навазние',
                        minLength: 'Слишком короткое навазние',
                    },
                },
                inn: {
                    value: item.inn,
                    validators: { required, minLength: minLength(2) },
                    errorMessages: {
                        required: 'Введите inn',
                        minLength: 'Слишком короткое inn',
                    },
                },
                kpp: {
                    value: item.kpp,
                    validators: { required, minLength: minLength(2) },
                    errorMessages: {
                        required: 'Введите kpp',
                        minLength: 'Слишком короткое kpp',
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
                isLoading: false,
                isDeleteLoading: false,
                notification: () => {},
            };
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
                    const response = await this.$axios.$put(`/api/v1/company/${this.item.id}`, {
                        name: this.form.name.$model.value,
                        inn: this.form.inn.$model.value,
                        kpp: this.form.kpp.$model.value,
                    });
                    this.isLoading = false;
                    this.notification = await this.$notify.create({
                        message: 'Настройки успешно сохранены',
                        type: 'positive',
                    });
                    console.log(
                        '🚀 ~ file: OrganizationEdit.vue ~ line 121 ~ handleAction ~ response',
                        response
                    );
                } catch (error) {
                    this.isLoading = false;
                    console.log(
                        '🚀 ~ file: OrganizationEdit.vue ~ line 127 ~ handleAction ~ err',
                        error
                    );
                    this.notification = await this.$notify.create({
                        message: getErrorMessage(error),
                        type: 'negative',
                    });
                }
            },
            async handleDelete() {
                try {
                    this.isDeleteLoading = true;
                    this.notification();
                    const response = await this.$axios.$delete(`/api/v1/company/${this.item.id}`);
                    this.fetchCompanies();
                    this.isDeleteLoading = false;
                    this.notification = await this.$notify.create({
                        message: 'Компания успешно удалена',
                        type: 'positive',
                    });
                    console.log(
                        '🚀 ~ file: OrganizationEdit.vue ~ line 146 ~ handleDelete ~ response',
                        response
                    );
                } catch (error) {
                    this.notification = await this.$notify.create({
                        message: 'Ошибка при удалении компании',
                        type: 'positive',
                    });
                    console.log(
                        '🚀 ~ file: OrganizationEdit.vue ~ line 147 ~ handleDelete ~ error',
                        error
                    );
                }
            },
        },
    });
</script>

<style lang="scss" module>
    .OrganizationEdit {
        display: flex;
        flex: 1;
        padding-top: 16px;
        // gap: 16px;
        flex-direction: column;
    }

    .inputsWrapper {
        display: flex;
        gap: 16px;
        // margin-bottom: 16px;
    }

    .buttonsWrapper {
        gap: 8px;
        display: flex;
    }
</style>
