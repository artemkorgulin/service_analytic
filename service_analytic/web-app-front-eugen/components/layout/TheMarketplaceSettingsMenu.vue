<template>
    <transition-group
        v-click-outside="handleClose"
        name="slide-x-transition"
        tag="div"
        appear
        :class="[$style.TheMarketplaceSettingsMenu, menuExpanded && $style.menuExpanded]"
        @afterLeave="handleAfterLeave"
    >
        <TheMarketplaceSettings v-if="isShow" key="marketplaces" />
    </transition-group>
</template>

<script>
    import { mapState, mapActions, mapGetters } from 'vuex';
    import { defineComponent } from '@nuxtjs/composition-api';

    export default defineComponent({
        name: 'TheMarketplaceSettingsMenu',
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
            }),
            ...mapGetters({
                // accounts: 'user/activeAccounts',
                selectedMarketplace: 'getSelectedMarketplace',
                selectedMarketplaceSlug: 'getSelectedMarketplaceSlug',
                marketplaces: 'getMarketplaces',
            }),
        },
        mounted() {
            this.isShow = true;
        },

        methods: {
            ...mapActions('user', {
                setAccountSettingsMenuState: 'setAccountSettingsMenuState',
                setMarketplaceSettingsMenuState: 'setMarketplaceSettingsMenuState',
            }),
            handleClose() {
                this.isShow = false;
            },
            handleAfterLeave() {
                return this.setMarketplaceSettingsMenuState(false);
            },
        },
    });
</script>

<style lang="scss" module>
    .TheMarketplaceSettingsMenu {
        --account-menu-width: 360px;

        position: fixed;
        left: calc(var(--menu-width) + 360px);
        z-index: 111;
        display: flex;
        height: 100vh;
        max-height: 100vh;

        &.menuExpanded {
            left: calc(var(--menu-expanded-width) + 360px);
        }
    }
</style>
