<template>
    <a :href="url" target="_blank" class="product-table__code-product">
        <span class="small-txt small-txt--medium">{{ sku }}</span>
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor">
            <path
                d="M6.66663 3.99996H3.99996C3.64634 3.99996 3.3072 4.14043 3.05715 4.39048C2.8071 4.64053 2.66663 4.97967 2.66663 5.33329V12C2.66663 12.3536 2.8071 12.6927 3.05715 12.9428C3.3072 13.1928 3.64634 13.3333 3.99996 13.3333H10.6666C11.0202 13.3333 11.3594 13.1928 11.6094 12.9428C11.8595 12.6927 12 12.3536 12 12V9.33329M9.33329 2.66663H13.3333M13.3333 2.66663V6.66663M13.3333 2.66663L6.66663 9.33329"
                stroke-width="1.5"
                stroke-linecap="round"
                stroke-linejoin="round"
            />
        </svg>
    </a>
</template>

<script>
    export default {
        name: 'ProductsListUrl',
        props: {
            url: {
                type: undefined,
                required: true,
            },
            sku: {
                type: undefined,
                required: true,
            },
        },
    };
</script>

<style lang="scss" module>
    //
</style>
