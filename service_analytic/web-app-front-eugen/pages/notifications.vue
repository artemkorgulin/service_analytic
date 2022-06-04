<template>
    <div :class="$style.NotificationsPage" class="d-flex flex-column">
        <Page :title="title">
            <div style="height: 83vh" class="perfect-scb">
                <PerfectScrollbar
                    class="perfect-scb__scroll"
                    style="height: 100%"
                    @ps-y-reach-end="getNotif"
                >
                    <table class="se-table">
                        <thead>
                            <tr style="position: sticky; top: 0">
                                <th width="180px">Дата</th>
                                <th width="150px">Группа</th>
                                <th>Содержание</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(item, index) in allNotifList"
                                :key="`notificationL${index}`"
                            >
                                <td>{{ formatDate(item.created_at) }}</td>
                                <td>
                                    {{ notificationType[item.type_id].name || 'Тип отсутствует' }}
                                </td>
                                <td class="notif-mess" v-html="item.message"></td>
                            </tr>
                            <tr v-if="isLoading">
                                <td colspan="100%" class="text-center">
                                    <v-progress-circular
                                        indeterminate
                                        :size="40"
                                        color="#dfdfdf"
                                    ></v-progress-circular>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </PerfectScrollbar>
            </div>
        </Page>
    </div>
</template>
<script>
    /* eslint-disable */
    import { mapActions, mapState } from 'vuex';
    import { format } from 'date-fns';
    import Page from '~/components/ui/SeInnerPage';

    export default {
        name: 'NotificationsPage',
        components: {
            Page,
        },

        data() {
            return {
                title: {
                    isActive: true,
                    text: 'Уведомления',
                },
                pageSettings: {
                    active: 0,
                    page: 0,
                    perPage: 30,
                },
                isLoading: false,
            };
        },
        async fetch() {
            await this.getNotif();
        },
        computed: {
            ...mapState('notification', ['allNotifList', 'notificationType', 'lastPageNotif']),
        },
        methods: {
            ...mapActions('notification', ['getNotifForJournal']),

            formatDate(date) {
                return format(new Date(date), 'dd.MM.yyyy - HH:mm');
            },
            async getNotif() {
                if (this.isLoading || this.lastPageNotif === this.pageSettings.page) return;
                this.isLoading = true;
                this.pageSettings.page += 1;
                await this.getNotifForJournal(this.pageSettings);
                this.isLoading = false;
            },
        },
    };
</script>
<style lang="scss" scoped>
    .perfect-scb {
        @include cardShadow;

        padding: 16px;
        border-radius: 24px;
        background: #fff;

        &__scroll {
            border-radius: 8px;
        }
    }

    .se-table {
        width: 100%;
        border-spacing: 0;

        tr td,
        tr th {
            padding: 15px;
            text-align: left;
            font-size: 14px;
        }

        thead {
            color: $base-700;

            tr th {
                border-right: 1px solid $border-color;
                border-bottom: 1px solid $border-color;
                background: #fff;

                &:last-child {
                    border-right: none;
                }
            }
        }

        tbody tr td {
            border-bottom: 1px solid $border-color;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }
    }
</style>

<style lang="scss" module>
    .NotificationsPage {
        height: 80vh;
        flex-direction: column;
    }

    .heading {
        margin-bottom: 16px;
        font-size: 24px;
        line-height: 33px;
        color: $base-800;
        font-weight: 500;
    }

    .sheet {
        display: flex;
        flex: 1;
        padding: 16px;
        border-radius: 24px;
        background: $white;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
        flex-direction: column;
    }

    .controls {
        display: flex;
        align-items: center;
        width: 100%;
        margin-bottom: 16px;
    }

    .label {
        font-size: 16px;
        line-height: 24px;
        color: $base-900;
    }

    .switch {
        margin-top: 0 !important;
        padding-top: 2px !important;
    }

    .readAllBtn {
        margin-left: auto;
    }

    .table {
        flex: 1;
        border-radius: 8px;
        border: 1px solid $base-400;
    }

    .cellInner {
        @extend %ellipsis;
    }
</style>
