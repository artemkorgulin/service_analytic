<template>
    <div class="product-table__messages border-hover">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
            <path
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M14.4 4.00002V10.4C14.4 10.8244 14.2314 11.2313 13.9313 11.5314C13.6313 11.8315 13.2243 12 12.8 12H8.79998L4.79998 15.2V12H3.19998C2.77563 12 2.36866 11.8315 2.0686 11.5314C1.76855 11.2313 1.59998 10.8244 1.59998 10.4V4.00002C1.59998 3.57568 1.76855 3.16871 2.0686 2.86865C2.36866 2.5686 2.77563 2.40002 3.19998 2.40002H12.8C13.2243 2.40002 13.6313 2.5686 13.9313 2.86865C14.2314 3.16871 14.4 3.57568 14.4 4.00002ZM5.59998 6.40002H3.99998V8.00002H5.59998V6.40002ZM7.19998 6.40002H8.79998V8.00002H7.19998V6.40002ZM12 6.40002H10.4V8.00002H12V6.40002Z"
            />
        </svg>
        <span class="small-txt small-txt--bold">{{ reviewsNumber }}</span>
    </div>
</template>

<script>
    export default {
        name: 'ProductsListReviews',
        props: {
            reviewsNumber: {
                type: [Number, String],
                required: true,
            },
        },
    };
</script>

<style lang="scss" module>
    //
</style>
