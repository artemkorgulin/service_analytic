<template>
    <div class="notif-list d-flex flex-column">
        <client-only>
            <div ref="notifHeader" class="notif-list__header">
                <VBtn fab absolute class="notif-list__close-btn" @click="close">
                    <VIcon color="base-900">$close</VIcon>
                </VBtn>
                <span class="draw-header">Центр уведомлений</span>
            </div>
            <div ref="notifBody" class="notif-list__body">
                <div
                    v-for="item in notifList"
                    :key="`notification-${item.id}`"
                    class="notif-list__item"
                >
                    <div class="notif-list__item-body">
                        <div class="notif-list__content">
                            <div class="notif-list__name subtitle-1">
                                {{ notificationType[item.type_id].name || 'Тип отсутсвует' }}
                            </div>
                            <div class="notif-list__date mb-2">
                                {{ getDistanceDate(item.created_at) }}
                            </div>
                            <div class="notif-list__message" v-html="item.message"></div>
                        </div>
                    </div>
                </div>
                <div v-if="!notifList.length" class="notif-list__no-notif-mes">
                    Новых уведомлений нет
                </div>
            </div>
            <div
                ref="notifFooter"
                v-ripple
                class="notif-list__read-more"
                @click="closeAndGoToLogPage"
            >
                Журнал уведомлений
            </div>
        </client-only>
    </div>
</template>

<script>
    import { mapState, mapActions, mapMutations } from 'vuex';
    import { formatDistanceStrict, formatDistance, format } from 'date-fns';
    import { ru } from 'date-fns/locale';

    export default {
        computed: {
            ...mapState('notification', ['notifList', 'notificationType']),
        },
        async created() {
            await this.getNotifForJournal();
        },
        mounted() {
            this.listenChanelNotif();
        },
        methods: {
            ...mapActions('notification', ['getNotifForJournal', 'getNotifiTypes']),
            ...mapMutations('notification', ['SET_FIELD']),

            listenChanelNotif() {
                const { api_token: userId } = this.$store.state.auth?.user ?? 0;

                this.$Echo
                    .channel(`eventmaster_database_notifications.${userId}`)
                    .listen('.notification.save', ({ data }) => {
                        this.SET_FIELD({
                            field: 'notifList',
                            value: [JSON.parse(data), ...this.notifList],
                        });
                    });
            },
            getDistanceDate(date) {
                try {
                    const today = new Date();
                    const totalHour = String(
                        formatDistanceStrict(today, new Date(date), { unit: 'hour' })
                    ).split(' ')[0];
                    return totalHour > 12
                        ? format(new Date(date), 'dd.MM.yyyy HH:mm')
                        : `${formatDistance(today, new Date(date), { locale: ru })} назад`;
                } catch {
                    return '';
                }
            },
            close() {
                this.$store.commit('notification/SET_FIELD', {
                    field: 'notifWindow',
                    value: false,
                });
            },
            closeAndGoToLogPage() {
                this.$store.commit('notification/SET_FIELD', {
                    field: 'notifWindow',
                    value: false,
                });
                if (this.$route.name !== 'notifications') {
                    this.$router.push({ path: '/notifications' });
                }
            },
        },
    };
</script>

<style lang="scss" scoped>
    .draw-header {
        @include draw-header;
    }

    .notif-list {
        position: relative;
        height: 100%;

        &__header {
            padding: 24px 16px;
            border-bottom: 1px solid $border-color;
        }

        &__close-btn {
            left: -70px;

            @media screen and (max-width: 600px) {
                top: 16px;
                right: 12px;
                left: auto;
                box-shadow: none;
                background: none !important;
            }
        }

        &__body {
            overflow-y: auto;
            flex: 1 auto;
            font-size: 14px;

            &::-webkit-scrollbar {
                width: 7px;
            }

            &::-webkit-scrollbar-thumb {
                border-radius: 200px;
                background-color: #7e8793;
            }
        }

        &__no-notif-mes {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }

        &__message {
            a {
                font-weight: bold;
                color: $primary-500 !important;
            }
        }

        &__name {
            @include subtitle-1;
        }

        &__date {
            @include date-subtitle;
        }

        &__item-body {
            padding: 8px 16px;
            border-bottom: 1px solid $border-color;
        }

        &__read-more {
            width: 100%;
            margin-top: auto;
            padding: 27px 0;
            border-top: 1px solid $border-color;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            color: $primary-color;
            cursor: pointer;
        }
    }
</style>
