import { mapGetters } from 'vuex';

export default {
    data() {
        return {
            isFocus: null,
        };
    },
    mounted() {
        this.setForm();
    },
    watch: {
        // eslint-disable-next-line func-names
        'formData.data'() {
            this.setForm();
        },
    },
    computed: {
        ...mapGetters({
            marketplaceSlug: 'getSelectedMarketplaceSlug',
        }),
        invalid() {
            return false;
            // if (this.form.rules && Object.keys(this.form.rules).length) {
            //     const check = Object.keys(this.form.rules).filter(key => (this.form.rules[key] && this.form.rules[key][0].required) && !this.form.fields[key]);
            //
            //     return Boolean(check.length);
            // } else {
            //     return false;
            // }
        },
    },
    methods: {
        setForm() {
            if (this.formData) {
                const formData = this.formData.data;

                if (formData) {
                    Object.keys(this.form.fields).forEach(key => {
                        this.form.fields[key] =
                            typeof formData[key] === 'undefined' ? null : formData[key];
                    });
                }
            }
        },
        async getInputs() {
            const check = await this.$refs.formAction.validate();
            if (check) {
                return { ...this.form.fields };
            } else {
                if (this.marketplaceSlug === 'wildberries') {
                    this.notifyUnvalidatedFields(this.$refs.formAction.inputs);
                }

                return false;
            }
        },
        notifyUnvalidatedFields(inputs) {
            const errors = inputs.filter(el => el.hasError);
            errors.forEach(err => {
                if (err.validations[0]) {
                    this.$notify.create({
                        message: `${err.label}: ${err.validations[0]}`,
                        type: 'negative',
                    });
                }
            });
        },
    },
};
