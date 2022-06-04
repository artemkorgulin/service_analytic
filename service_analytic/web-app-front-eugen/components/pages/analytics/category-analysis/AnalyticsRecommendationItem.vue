<template>
    <div class="analytics-recommendation-item">
        <div class="analytics-recommendation-item__index">
            {{ index + 1 }}
        </div>
        <div class="analytics-recommendation-item__text">
            {{ text }}
        </div>
    </div>
</template>

<script>
    export default {
        name: 'AnalyticsRecommendationItem',
        props: {
            index: {
                type: Number,
                required: true,
            },
            text: {
                type: String,
                required: true,
            },
        },
    };
</script>

<style lang="scss" scoped>
    .analytics-recommendation-item {
        @include flex-grid-x;

        &__index {
            @include flex-center;

            flex-shrink: 0;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(234, 94, 127, 0.08);
            font-size: 14px;
            font-weight: bold;
            color: #fc6e90;
        }

        &__text {
            font-size: 16px;
            font-weight: normal;
            line-height: 1.5;
        }
    }
</style>
