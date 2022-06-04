<template>
    <div :class="$style.AdmListItemMobile">
        <div :class="$style.activeAreaWrapper">
            <div :class="[$style.activeArea]">
                <VStatusIcon
                    :title="item.campaign_status.name"
                    :class="$style.campaignStatus"
                    :status="item.campaign_status_id"
                />
                <div :class="$style.heading">
                    <div :class="$style.headingInner">{{ item.name }}</div>
                </div>
                <div
                    v-ripple="{ center: true }"
                    :class="[$style.actionBtn, isActive && $style.active]"
                    @click="handleClick"
                >
                    <VIcon :class="$style.icon">{{ isActive ? '$eye' : '$eyeOff' }}</VIcon>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'AdmListItemMobile',
        props: {
            item: {
                type: Object,
                default: () => ({}),
            },
            isActive: {
                type: Boolean,
                default: false,
            },
        },
        data() {
            return {
                // isExpand: false,
                // countersValuesDict: {
                //     avg_1000_shows_price: 'СРМ, ₽',
                //     avg_click_price: 'СРС, ₽',
                //     clicks: 'Клики',
                //     cost: 'Расход, ₽',
                //     cpo: 'СРО, ₽',
                //     ctr: 'CTR, %',
                //     drr: 'ДРР, %',
                //     orders: 'Заказы',
                //     popularity: 'Популярность',
                //     profit: 'Выручка, ₽',
                //     purchased_shows: 'Выкупленных показов, %',
                //     shows: 'Показы',
                // },
            };
        },
        // computed: {
        //     isStatistic() {
        //         const statObj = this?.item?.sum_statistics;
        //         if (!statObj || !Object.keys(statObj).length) {
        //             return false;
        //         }
        //         return !Object.values(statObj).every(item => item === null);
        //     },
        // },
        methods: {
            // handleExpand() {
            //     this.isExpand = !this.isExpand;
            // },
            handleClick() {
                return this.$emit('select', this.item);
            },
        },
    };
</script>

<style lang="scss" module>
    $size: 48px;

    .AdmListItemMobile {
        position: relative;
        display: flex;
        flex-direction: column;
        padding-right: 0;

        @include borderLine;
    }

    .activeAreaWrapper {
        display: flex;
        width: 100%;
        height: 48px;
    }

    // .counters {
    //     border-top: 1px solid $base-400;
    // }

    .heading {
        @extend %text-body-1;

        margin-left: 10px;
    }

    .headingInner {
        @extend %ellipsis;
    }

    .campaignStatus {
        margin-right: 8px;
    }

    // .chevronWrapper {
    //     display: flex;
    //     align-items: center;
    //     justify-content: center;
    //     flex: 1 0 $size;
    //     width: $size;
    //     min-width: $size;
    //     max-width: $size;
    //     height: $size;
    //     margin-left: auto;
    //     background-color: $white;
    //     transition: $primary-transition;
    //     cursor: pointer;

    //     &:hover {
    //         background-color: $base-100;
    //     }

    //     .chevron {
    //         width: 16px;
    //         height: 16px;
    //         color: $base-700;
    //         transition: $primary-transition;
    //     }

    //     &.expanded {
    //         .chevron {
    //             transform: rotate(180deg);
    //         }
    //     }
    // }

    .activeArea {
        display: flex;
        align-items: center;
        flex: 1;
        flex: 1 1 auto;
        // max-width: calc(100% - #{$size});
        height: 100%;
        padding-right: 16px;
        padding-left: 16px;
        background-color: $white;
        transition: $primary-transition;
        cursor: pointer;

        // &:hover {
        //     background-color: $base-100;
        // }

        &.withStatistic {
            max-width: calc(100% - #{$size});
        }
    }

    .actionBtn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        min-width: 32px;
        height: 32px;
        margin-left: auto;
        border-radius: 50%;
        background-color: $base-100;
        transition: $primary-transition;

        &:hover {
            background-color: $base-400;
        }

        &.active {
            background-color: $base-900;

            .icon {
                color: $white;
            }

            &:hover {
                background-color: $base-800;
            }
        }
    }
</style>
