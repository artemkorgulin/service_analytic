<template>
    <div class="dashboard-card" :class="$style.DashboardNotifications">
        <div :class="$style.cardHeader">
            <h3 class="dashboard-card__header">Уведомления</h3>
            <div v-show="notifications.length" :class="$style.notificationNumber">16</div>
        </div>
        <div :class="$style.content">
            <div v-show="notifications.length" class="custom-scrollbar" :class="$style.gridWrapper">
                <div :class="$style.grid">
                    <NotificationBlock
                        v-for="(item, index) in notifications"
                        :key="index"
                        :text="item.message"
                    />
                    <!--:status="item.status":link="item.link" :img="item.img" -->
                </div>
            </div>
            <div v-show="!notifications.length" :class="$style.cover">
                <VImg
                    :class="$style.coverImage"
                    src="/images/error.svg"
                    contain
                    alt="нет уведомлений"
                />
                <div class="dashboard-card__header" :class="$style.coverText">
                    У вас нет уведомлений
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'DashboardNotifications',
        props: {
            items: {
                type: Array,
                default: () => [],
            },
        },
        //         created_at: "2021-10-05 18:02:18"
        // deleted_at: null
        // id: 3
        // message: "Hello Ivan 123"
        // subtype_id: 1
        // template_id: 1
        // type_id: 1
        // data() {
        //     return {
        //         mockNotifications: [
        //             {
        //                 link: 150549186,
        //                 checked: true,
        //                 img: 'https://cdn1.ozone.ru/s3/multimedia-j/6068979979.jpg',
        //                 text: 'Ножницы Axent Ultra, 19 см, черные',
        //                 status: 1,
        //             },
        //         ],
        //     };
        // },
        computed: {
            notifications() {
                return this.items;
            },
        },
    };
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .DashboardNotifications {
        display: flex;
        flex-direction: column;

        @include respond-to(md) {
            grid-column: span 3;
        }

        @include respond-to(sm) {
            grid-column: 1 / -1;
            grid-row: 4 / 5;
        }

        @include respond-to(sm) {
            grid-row: 5 / 6;
        }
    }

    .cardHeader {
        display: flex;
        justify-content: flex-start;

        @include respond-to(md) {
            padding-bottom: 1rem;
            border-bottom: 1px $color-gray-light solid;

            & :global(.dashboard-card__header) {
                font-size: 1rem !important;
                font-weight: 600 !important;
            }
        }
    }

    .notificationNumber {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 1.5rem;
        height: 1.5rem;
        margin-left: 0.5rem;
        border-radius: 50%;
        background-color: $color-pink-dark;
        font-size: 0.75rem;
        font-weight: bold;
        color: $white;
    }

    .content {
        position: relative;
        flex-grow: 1;
    }

    .gridWrapper {
        max-height: 16rem;
        margin-top: 1rem;
    }

    .grid {
        display: grid;
        grid-gap: 1rem;
        grid-template-columns: 1fr;
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
