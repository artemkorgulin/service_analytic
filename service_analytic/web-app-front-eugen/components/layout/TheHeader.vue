<template>
    <div :class="$style.TheHeader" class="se-content-header">
        <v-dialog v-model="showCallBack" content-class="se-content-dialog" max-width="600">
            <div class="se-content-close" @click="closeCallBack">
                <SvgIcon name="outlined/close" />
            </div>
            <script data-b24-form="inline/32/fkubxe" data-skip-moving="true">
                (function (w, d, u) {
                    var s = d.createElement('script');
                    s.async = true;
                    s.src = u + '?' + ((Date.now() / 180000) | 0);
                    var h = d.getElementsByTagName('script')[0];
                    h.parentNode.insertBefore(s, h);
                })(
                    window,
                    document,
                    'https://crm.korgulin.ru/upload/crm/form/loader_32_fkubxe.js'
                );
            </script>
        </v-dialog>
        <VBtn class="actionBtn" icon @click="$emit('openMenu')">
            <SvgIcon name="outlined/menufull" />
        </VBtn>
        <VBtn class="logoMobileBtn" icon to="/" nuxt>
            <VImg :class="$style.logoMobile" src="/logoVertical.svg" contain />
        </VBtn>
        <PortalTarget name="header" :class="$style.portalTarget" />
        <div :class="$style.controls">
            <VSelect
                v-if="isCompanies"
                v-model="selectCompanyId"
                :items="companiesSelect"
                style="max-width: 300px"
                label="Компания"
                item-text="name"
                item-value="id"
                class="per-page__select light-outline"
                outlined
                dense
                hide-details
                :menu-props="{ nudgeBottom: 42 }"
                @change="selectCompany"
            />

            <button
                v-ripple
                type="button"
                class="se-content-header__call-action"
                @click="showCallBack = true"
            >
                <SvgIcon name="outlined/phone"></SvgIcon>
            </button>
            <button
                ref="calledBtn"
                v-ripple
                type="button"
                class="se-content-header__helper-btn helper-btn"
                :class="{ 'helper-btn_active': showUserHelper }"
                @click="showHelper"
            >
                <SvgIcon name="outlined/question"></SvgIcon>
                <span class="helper-btn__title">Помощь</span>
                <SvgIcon
                    name="outlined/chevronDown"
                    color="primary"
                    class="helper-btn__chevron"
                    :class="{ 'helper-btn__chevron_rotate-180': showUserHelper }"
                ></SvgIcon>
            </button>
            <VBtn icon @click="openOrCloseNotifWin">
                <SvgIcon :class="$style.icon" name="outlined/notification" />
                <div v-if="isShowNotifBadge" class="se-notif-badge"></div>
            </VBtn>
            <VBadge color="accent" disabled :value="false" overlap></VBadge>
            <div v-if="user" id="account-wrapper">
                <VBtn id="account" icon style="position: relative">
                    <SvgIcon :class="$style.icon" name="outlined/user2" />
                </VBtn>
                <VMenu
                    activator="#account"
                    attach="#account-wrapper"
                    open-on-click
                    content-class="account-menu"
                >
                    <VList class="rounded-lg pa-0" width="288" tile>
                        <VListItem class="py-4" :class="$style.listItemBorder">
                            <VListItemAvatar :class="$style.userAvatar">
                                <SvgIcon :class="$style.icon" name="outlined/user2" />
                            </VListItemAvatar>
                            <VListItemContent>
                                <VListItemTitle>{{ user.name }}</VListItemTitle>
                                <VListItemSubtitle>{{ user.email }}</VListItemSubtitle>
                            </VListItemContent>
                        </VListItem>
                        <VListItem :class="[$style.listItemBorder, $style.userPlan]" dark>
                            <VListItemTitle>{{ userPlan.nameRate }}</VListItemTitle>
                            <VListItemTitle v-if="userPlan.expDate">
                                До {{ userExpDate }}
                            </VListItemTitle>
                        </VListItem>
                        <VListItem
                            v-for="(item, index) in accountItems"
                            :key="index"
                            block
                            tile
                            :to="item.to"
                            :color="item.color"
                            :class="$style.listItemBorder"
                            v-on="item.on"
                        >
                            <VListItemTitle class="font-weight-medium">
                                {{ item.title }}
                            </VListItemTitle>
                        </VListItem>
                        <VListItem @click="openConfirmExitModal()">
                            <VListItemTitle :class="$style.userExit">Выход</VListItemTitle>
                        </VListItem>
                    </VList>
                </VMenu>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapGetters, mapState, mapActions, mapMutations } from 'vuex';
    import { formatDateTime } from '~utils/date-time.utils';

    export default {
        data: () => ({
            showCallBack: false,
            accountItems: [
                {
                    title: 'Тарифы',
                    to: '/tariffs',
                },
                {
                    title: 'Настройки',
                    to: '/settings',
                },
            ],
            user: {},

            selectCompanyId: null,
        }),
        computed: {
            ...mapGetters(['isSelectedMp', 'userActiveAccounts']),
            ...mapGetters('companies', ['companiesSelect', 'isCompanies']),
            ...mapState(['showUserHelper']),
            ...mapState('notification', ['notifList']),
            ...mapGetters({
                productChange: 'product/GET_CHANGES',
                userPlan: 'userPlan',
            }),

            isShowNotifBadge() {
                return this.notifList.length !== 0;
            },
            isShowAccountSelector() {
                return Boolean(this.$route?.meta?.pageGroup);
            },
            isEnableGoBack() {
                return Boolean(this.$route?.meta?.isEnableGoBack);
            },
            isEnableGoBackOnMobile() {
                return Boolean(this.$route?.meta?.isEnableGoBackOnMobile);
            },
            routeBack() {
                if (!this.isEnableGoBack && !this.isEnableGoBackOnMobile) {
                    return {};
                }
                return this.$route?.meta?.fallbackRoute;
            },
            isDesktop() {
                return this.$nuxt.$device.isDesktop;
            },
            isProductPage() {
                return this.$route?.name === 'product-id';
            },
            userExpDate() {
                return formatDateTime(new Date(this.userPlan.expDate), '$d.$m.$y');
            },
        },
        watch: {
            '$auth.$state.user': {
                immediate: true,
                handler(val) {
                    this.user = val;
                    const company = this.user.companies.filter(item => item.pivot.is_selected == 1);
                    this.selectCompanyId = company[0] ? company[0].id : 0;
                    this.setField({ field: 'selectedCompanyId', value: this.selectCompanyId });
                },
            },
        },
        mounted() {
            this.setField({ field: 'userHelperCalledBtn', value: this.$refs.calledBtn });
            this.loadCompanies();
        },
        beforeDestroy() {
            this.setField({ field: 'userHelperCalledBtn', value: undefined });
        },
        methods: {
            ...mapActions('companies', ['loadCompanies']),
            ...mapActions(['handleChangeAccount']),
            ...mapActions({
                loadProducts: 'products/LOAD_PRODUCTS',
            }),

            async selectCompany() {
                await this.$axios({
                    method: 'POST',
                    url: '/api/v1/set-default-company',
                    params: {
                        id: this.selectCompanyId,
                    },
                });
                this.setField({ field: 'selectedCompanyId', value: this.selectCompanyId });
                const response = await this.$axios({
                    method: 'GET',
                    url: '/api/v1/me',
                });
                this.$auth.setUser(response.data.data.user);
                await this.handleChangeAccount(
                    this.isSelectedMp?.userMpId ?? this.userActiveAccounts[0]?.id
                );
                this.$emit('resetAccount');
            },

            ...mapMutations(['setField']),
            showHelper() {
                if (!this.showUserHelper) {
                    setTimeout(() => {
                        this.setField({
                            field: 'openHelper',
                            value: true,
                        });
                    }, 50);
                }
            },
            openOrCloseNotifWin() {
                this.$store.commit('notification/SET_FIELD', {
                    field: 'notifWindow',
                    value: true,
                });

                this.$store.dispatch('notification/readNotif');
            },
            openConfirmExitModal() {
                this.$modal.open({
                    component: 'ModalConfirmExit',
                });
            },
            closeCallBack() {
                this.showCallBack = false;
            },
        },
    };
</script>
<style lang="scss" scoped>
    .se-content-header {
        &__call-action {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            margin: 0 16px;
            border-radius: 50%;
            background: $primary-color;
            color: #fff;
        }
    }

    .se-content-close {
        position: absolute;
        top: 25px;
        right: 24px;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background-color: #f9f9f9;
        color: #000;
        cursor: pointer;
        user-select: none;
    }

    .se-content-close svg {
        width: 18px;
        height: 18px;
    }

    .actionBtn {
        display: none;
    }

    .logoMobileBtn {
        display: none;
    }

    #account-wrapper {
        position: relative;
    }

    @media screen and (max-width: 1010px) {
        .actionBtn {
            display: block;
        }

        .logoMobileBtn {
            display: block;
        }
    }

    .helper-btn {
        display: flex;
        align-items: center;
        height: 100%;
        gap: 10px;
        margin-right: 12px;
        padding: 0 16px;
        border-right: 1px solid $border-color;
        border-left: 1px solid $border-color;
        font-size: 14px;
        color: #7e8793;

        &_active {
            color: $primary-color;
        }

        &__chevron {
            transition: transform 0.2s ease;

            &_rotate-180 {
                transform: rotate(180deg);
            }
        }
    }
</style>
<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .TheHeader {
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: var(--header-h);
        padding-right: 16px;
        padding-left: 16px;
        background: $color-light-background;
        box-shadow: $box-shadow;

        @include borderLine();

        @include md {
            padding-right: 8px;
            padding-left: 8px;
            gap: 4px;
        }
    }

    .logoMobileBtn {
        display: none;
        align-items: center;
        justify-content: center;
        width: var(--header-h);
        height: 100%;
        padding-bottom: 4px;

        @include md {
            display: flex;
        }

        @include sm {
            width: 42px;
        }
    }

    .logoMobile {
        width: 35px;
        height: 37px;
    }

    .portalTarget {
        flex: 1;
        height: 100%;
    }

    .burgerBtn {
        display: none;
        margin-right: 24px;
        margin-left: 24px;

        @include respond-to(md) {
            display: block;
        }
    }

    .arrowBack {
        position: relative;
        width: var(--header-h) !important;
        min-width: var(--header-h) !important;
        max-width: var(--header-h) !important;
        height: 100% !important;
        padding: 0 !important;
        border-right: 1px solid $base-400;

        &.onMobile {
            display: none !important;

            @include md {
                display: flex !important;
            }
        }
    }

    .icon {
        color: $base-800;
    }

    .controls {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        width: 100%;
        height: 100%;
        gap: 8px;
        user-select: none;

        @include sm {
            gap: 0;
        }
    }

    :global(.account-menu) {
        top: 100% !important;
        right: 0;
        left: unset !important;
        overflow: visible;
        display: flex;
        max-width: unset !important;
        margin-top: 20px;
        padding: 0;
        border-radius: 16px;
        contain: none !important;
        flex-direction: column;
        background-color: $white;

        &:before {
            content: '';
            position: absolute;
            top: 2px;
            right: 10px;
            width: 12.4px;
            height: 11px;
            border-right: 10px solid transparent;
            border-bottom: 12px solid #fff;
            border-left: 10px solid transparent;
            transform: translateY(-100%);
        }

        .listItemBorder {
            border-bottom: 1px solid $border-color;
        }

        .userPlan {
            background: $gradient-color;
        }

        .userAvatar {
            background: $color-main-background;
        }

        .userExit {
            color: $error;
        }
    }

    .accountSelector {
        margin-right: auto;
        margin-left: 24px;
    }

    .marketplaceSelector,
    .accountSelector {
        @include respond-to(md) {
            display: none !important;
        }
    }
</style>

<style lang="scss">
    .se-content-dialog {
        position: relative;
    }
</style>
