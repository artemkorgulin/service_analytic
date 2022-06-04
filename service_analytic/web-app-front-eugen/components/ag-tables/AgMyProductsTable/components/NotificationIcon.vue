<template>
    <VMenu offset-y open-on-hover top :nudge-bottom="-10">
        <template #activator="{ on, attrs }">
            <div
                :class="[$style.Status, getColorClass, { [$style.Pending]: pending }]"
                v-bind="attrs"
                v-on="on"
            >
                <SvgIcon name="filled/bell" />
            </div>
        </template>
        <VCard class="custom-popover" max-width="270">
            <span class="custom-popover__text">{{ getMessage }}</span>
            <span v-if="status === 1" class="custom-popover__text custom-popover__text--gray">
                    {{ $options.filters.formatDateTime(time, '$y.$m.$d') }}
                </span>
        </VCard>
    </VMenu>
</template>

<script>
    export default {
        name: 'NotificationIcon',
        props: {
            status: {
                type: Number,
                required: true,
            },
            time: {
                type: null,
                default: null,
            },
            pending: {
                type: Boolean,
                default: false,
            },
        },
        data: () => ({
            messages: {
                on: 'Есть уведомления',
                off: 'Нет уведомлений',
            },
        }),
        computed: {
            getMessage() {
                if (this.status === 1) {
                    return this.messages.on;
                } else {
                    return this.messages.off;
                }
            },
            getColorClass() {
                if (this.status === 1) {
                    return this.$style.HasNotifications;
                } else {
                    return this.$style.NoNotifications;
                }
            },
        },
    };
</script>

<style lang="scss" module>
    .Status {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        border-radius: 0.5rem;
        border: 1px solid $color-gray-light;
        font-size: 1rem;
        color: $color-gray-light-100;
        transition: $transition-fast;
    }

    .HasNotifications {
        border: 1px $error solid;
        background-color: $white;
        color: $error;
    }

    .NoNotifications {
        border: 1px $color-gray-blue-light solid;
        background-color: $white;
        color: $color-gray-light-100;
    }

    .Pending {
        opacity: 0.24;
        pointer-events: none;
    }
</style>
