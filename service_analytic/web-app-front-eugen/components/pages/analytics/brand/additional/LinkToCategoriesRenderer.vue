<template>
    <NuxtLink style="color: #7e21ff" :to="{ path: path, query: query }">
        {{ cellValue }}
    </NuxtLink>
</template>

<script>
    import { mapActions } from 'vuex';

    export default {
        name: 'LinkToCategoriesRenderer',
        data() {
            return {
                catToCat: false,
                query: {},
                path: '/analytics/categories',
            };
        },
        computed: {
            cellValue() {
                if (this.params) {
                    return this.params.valueFormatted
                        ? this.params.valueFormatted
                        : this.params.value;
                }
                return null;
            },
            webIdValue() {
                if (this.params?.data) {
                    return this.params.data.web_id;
                }
                return null;
            },
        },
        mounted() {
            if (this.params.colDef?.to === 'brand') {
                this.path = '/analytics/brand';
                this.query = { brand: this.cellValue };
                return;
            }
            /* eslint-disable */
            this.catToCat = this.params.colDef?.from === 'cat';
            this.query = { category: this.cellValue };
            this.catToCat && (this.query.fc = 1);
        },
        methods: {
            ...mapActions('categories-analitik', ['searchAndOpenCategory']),
            async invokePassToCategories() {
                if (this.webIdValue) {
                    await this.searchAndOpenCategory(this.webIdValue);
                }
            },
        },
    };
</script>
