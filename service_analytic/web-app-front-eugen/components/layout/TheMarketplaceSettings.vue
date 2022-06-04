<template>
    <div :class="$style.TheMarketplaceSettings">
        <div :class="$style.heading" class="text-center">Добавить аккаунт</div>
        <div class="selected-marketplace-01">
            <v-radio-group v-model="activeMarketplace">
                <div
                    v-for="(item, index) in mpFormatList"
                    :key="item.title"
                    class="selected-mp__item d-flex align-center pr-3 pl-3 mb-3"
                >
                    <v-radio :value="index + 1" hide-details dense class="mb-0"></v-radio>
                    <img :src="item.logo" alt="Логотип маркетплейса" class="mr-3" />
                    <span class="selected-mp__title" v-text="item.title"></span>
                </div>
            </v-radio-group>
        </div>
        <div :class="$style.tabContent" class="custom-scrollbar">
            <div v-for="item in marketplacesActive" :key="item.id" :class="$style.tabContentInner">
                <component :is="getComponent(item.key)" v-if="item.id === activeMarketplace" />
            </div>
        </div>
        <div class="form-actions">
            <ul class="actions-list">
                <li class="actions-list__item">
                    <div class="actions-list__icon">
                        <SvgIcon
                            style="margin-top: 3px"
                            name="filled/check"
                            color="#710BFF"
                        ></SvgIcon>
                    </div>
                    <div class="actions-list__text">
                        Безопасно: Ключи передаются только по зашифрованному протоколу https
                    </div>
                </li>
                <li class="actions-list__item">
                    <div class="actions-list__icon">
                        <SvgIcon
                            style="margin-top: 3px"
                            name="filled/check"
                            color="#710BFF"
                        ></SvgIcon>
                    </div>
                    <div class="actions-list__text">
                        Конфиденциально: Ключи видите только вы. Они передаются и хранятся в
                        зашифрованном виде, их не видим даже мы.
                    </div>
                </li>
                <li class="actions-list__item">
                    <div class="actions-list__icon">
                        <SvgIcon
                            style="margin-top: 3px"
                            name="filled/check"
                            color="#710BFF"
                        ></SvgIcon>
                    </div>
                    <div class="actions-list__text">
                        Приватно: Данные передаются только между маркетплейсом и вашим личным
                        кабинетом SellerExpert. Даже мы не имеем доступ в ваш личный кабинет.
                    </div>
                </li>
            </ul>
            <div class="form-actions__action">
                <VBtn
                    color="accent"
                    :loading="$store.state.isLoadingAddMp"
                    depressed
                    :disabled="addMpBtnEnable"
                    class="se-btn"
                    block
                    @click="addMarketPlaceTick"
                >
                    Добавить маркетплейс
                </VBtn>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapState, mapActions, mapMutations, mapGetters } from 'vuex';
    import { defineComponent } from '@nuxtjs/composition-api';
    import onboarding from '~mixins/onboarding.mixin';

    export default defineComponent({
        name: 'TheMarketplaceSettings',
        mixins: [onboarding],
        props: {
            selMP: {
                type: String,
                default: '',
            },
        },
        data() {
            return {
                mpList: ['OZON Управление товарами', 'WB Управление товарами'],
                activeMarketplace: 3,
            };
        },
        computed: {
            ...mapState('user', {
                menuExpanded: 'isMenuExpanded',
                isAccountSettingsMenuExpanded: 'isAccountSettingsMenuExpanded',
            }),
            ...mapState(['addMpBtnEnable']),
            ...mapGetters({
                getObjectAllMpByName: 'getObjectAllMpByName',
                accounts: 'userActiveAccounts',
                selectedMarketplace: 'getSelectedMarketplace',
                selectedMarketplaceSlug: 'getSelectedMarketplaceSlug',
                marketplaces: 'getMarketplaces',
            }),
            mpFormatList() {
                const getImgMarketPlace = index =>
                    index === 0 ? '/images/icons/ozon.png' : '/images/icons/wb.png';

                return this.mpList.map((_, index) => ({
                    title: _,
                    logo: getImgMarketPlace(index),
                }));
            },
            marketplacesActive() {
                return this.marketplaces.filter(el => el.disable === false);
            },
            accountsCount() {
                return this.accounts.length;
            },
        },
        watch: {
            accountsCount(val, prevVal) {
                if (val && !prevVal) {
                    this.$modal.close();
                    if (this.$route.path !== '/products') {
                        this.$router.push({ name: 'products' });
                    }
                }
            },
        },
        mounted() {
            this.isShow = true;
            this.setDrawerAfterEnter(this.createOnBoarding);
        },
        methods: {
            ...mapActions('user', {
                setAccountSettingsMenuState: 'setAccountSettingsMenuState',
                setMarketplaceSettingsMenuState: 'setMarketplaceSettingsMenuState',
            }),
            ...mapMutations('modal', ['setDrawerAfterEnter']),

            createOnBoarding() {
                const elements = [
                    {
                        el: document.querySelectorAll('.selected-marketplace-01')[0],
                        intro: 'Выберите площадку',
                        pos: 'bottom',
                        callback: () => {
                            this.$store.commit('onBoarding/setOnboardActive', true);
                        },
                    },
                ];

                const createOnBoardingParams = {
                    elements,
                    isDisplayOnboard: true,
                    timeout: 0,
                };

                this.createOnBoardingByParams(createOnBoardingParams);
            },

            addMarketPlaceTick() {
                this.$store.commit('setField', {
                    field: 'addMarketPlaceTick',
                    value: true,
                });
            },

            handleClose() {
                this.isShow = false;
            },
            getMarketplaceId(payload) {
                if (String(payload) === '3') {
                    return '2';
                }
                return '1';
            },
            getComponent(slug) {
                let value;
                switch (slug) {
                    case 'ozon':
                        value = 'OzonSettings';
                        break;
                    case 'wildberries':
                        value = 'WildberriesSettings';
                        break;
                    default:
                        break;
                }
                return value;
            },
        },
    });
</script>
<style lang="scss" scoped>
    .selected-mp {
        &__title {
            font-size: 20px;
            font-weight: bold;
        }
    }

    .form-actions {
        &__action {
            margin: 0 24px 24px 24px;
        }
    }
</style>

<style lang="scss" module>
    /* stylelint-disable */
    .TheMarketplaceSettings {
        display: flex;
        flex-direction: column;
        width: var(--account-menu-width);
        height: 100%;
        border-left: 1px solid $border-color;
        background-color: white;
        box-shadow: 5px 0px 6px 0px rgba(0, 0, 0, 0.07);
    }

    .tabSwitcher {
        display: flex;
        gap: 16px;
        width: 100%;
        padding: 16px 16px 32px;

        & .tabSwitcherInnerTemporary {
            overflow: hidden;
            display: flex;
            gap: 16px;
            width: 100%;
        }

        & .tabBtn {
            height: 42px;
            min-height: 42px;

            &:global(.v-btn:not(.v-btn--round).v-size--default) {
                height: auto;
                padding: 8px;
            }

            &.tabBtnActive {
                box-shadow: 0 8px 32px rgba(113, 11, 255, 0.18);
                border: 1px solid $color-purple-primary;
            }
        }

        & .tabBtnImg {
            width: 24px;
            height: 24px;
            margin-right: 8px;
        }
    }

    .tabContent {
        flex-grow: 1;
        flex-shrink: 1;
        width: 100%;

        & .tabContentInner {
            width: 100%;
        }
    }

    .heading {
        padding: 16px;
        font-size: 26px;
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
            width: 22px;
            height: 22px;
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
