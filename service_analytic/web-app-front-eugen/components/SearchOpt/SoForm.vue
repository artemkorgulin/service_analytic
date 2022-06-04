<template>
    <div class="so-form">
        <div class="se-form__item" :class="`se-form__item_${'active'}`"></div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';
    export default {
        data() {
            return {
                wbTmpl: [{ field: '', label: '', type: 'v-text-field', model: '' }],
                ozonTmpl: [{ field: 'name', label: 'Название', type: 'v-text-field', model: '' }],
                selectedTmpl: undefined,
            };
        },
        // Задача, чтобы был конструктор формы
        computed: {
            ...mapGetters(['isSelectedMp']),
        },
        mounted() {
            if (this.isSelectedMp.id === 1) {
                this.selectedTmpl = this.ozonTmpl;
            } else if (this.isSelectedMp.id === 2) {
                this.selectedTmpl = this.WbTmpl;
            }
        },
    };
</script>

<style lang="scss" scoped>
    .so-form {
        overflow: hidden;
    }
</style>
