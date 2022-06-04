<template>
    <div
        class="dashboard-card"
        :class="[$style.DashboardAverageRating, { [$style.disabledCard]: disabled }]"
    >
        <div :class="$style.contents">
            <h3 class="dashboard-card__header">Средний рейтинг</h3>
            <div :class="$style.rating">
                <div class="dashboard-card__big-text">{{ normalizeNum(rating) }}</div>
                <VRating
                    :class="$style.ratingStars"
                    color="accent"
                    length="5"
                    :value="rating"
                    dense
                    readonly
                    size="35"
                />
            </div>
            <div class="dashboard-card__text" :class="$style.cardText">
                Средний рейтинг добавленных товаров
                <br />
                на основе отзывов покупателей
            </div>
        </div>
        <div :class="$style.imageWrapper">
            <VImg :class="$style.image" src="/images/products-request.svg" contain alt="Рейтинг" />
        </div>
    </div>
</template>

<script>
    /* eslint-disable vue/require-prop-types */
    export default {
        name: 'DashboardAverageRating',
        props: {
            rating: {
                required: true,
            },
            disabled: {
                type: Boolean,
                default: false,
            },
        },
        methods: {
            normalizeNum(num) {
                if (!num) {
                    return 0;
                }
                return String(num.toFixed(1)).replace(/\./, ',');
            },
        },
    };
</script>

<style lang="scss" module>
    .DashboardAverageRating {
        display: flex;
        justify-content: space-between;

        @include respond-to(md) {
            flex-wrap: wrap;
        }

        &.disabledCard {
            filter: grayscale(1);
        }
    }

    .contents {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        margin-right: 1.5rem;

        @include respond-to(md) {
            justify-content: flex-start;
            flex-grow: 1;
            margin-right: 0;
            margin-left: 1rem;
        }
    }

    .imageWrapper {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .image {
        $size: 120px;

        width: $size;
        max-width: $size;
        height: $size;
    }

    .rating {
        display: flex;
        align-items: center;
    }

    .ratingStars {
        margin-left: 8px;

        @include respond-to(md) {
            display: none;
        }
    }
</style>
