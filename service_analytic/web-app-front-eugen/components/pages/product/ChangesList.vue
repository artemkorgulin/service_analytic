<template>
    <VMenu
        v-if="changes.length"
        v-model="popovers.changes"
        :class="$style.ChangesList"
        offset-y
        bottom
        :nudge-bottom="10"
    >
        <template #activator="{ on, attrs }">
            <VBtn
                class="default-btn default-btn--size-middle"
                outlined
                v-bind="attrs"
                style="position: absolute; top: calc(50% - 20px)"
                v-on="on"
            >
                Изменения: {{ changes.length }}
            </VBtn>
        </template>
        <div :class="$style.ChangesListPopover" class="custom-scrollbar">
            <div>
                <!-- hiding button temporarily-->
                <!-- <button type="button" :class="$style.ButtonOutsider" class="mb-4 pl-3" @click="restoreChangesAll" v-html="`Отменить все изменения (${changes.length})`"></button>-->
                <div
                    v-for="(change, index) in changes"
                    :key="change.timestamp"
                    :class="$style.PopoverItem"
                >
                    <!-- change.timestamp | dateFormat('DD.MM.YY - HH:mm') -->
                    <span :class="$style.PopoverItemDate">
                        {{ $options.filters.formatDateTime(change.timestamp, '$y.$m.$d - $H:$I') }}
                    </span>
                    <span :class="$style.PopoverItemTitle">{{ change.title }}</span>
                    <button type="button" @click="restoreChanges(index)">Отменить изменения</button>
                </div>
            </div>
        </div>
    </VMenu>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex';

    export default {
        name: 'ChangesList',
        data() {
            return {
                popovers: {
                    changes: false,
                },
            };
        },
        computed: {
            ...mapGetters({
                changes: 'product/GET_CHANGES',
            }),
        },
        methods: {
            ...mapActions('product', {
                restoreChanges: 'RESTORE_CHANGES',
                restoreChangesAll: 'restoreChangesAll',
            }),
        },
    };
</script>

<style lang="scss" module>
    .ChangesList {
        //
    }

    .ChangesListPopover {
        min-width: 6.25rem;
        max-height: 335px;
        margin-bottom: 0;
        padding: 0.875rem 1rem 1rem;
        border-radius: 1rem;
        border: 1px solid $color-gray-light;
        background-color: $color-light-background;
        text-align: left;
        font-size: 0.875rem;
        color: $color-main-font;
        box-shadow: 0 4px 64px rgba(0, 0, 0, 0.16);
        font-weight: 500;
        word-break: unset;

        @include phone-large {
            max-width: 420px;
        }

        @include phone-small {
            max-width: 320px;
        }

        & .ButtonOutsider {
            width: max-content;
            text-decoration-line: underline;
            font-size: 0.875rem;
            line-height: 1;
            color: $color-red-secondary;
            font-weight: 500;
        }

        .PopoverItem {
            display: flex;
            margin-bottom: 16px;
            padding: 8px 12px;
            border-radius: 8px;
            background: $color-main-background;
            flex-direction: column;
            transition: all 0.35s;

            &:last-child {
                margin-bottom: 0;
            }

            button {
                width: max-content;
                text-decoration-line: underline;
                font-size: 0.875rem;
                line-height: 1;
                color: $color-red-secondary;
                font-weight: 500;
            }

            .PopoverItemDate {
                font-weight: 500;
                font-size: 0.875rem;
                line-height: 1;
                color: $color-gray-dark;
            }

            .PopoverItemTitle {
                display: block;
                margin: 8px 0;
                font-size: 0.875rem;
                line-height: 1.35;
                color: $color-main-font;
                font-weight: 500;
            }
        }

        //.ButtonOutsider {
        //    width: max-content;
        //    text-decoration-line: underline;
        //    font-size: 0.875rem;
        //    line-height: 1;
        //    color: $color-red-secondary;
        //    font-weight: 500;
        //}
    }
</style>
