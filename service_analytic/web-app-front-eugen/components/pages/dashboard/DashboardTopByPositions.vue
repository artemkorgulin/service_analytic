<template>
    <div class="dashboard-card" :class="$style.DashboardTopByPositions">
        <h3 :class="$style.heading" class="dashboard-card__header">ТОП-10 товаров по позициям</h3>
        <div :class="$style.content">
            <div v-if="items.length" :class="$style.grid" class="custom-scrollbar">
                <TopByPositionsItemHeader />
                <TopByPositionsItem v-for="(item, index) in items" :key="index" :item="item" />
            </div>
            <div v-else :class="$style.cover">
                <VImg
                    :class="$style.coverImage"
                    src="/images/error.svg"
                    contain
                    alt="нет уведомлений"
                />
                <div class="dashboard-card__header" :class="$style.coverText">
                    Добавьте товары, чтобы видеть топ
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'DashboardTopByPositions',
        props: {
            items: {
                type: Array,
                default: () => [],
            },
        },
    };
</script>

<style lang="scss" module>
    .DashboardTopByPositions {
        @include respond-to(md) {
            display: none;
        }
    }

    .heading {
        margin-bottom: 16px !important;
    }

    .content {
        position: relative;
        height: 100%;
    }

    .grid {
        display: grid;
        height: 210px;
        border-radius: 8px;
        border: 1px solid #c8cfd9;
        grid-template-columns: 1fr;
    }

    .buttons {
        display: grid;
        grid-gap: 0.5rem;
        grid-template-columns: repeat(2, 2.5rem);
        padding-top: 1rem;
    }

    .cover {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
        height: 100%;
        padding: 1rem;
    }

    .coverImage {
        max-width: 9.375rem;
        max-height: 8.733rem;
        filter: grayscale(1);
    }

    .coverText {
        margin-top: 0.65rem;
    }
</style>
