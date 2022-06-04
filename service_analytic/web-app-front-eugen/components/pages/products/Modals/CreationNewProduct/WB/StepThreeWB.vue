<template>
    <VForm ref="formAction" class="custom-form"></VForm>
</template>

<script>
    import formMixin from '~mixins/form.mixin';

    export default {
        name: 'StepThree',
        mixins: [formMixin],
        data() {
            return {
                radio: 0,
                form: {
                    fields: {},
                    rules: {},
                },
            };
        },
        watch: {
            invalid(val) {
                this.$emit('setValid', !val);
            },
        },
    };
</script>
