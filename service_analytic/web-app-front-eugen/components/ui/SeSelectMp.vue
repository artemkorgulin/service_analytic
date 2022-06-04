<template>
    <div>
        <v-dialog v-model="switchWarning" max-width="560px" @click:outside="cancelInWarning">
            <v-card class="warning">
                <v-btn
                    outlined
                    class="warning_btn pl-2 pr-2"
                    style="align-self: end; min-width: auto; min-height: auto"
                    @click="cancelInWarning"
                >
                    <SvgIcon name="outlined/close" />
                </v-btn>
                <img class="warning__img" src="/images/monitorMarket.png" alt="" />
                <h3>Переход в другой маркетплейс</h3>
                <p>
                    Вы уверены, что хотите перейти в другой маркетплейс? Все несохраненные данные
                    будут утеряны
                </p>
                <div class="warning__actions">
                    <v-btn class="se-btn" color="primary" @click="confirmInWarning">Перейти</v-btn>
                    <v-btn outlined @click="cancelInWarning">Отмена</v-btn>
                </div>
            </v-card>
        </v-dialog>
        <v-select
            v-model="selectedId"
            style="width: 320px"
            :loading="loading"
            outlined
            :items="userActiveAccounts"
            item-value="id"
            dense
            hide-details
            class="light-outline"
            background-color="#fff"
            :menu-props="{ nudgeBottom: 42 }"
            :item-disabled="isItemDisabled"
        >
            <template #selection="{ item }">
                <div class="mp">
                    <img :src="item.img" class="mp-img" />
                    <span class="mp-name">
                        {{ item.title }}
                    </span>
                </div>
            </template>
            <template #item="{ item }">
                <div class="mp">
                    <img :src="item.img" class="mp-img" />
                    <span class="mp-name">
                        {{ item.title }}
                    </span>
                </div>
            </template>
        </v-select>
    </div>
</template>

<script>
    import { mapGetters, mapActions } from 'vuex';
    export default {
        props: {
            withConfirmWindow: Boolean,
            disableByPlatform: {
                type: Array,
                default: () => [],
            },
        },
        data() {
            return {
                bufferForConfirmWin: {
                    oldValue: undefined,
                    value: undefined,
                },
                switchWarning: false,
                allowedToChangeAcc: false,
                loading: false,
                selectedId: undefined,
                cancelMode: false,
            };
        },
        computed: {
            ...mapGetters(['isSelectedMp', 'userActiveAccounts']),
        },
        watch: {
            async selectedId(value, oldValue) {
                if (!value || !oldValue) {
                    return;
                }

                if (this.cancelMode) {
                    this.switchWarning = false;
                    this.cancelMode = false;
                } else if (this.withConfirmWindow) {
                    this.switchWarning = true;
                    this.bufferForConfirmWin.value = value;
                    this.bufferForConfirmWin.oldValue = oldValue;
                } else {
                    await this.changeAccount(value);
                }
            },

            userActiveAccounts() {
                this.selectedId = this.isSelectedMp?.userMpId ?? this.userActiveAccounts[0].id;
            },
        },

        created() {
            this.setSelectedId();
        },

        methods: {
            ...mapActions(['handleChangeAccount']),
            ...mapActions({
                loadProducts: 'products/LOAD_PRODUCTS',
            }),
            setDefaultBufferForWarning() {
                for (const key in this.bufferForConfirmWin) {
                    this.bufferForConfirmWin[key] = undefined;
                }
            },
            confirmInWarning() {
                this.switchWarning = false;
                this.changeAccount(this.bufferForConfirmWin.value);
                this.setDefaultBufferForWarning();
            },
            cancelInWarning() {
                // TODO: Поправить этот костыль зависимый от Watcher
                this.cancelMode = true;
                this.selectedId = this.bufferForConfirmWin.oldValue;
                this.setDefaultBufferForWarning();
            },

            async changeAccount(value) {
                this.loading = true;
                await this.handleChangeAccount(value);
                this.loading = false;
                const { key } = this.isSelectedMp;

                this.$emit('resetAccount');
                await this.$store.commit('products/CHANGE_PAGE', 1);

                await this.$store.commit('products/SET_CATEGORY', []);
                await this.$store.commit('category/REMOVE_SEL_CAT');
                await this.$store.commit('category/REMOVE_CATEGORIES');
                await this.$store.commit('category/SET_SEARCH', '');

                this.loadProducts({
                    type: 'common',
                    reload: false,
                    marketplace: key,
                });

                this.$store.commit('products/setField', {
                    field: 'nameSortField',
                    value: '',
                });
            },
            isItemDisabled(item) {
                const {
                    platform: { id },
                } = item;
                return Boolean(this.disableByPlatform.find(el => el === id));
            },
            setSelectedId() {
                const { id } = this.isSelectedMp;
                const needsToSwitchAccount = this.disableByPlatform.find(el => el === id);
                this.selectedId = needsToSwitchAccount ? this.userActiveAccounts.find(el => el.platform.id !== id)?.id : this.isSelectedMp?.userMpId ?? this.userActiveAccounts[0].id;
            },
        },
    };
</script>

<style lang="scss" scoped>
    .mp {
        display: flex;
        align-items: center;
        max-width: 100%;
        gap: 4px;
        font-size: 16px;

        &-name {
            overflow: hidden;
            flex-grow: 1;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        &-img {
            width: 24px;
        }
    }

    .warning {
        display: flex;
        flex-direction: column;
        gap: 32px;
        align-items: center;
        padding: 24px;
        text-align: center;

        img {
            width: 192px;
        }

        h3 {
            font-size: 24px;
        }

        p {
            font-size: 16px;
            font-weight: 400px;
        }

        &__actions {
            display: grid;
            width: 100%;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
    }
</style>
