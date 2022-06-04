<template>
    <div class="pt-3 pb-3">
        <div :class="$style.headingSecond" style="font-weight: 500" class="pb-4">Аккаунты</div>
        <VRow justify="start" align="center" :class="$style.accountsRow" class="ml-0">
            <VCol
                v-for="item in filteredAccounts"
                :key="item.id"
                :class="$style.item"
                :data-account-id="item.id"
                @click="handleOpenEditAccountModal(item.id)"
            >
                <VListItem class="justify-space-between pa-0">
                    <VListItemTitle :class="$style.name">{{ item.title }}</VListItemTitle>
                    <VBtn fab depressed width="32" height="32" :class="$style.accountBtn">
                        <SvgIcon :class="$style.icon" name="filled/settings" />
                    </VBtn>
                </VListItem>
            </VCol>
            <VCol lg="3" sm="6" cols="12" class="pa-0">
                <VBtn v-ripple :class="$style.addBtn" @click="handleOpenModalAddAccount">
                    <SvgIcon name="outlined/plus" />
                </VBtn>
            </VCol>
        </VRow>
        <!-- <VItemGroup></VItemGroup> -->
    </div>
</template>

<script>
    import { mapGetters, mapState } from 'vuex';

    export default {
        name: 'SettingsAccountsList',
        props: {
            namemarkplace: {
                type: String,
                required: true,
            },
            id: {
                type: [String, Number, Array],
                required: true,
            },
        },
        data() {
            return {
                platformAccounts: [],
                isLoading: false,
            };
        },
        computed: {
            ...mapState('user', {
                companies: 'companies',
            }),
            ...mapGetters({
                accounts: 'getAccounts',
            }),
            filteredAccounts() {
                if (!Array.isArray(this.id)) {
                    return this.accounts.filter(item => item.platform_id === this.id);
                }

                return this.accounts.filter(item => this.id.includes(item.platform_id));
            },
        },
        methods: {
            handleOpenModalAddAccount() {
                return this.$modal.open({
                    component: 'ModalTheMarketplaceSettings',
                    attrs: { selMP: this.namemarkplace },
                });
            },
            handleOpenEditAccountModal(accountId) {
                this.$store.commit('user/SET_CURRENT_EDIT_ACCOUNT_DATA', this.accounts[accountId]);

                return this.$modal.open({ component: 'ModalEditAccount' });
            },
            async handleChangeAccount(payload) {
                try {
                    this.isLoading = true;
                    await this.$axios.$post('/api/v1/set-default-account', {
                        id: payload.id,
                    });
                    await this.$nuxt.$auth.fetchUser();
                    await this.handleChangeMarketplace(this.getMarketplaceId(payload.platform_id));
                    this.isLoading = false;
                } catch (error) {
                    this.isLoading = false;
                }
            },
            getMarketplaceId(payload) {
                if (String(payload) === '3') {
                    return '2';
                }
                return '1';
            },
            async handleChangeMarketplace(id) {
                if (String(id) === String(this.selectedMarketplace)) {
                    return;
                }
                await this.$store.dispatch('setSelectedMarketplace', id);

                if (this.$route?.params?.marketplace) {
                    if (this.$route.meta.redirectOnChangeMarketplace) {
                        return this.$router.push({
                            ...this.$route.meta.fallbackRoute,
                            params: {
                                ...this.$route.params,
                                marketplace: this.selectedMarketplaceSlug,
                            },
                        });
                    } else {
                        return this.$router.push({
                            name: this.$route.name,
                            params: {
                                ...this.$route.params,
                                marketplace: this.selectedMarketplaceSlug,
                            },
                        });
                    }
                }
            },
        },
    };
</script>
<style lang="scss" module>
    .heading {
        @extend %text-body-1;

        font-weight: bold;
        margin: 16px 0;
    }

    .headingSecond {
        @extend %text-body-2;

        margin-bottom: 16px;
    }

    .accountsRow {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(12rem, 1fr));
        grid-gap: 1rem;
    }

    .accountBtn {
        margin-left: 1rem;
    }

    .item {
        align-items: center;
        min-height: 72px;
        border-radius: 8px;
        border: 1px solid #c8cfd9;
        transition: $primary-transition;
        cursor: pointer;

        &:hover {
            border: 1px solid $accent;
            background-color: $base-100;
        }

        &:hover button {
            background-color: rgba(113, 11, 255, 0.1) !important;
            color: $accent;
        }
    }

    .addBtn {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 150px !important;
        min-height: 72px !important;
        padding: 0 !important;
        border: 1px dashed $base-500;
    }

    .name {
        color: $base-900;

        @extend %text-body-2;
        @extend %ellipsis;
    }

    .icon {
        width: 16px !important;
        height: 16px !important;
    }
</style>
