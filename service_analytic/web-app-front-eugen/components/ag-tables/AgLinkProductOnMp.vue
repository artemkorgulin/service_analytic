<template>
    <div class="ag-product-link">
        <a :href="params.value.url" target="_blank" class="def-link">{{ params.value.id }}</a>
    </div>
</template>

<style lang="scss" scoped>
    .def-link {
        font-weight: 500;
        color: $primary-color;

        &:hover {
            opacity: 0.8;
        }
    }
</style>
