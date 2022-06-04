<template>
    <BaseDrawer v-model="isShow" width="600px">
        <TheMarketplaceSettings :sel-m-p="selMP" />
    </BaseDrawer>
</template>

<script>
    /* eslint-disable no-empty-function */
    import { mapGetters } from 'vuex';
    import { defineComponent, reactive } from '@nuxtjs/composition-api';

    import { useForm } from '~use/form';
    import { useField } from '~use/field';
    import { minLength, required } from '~utils/patterns';

    export default defineComponent({
        name: 'ModalTheMarketplaceSettings',
        props: {
            selMP: String,
        },
        setup() {
            const formFields = {
                clientId: {
                    validators: { required },
                    errorMessages: {
                        required: 'Введите clientId',
                    },
                },
                apiKey: {
                    validators: { required, minLength: minLength(8) },
                    errorMessages: {
                        required: 'Введите apiKey',
                        minLength: 'Слишком короткий apiKey',
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
                isShow: true,
                selectedMarketplace: null,
                notification: () => {},
            };
        },

        computed: {
            ...mapGetters({
                marketplaces: 'getMarketplaces',
            }),
            marketplacesAll() {
                return this.marketplaces.reduce((acc, val) => {
                    acc[val.key] = val;
                    return acc;
                }, {});
            },
        },
        mounted() {
            if (this.selMP) {
                this.handleChangeMarketplace(this.marketplacesAll[this.selMP]);
            }
        },
        methods: {
            handleChangeMarketplace(item) {
                this.selectedMarketplace = item.key;
            },
        },
    });
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .TheMarketplaceSettings {
        overflow: auto;
        max-height: 100%;
        padding: 24px;
    }

    .heading {
        font-size: 26px;
        font-weight: 600;
        color: $base-900;
    }

    .wrapper {
        max-height: calc(100% - 40px) !important;
        margin-top: 0;
        gap: 16px;
    }
</style>
