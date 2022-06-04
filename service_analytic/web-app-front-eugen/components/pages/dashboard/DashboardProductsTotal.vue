<template>
    <div class="dashboard-card" :class="$style.DashboardProductsTotal">
        <div :class="$style.contents">
            <h3 class="dashboard-card__header">Всего товаров</h3>
            <template v-if="number">
                <div class="dashboard-card__big-text">{{ number }}</div>
                <div class="dashboard-card__text" :class="$style.cardText">
                    Количество добавленных в отслеживание
                    <br />
                    товаров на этом аккаунте
                </div>
            </template>
            <template v-else>
                <VBtn color="accent" to="/products">Добавить товары</VBtn>
                <div class="dashboard-card__text" :class="$style.cardText">
                    Добавьте товары, чтобы получить
                    <br />
                    больше данных
                </div>
            </template>
        </div>
        <div :class="$style.imageWrapper">
            <VImg
                :class="$style.image"
                src="/images/products-empty.svg"
                contain
                alt="Добавить товары"
            />
        </div>
    </div>
</template>

<script>
    /* eslint-disable vue/require-prop-types */
    export default {
        name: 'DashboardProductsTotal',
        props: {
            number: {
                required: true,
            },
        },
    };
</script>

<style lang="scss" module>
    .DashboardProductsTotal {
        display: flex;
        justify-content: space-between;

        @include respond-to(md) {
            flex-wrap: wrap;
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
</style>
