import { mapGetters, mapActions } from 'vuex';
/* eslint-disable no-extra-parens */
export default {
    data() {
        return {
            oldValues: {},
        };
    },
    computed: {
        ...mapGetters({
            product: 'product/GET_PRODUCT',
            changes: 'product/GET_CHANGES',
            restore: 'product/GET_RESTORE',
            getChangesNomenclature: 'product/getChangesNomenclature',
        }),
    },
    watch: {
        'restore.count'() {
            if (
                (this.getChangesNomenclature !== null &&
                    this.nomenclature &&
                    this.getChangesNomenclature !== this.nomenclature.index) ||
                (this.sizeIndex !== undefined &&
                    this.restore.size !== null &&
                    this.restore.size !== this.sizeIndex)
            ) {
                return false;
            }

            this.reloadField(this.restore.key);
        },
    },
    mounted() {
        if (!this.isNotCommonBehavior && this.values) {
            this.setFields();
        }
    },
    methods: {
        ...mapActions({
            setChangesNomenclature: 'product/setChangesNomenclature',
        }),
        reloadField(key) {
            const value = this.getValue(key);
            this.setValue(value, key);
        },
        setFields() {
            Object.keys(this.values).forEach(key => {
                const value = this.getValue(key);
                this.setValue(value, key);
            });
        },
        getValue(key) {
            const data = this.values[key];

            if (this.isNumeric && key !== 'nomenclature') {
                return Number(data);
            }
            if (this.isObjectValue && data) {
                return data.value;
            }

            return data;
        },
        setValue(value, key) {
            this.form.fields[key] = value;
            this.oldValues[key] = value;
        },
        onChange(key, title, id = null) {
            const getKey = id || key;
            const changes = [...this.changes];
            const value = this.oldValues[getKey] || '';
            const timestamp = Number(new Date());

            changes.unshift({ timestamp, key, title, value, prop: getKey });
            this.$store.commit('product/SET_CHANGES', changes);
            this.oldValues[getKey] = this.form.fields[getKey];
        },
    },
};
