<template>
    <transition-group
        v-click-outside="handleClose"
        name="slide-x-transition"
        tag="div"
        appear
        :class="[$style.TheAccountSettings, menuExpanded && $style.menuExpanded]"
        @afterLeave="handleAfterLeave"
    >
        <client-only>
            <div v-if="isShow" key="accounts" :class="$style.segment">
                <div :class="$style.heading">Аккаунты</div>
                <AccountItem
                    v-for="item in filteredAccounts"
                    :key="item.id"
                    :item="item"
                    :disabled="!!item.pivot.is_selected"
                    @click="handleChangeAccount(item)"
                >
                    <template #prepend>
                        <VImg
                            :class="$style.img"
                            contain
                            disabled
                            :src="getImageMarketplace(item.platform_id)"
                        />
                    </template>
                    <template #text>{{ item.title }}</template>
                    <template #append>
                        <VFadeTransition mode="out-in">
                            <SvgIcon
                                v-if="!!item.pivot.is_selected"
                                :class="$style.check"
                                name="outlined/tick"
                            />
                        </VFadeTransition>
                    </template>
                </AccountItem>
                <AccountItem :class="$style.addAccount" @click="handleAdd">
                    <template #prepend>
                        <div :class="$style.iconWrapper">
                            <SvgIcon :class="$style.icon" name="filled/plus" />
                        </div>
                    </template>
                    <template #text>Добавить аккаунт</template>
                </AccountItem>
            </div>
        </client-only>
        <div v-if="isAdd" key="add" :class="[$style.segment, $style.add]" @click="handleAdd">
            <div :class="$style.heading">Добавить аккаунт</div>
        </div>
    </transition-group>
</template>

<script>
    import { mapState, mapActions, mapGetters } from 'vuex';

    export default {
        name: 'TheAccountSettings',
        data() {
            return {
                isShow: false,
                isAdd: false,
                isLoading: false,
            };
        },
        computed: {
            ...mapState('user', {
                menuExpanded: 'isMenuExpanded',
                isAccountSettingsMenuExpanded: 'isAccountSettingsMenuExpanded',
                isMarketplaceSettingsMenuExpanded: 'isMarketplaceSettingsMenuExpanded',
            }),
            ...mapGetters({
                accounts: 'getAccounts',
                selectedMarketplace: 'getSelectedMarketplace',
                selectedMarketplaceSlug: 'getSelectedMarketplaceSlug',
            }),
            ...mapGetters(['isSelectedMp']),

            filteredAccounts() {
                if (!this.accounts.length) {
                    return [];
                }

                return this.accounts.filter(item => item?.is_active);
            },
            isMobile() {
                return this.$nuxt.$vuetify.breakpoint.mdAndDown;
            },
        },
        mounted() {
            this.isShow = true;
        },

        methods: {
            ...mapActions('user', {
                setAccountSettingsMenuState: 'setAccountSettingsMenuState',
                setMarketplaceSettingsMenuState: 'setMarketplaceSettingsMenuState',
            }),
            ...mapActions({
                loadProducts: 'products/LOAD_PRODUCTS',
            }),

            handleClose() {
                if (!this.isMarketplaceSettingsMenuExpanded) {
                    this.isShow = false;
                }
            },
            handleCloseAdd() {
                this.isAdd = false;
            },
            handleAfterLeave() {
                this.setAccountSettingsMenuState(false);
                this.setMarketplaceSettingsMenuState(false);
            },
            getImageMarketplace(id) {
                if (id === 3) {
                    return '/images/icons/wb.png';
                }
                return '/images/icons/ozon.png';
            },
            getMarketplaceId(payload) {
                if (String(payload) === '3') {
                    return '2';
                }
                return '1';
            },
            handleAdd() {
                return this.$modal.open({
                    component: 'ModalTheMarketplaceSettings',
                });
                // if (this.isMobile) {
                //     this.handleClose();

                // } else {
                //     return this.setMarketplaceSettingsMenuState(true);
                // }
            },
            async handleChangeAccount(payload) {
                try {
                    const topic = '/api/v1/set-default-account';
                    const { id } = payload;

                    this.isLoading = true;
                    await this.$axios.$post(topic, {
                        id,
                    });

                    await this.$nuxt.$auth.fetchUser();
                    this.setActionAfterChangeAcc();
                    // await this.handleChangeMarketplace(this.getMarketplaceId(payload.platform_id));
                    this.isLoading = false;
                } catch (error) {
                    this.isLoading = false;
                    console.log(
                        '🚀 ~ file: TheAccountSettings.vue ~ line 96 ~ handleChange ~ error',
                        error
                    );
                }
            },
            setActionAfterChangeAcc() {
                const { name } = this.$route;
                const { key } = this.isSelectedMp;
                if (name === 'products') {
                    this.loadProducts({
                        type: 'common',
                        reload: true,
                        marketplace: key,
                    });
                } else if (name === 'product-id') {
                    this.$router.push({ name: 'products' });
                }
            },
        },
    };
</script>

<style lang="scss" module>
    .TheAccountSettings {
        --account-menu-width: 360px;

        position: fixed;
        left: var(--menu-width);
        z-index: 111;
        display: flex;
        height: 100%;

        &.menuExpanded {
            left: var(--menu-expanded-width);
        }

        .segment {
            width: var(--account-menu-width);
            height: 100%;
            border-left: thin solid $base-100;
            background-color: white;
            box-shadow: $box-shadow;
        }
    }

    .heading {
        padding: 16px;
        font-size: 20px;
        line-height: 27px;
        color: $base-900;
        font-weight: 600;
    }

    .check {
        margin-left: auto;
        color: $base-900;
    }

    .title {
        @extend %ellipsis;
    }

    .addAccount {
        .icon {
            width: 24px;
            height: 24px;
        }

        &:hover {
            .iconWrapper {
                background-color: #2f3640;
            }
        }
    }

    // .btn {
    //     display: flex;
    //     align-items: center;
    //     height: 56px;
    //     padding: 12px 16px;
    //     gap: 12px;
    //     font-size: 16px;
    //     transition: $primary-transition;
    //     transition-property: background-color;

    //     &:hover {
    //         background-color: #e9edf2;

    //         &.addAccount {
    //             .iconWrapper {
    //                 background-color: #2f3640;
    //             }

    //             .icon {
    //                 color: $white;
    //             }
    //         }
    //     }
    // }

    .icon {
        transition: $primary-transition;
    }

    .iconWrapper {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        transition: $primary-transition;
    }

    .img {
        width: 24px;
        max-width: 24px;
        height: 24px;
        flex-basis: 24px;
    }
</style>
