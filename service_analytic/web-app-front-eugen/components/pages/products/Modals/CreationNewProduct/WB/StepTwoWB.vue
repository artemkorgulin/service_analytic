<template>
    <VForm ref="formAction" class="custom-form"></VForm>
</template>

<script>
    import formMixin from '~mixins/form.mixin';

    export default {
        name: 'StepTwo',
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
        watch: {
            invalid(val) {
                this.$emit('setValid', !val);
            },
        },
    };
</script>
