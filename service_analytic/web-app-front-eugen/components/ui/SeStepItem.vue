<template>
    <div class="se-step-item">
        <div class="se-step-item__circle">
            <span class="se-step-item__num">
                {{ number }}
            </span>
        </div>
        <span class="se-step-item__title">
            {{ title }}
        </span>
    </div>
</template>

<script>
    export default {
        props: {
            number: {
                type: Number,
                default: 0,
            },
            title: {
                type: String,
                default: 'Title',
            },
        },
    };
</script>

<style lang="scss" scoped>
    .se-step-item {
        display: flex;
        align-items: center;
        gap: 16px;

        &__circle {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 43px;
            min-height: 43px;
            border-radius: 50%;
            background: $primary-500;
            font-size: 18px;
            color: #fff;
        }

        &__title {
            font-size: 20px;
            color: $primary-500;
        }
    }
</style>
