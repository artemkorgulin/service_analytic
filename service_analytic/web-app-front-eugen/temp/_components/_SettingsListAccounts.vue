<template>
    <PerfectScrollbar :class="$style.SettingsAccountsList" class="account-list">
        <transition-group name="list-complete" tag="div" class="account-list__items">
            <div
                v-for="item in items"
                :key="item.id"
                v-ripple
                :class="$style.accountItem"
                @click="handleClick(item.id)"
            >
                <BaseRadio :value="String(item.id) === String(value)" />
                <div :class="$style.textWrapper">
                    <div :class="$style.title">
                        {{ item.title }}
                    </div>

                    <div :class="$style.subtitle">
                        {{ item.platform_client_id }}
                    </div>
                </div>
            </div>
        </transition-group>
    </PerfectScrollbar>
</template>

<script>
    export default {
        name: 'SettingsAccountsList',
        props: {
            items: {
                type: Array,
                default: () => [],
            },
            selected: {
                type: Number,
                default: null,
            },
        },
        data() {
            return {
                value: null,
            };
        },
        watch: {
            value() {
                this.$emit('updateSelected', this.value);
            },
        },
        mounted() {
            this.value = this.selected;
        },
        methods: {
            handleClick(payload) {
                this.value = payload;
            },
        },
    };
</script>
<style lang="scss" module>
    .SettingsAccountsList {
        // max-height: 230px;
        // padding: 16px;
    }

    .accountItem {
        display: flex;
        margin-bottom: 16px;
        padding: 16px;
        border-radius: 4px;
        background-color: $base-100;

        &:last-child {
            margin-bottom: 0;
        }
    }

    .textWrapper {
        margin-left: 12px;
    }

    .title {
        line-height: 1.35;
        font-weight: 600;
    }

    .subtitle {
        display: block;
        margin-top: 4px;
        font-size: 12px;
        color: #7e8793;
        font-weight: 400;
    }
</style>
<style lang="scss">
    .account-list {
        max-height: 230px;
        margin-top: 16px;

        &__item {
            margin-top: 8px;
            margin-right: 26px;

            &-id {
                display: block;
                margin-top: 4px;
                font-size: 12px;
                color: #7e8793;
                font-weight: 400;
            }

            &:first-child {
                margin-top: 0;
            }
        }

        .ps__rail-y {
            margin-right: 5px;
        }
    }
</style>
