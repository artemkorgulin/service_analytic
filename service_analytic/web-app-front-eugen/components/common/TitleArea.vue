<template>
    <div class="page-app__top">
        <span class="title-h1 title-h1--bold">{{ title }}</span>
        <span v-if="subtitle" class="base-txt base-txt--light">{{ subtitle }}</span>
    </div>
</template>

<script>
    export default {
        name: 'TitleArea',
        props: {
            title: {
                type: String,
                required: true,
            },
            subtitle: {
                type: String,
                default: null,
            },
        },
    };
</script>

<style lang="scss">
    .title-h1 {
        margin-bottom: 8px;
    }
</style>
