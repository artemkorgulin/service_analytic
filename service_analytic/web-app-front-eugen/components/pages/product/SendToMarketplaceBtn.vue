<template>
    <div :class="$style.SendToMarketplaceBtn">
        <VBtn
            class="primary-btn primary-btn--blue primary-btn--size-small"
            :disabled="!changes.length && marketplaceSlug !== 'wildberries'"
            @click="getMarketplaceUpdateMethod"
        >
            {{ getSendToMarketplaceButtonText }}
        </VBtn>
        <confirm ref="confirm" />
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex';
    import Confirm from '~/components/common/Confirm.vue';

    export default {
        name: 'SendToMarketplaceBtn',
        components: {
            Confirm,
        },
        computed: {
            ...mapGetters({
                changes: 'product/GET_CHANGES',
                marketplaceSlug: 'getSelectedMarketplaceSlug',
            }),
            getMarketplaceUpdateMethod() {
                switch (this.marketplaceSlug) {
                    case 'wildberries':
                        return this.updateMarketWildberries;

                    default:
                        return this.updateMarketOzon;
                }
            },
            getSendToMarketplaceButtonText() {
                switch (this.marketplaceSlug) {
                    case 'wildberries':
                        return 'Отправить в Wildberries';

                    default:
                        return 'Отправить в Ozon';
                }
            },
        },
        methods: {
            ...mapActions({
                restoreChanges: 'product/RESTORE_CHANGES',
                updateMarketWildberries() {
                    this.$refs.confirm.show({
                        title: 'Отправить данные в Wildberries?',
                        text: 'Товар может находиться на модерации до 3 часов, в это время его нельзя редактировать. Чем больше вы заполните характеристик, тем лучше будет продаваться ваш товар.',
                        btn: {
                            confirm: {
                                text: 'Отправить в Wildberries',
                                cls: 'primary-btn primary-btn--size-middle primary-btn--blue',
                            },
                            cancel: {
                                text: 'Отмена',
                                cls: 'default-btn default-btn--size-middle',
                            },
                        },
                        confirm: () => {
                            this.$store.commit('product/SET_ACTION_WILDBERRIES');
                        },
                    });
                },
                updateMarketOzon() {
                    this.$refs.confirm.show({
                        title: 'Отправить данные в Ozon?',
                        text: 'Товар может находиться на модерации до 3 часов, в это время его нельзя редактировать. Чем больше вы заполните характеристик, тем лучше будет продаваться ваш товар.',
                        btn: {
                            confirm: {
                                text: 'Отправить в Ozon',
                                cls: 'primary-btn primary-btn--size-middle primary-btn--blue',
                            },
                            cancel: {
                                text: 'Отмена',
                                cls: 'default-btn default-btn--size-middle',
                            },
                        },
                        confirm: () => {
                            this.$store.commit('product/SET_ACTION_OZON');
                        },
                    });
                },
            }),
        },
    };
</script>

<style lang="scss" module>
    .SendToMarketplaceBtn {
        //
    }
</style>
