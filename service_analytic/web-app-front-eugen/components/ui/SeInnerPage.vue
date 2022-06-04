<template>
    <div class="page">
        <template v-if="isDisplayMainTpl">
            <div class="page__header">
                <div class="page__header-title-wrapper">
                    <NuxtLink v-if="back.isActive" :to="back.route" class="page__header-back">
                        <SvgIcon
                            class="page__header-back-img"
                            name="outlined/arrowLeft"
                            data-left
                        />
                        Назад
                    </NuxtLink>
                    <div v-if="backBtn.isActive" class="page__header-back" @click="backBtnClick">
                        <SvgIcon
                            class="page__header-back-img"
                            name="outlined/arrowLeft"
                            data-left
                        />
                        Назад
                    </div>
                    <h1 v-if="title.isActive" class="page__header-title">
                        {{ title.text }}
                    </h1>
                    <se-select-mp
                        v-if="select.isActive"
                        :disable-by-platform="disableByPlatform"
                        @resetAccount="selectResetAccount"
                    />
                    <div v-if="next.isActive" class="page__header-to">
                        <SvgIcon
                            class="page__header-back-img"
                            name="outlined/chevronNext"
                            data-left
                        />
                    </div>
                </div>
                <div class="page__header-btns-wrapper">
                    <v-btn
                        v-if="btnCharacteristics.isActive"
                        outlined
                        @click="btnCharacteristicsClick"
                    >
                        <SvgIcon
                            v-if="btnCharacteristics.isImg"
                            class="mr-1"
                            name="outlined/tableSplit"
                            data-left
                        />
                        {{ btnCharacteristics.text }}
                    </v-btn>
                    <v-btn v-if="btnRequest.isActive" outlined @click="btnRequestClick">
                        <SvgIcon v-if="btnRequest.isImg" name="outlined/speaker" data-left />
                        {{ btnRequest.text }}
                    </v-btn>

                    <div v-if="btnAdd.isActive" :class="btnAdd.class">
                        <v-btn class="se-btn" color="accent" depressed @click="btnAddClick">
                            <SvgIcon v-if="btnAdd.isImg" name="outlined/plus" data-left />
                            {{ btnAdd.text }}
                        </v-btn>
                    </div>
                    <SeDatePickerPeriod
                        v-if="period.isActive"
                        v-model="userDates"
                        @change="periodChange"
                    />
                </div>
            </div>
            <div class="page__content">
                <slot></slot>
            </div>
        </template>
        <div
            v-else
            class="se-card d-flex flex-column justify-center"
            style="height: calc(100vh - 120px)"
        >
            <NoAddedAccPage :no-added-acc="true" style="flex: auto"></NoAddedAccPage>
        </div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';
    import SeDatePickerPeriod from '~/components/ui/SeDatePickerPeriod.vue';

    export default {
        name: 'SeInnerPage',
        components: { SeDatePickerPeriod },
        props: {
            isDisplayMainTpl: {
                type: Boolean,
                default: true,
            },
            back: {
                type: Object,
                default: () => ({}),
            },
            backBtn: {
                type: Object,
                default: () => ({}),
            },
            backBtnClick: {
                type: Function,
                default: () => ({}),
            },
            title: {
                type: Object,
                default: () => ({}),
            },
            select: {
                type: Object,
                default: () => ({}),
            },
            selectResetAccount: {
                type: Function,
                default: () => ({}),
            },
            next: {
                type: Object,
                default: () => ({}),
            },
            btnCharacteristics: {
                type: Object,
                default: () => ({}),
            },
            btnCharacteristicsClick: {
                type: Function,
                default: () => ({}),
            },
            btnRequest: {
                type: Object,
                default: () => ({}),
            },
            btnRequestClick: {
                type: Function,
                default: () => ({}),
            },
            btnAdd: {
                type: Object,
                default: () => ({}),
            },
            btnAddClick: {
                type: Function,
                default: () => ({}),
            },
            period: {
                type: Object,
                default: () => ({}),
            },
            periodChange: {
                type: Function,
                default: () => ({}),
            },
            disableByPlatform: {
                type: Array,
                default: () => [],
            },
        },
        computed: {
            ...mapGetters(['userActiveAccounts']),

            userDates: {
                get() {
                    return this.period.selectedDates;
                },
                set(value) {
                    this.periodChange(value);
                },
            },
        },
    };
</script>

<style lang="scss" scoped>
    .page {
        width: 100%;
        padding: 24px 32px;

        &__header {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            min-height: 40px;

            &-title-wrapper {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            &-back {
                box-sizing: border-box;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
                width: 80px;
                height: 32px;
                border-radius: 8px;
                border: 1px solid #c8cfd9;
                font-size: 14px;
                font-weight: 500;
                line-height: 19px;
                color: #7e8793;
                cursor: pointer;

                &-img {
                    width: 10px;
                    margin: 0;
                }
            }

            &-title {
                font-size: 28px;
                font-weight: 600;
                line-height: 38px;
                color: #2f3640;
            }

            &-to {
                box-sizing: border-box;
                display: flex;
                align-items: center;
                justify-content: center;
                width: 32px;
                height: 32px;
                border-radius: 8px;
                border: 1px solid #c8cfd9;

                & img {
                    width: 10px;
                }
            }

            &-btns-wrapper {
                display: flex;
                gap: 8px;
            }
        }

        &__content {
            margin-top: 24px;
        }
    }
</style>
