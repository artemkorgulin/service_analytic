<template>
    <div class="blank-cover">
        <div class="blank-cover__inner-text">
            <VImg max-width="185" :src="image" contain />
            <p class="mt-7">
                {{ text }}
            </p>
            <VBtn v-if="hasButton" color="primary" class="mt-7" link :to="button.link">
                {{ button.text }}
            </VBtn>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'BlankCover',
        props: {
            text: { type: String },
            image: { type: String },
            hasButton: {
                type: Boolean,
                default: false,
            },
            button: { type: Object },
        },
    };
</script>

<style lang="scss" scoped>
    .blank-cover {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;

        &__inner-text {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: auto;
            max-width: 305px;
            text-align: center;
            font-weight: normal;
            font-size: 16px;
            line-height: 24px;
        }
    }
</style>
