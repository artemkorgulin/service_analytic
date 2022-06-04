<template>
    <div class="menu" :class="classes" :style="styles">
        <div
            alt="Фон панели меню"
            class="menu__bcg"
            :class="$style.menuBcg"
            :style="`background: url(${bcgDecorComputed})`"
        />
        <div :class="$style.topBlockWrapper" class="menu__top" style="position: relative">
            <VFadeTransition appear>
                <NuxtLink v-show="menuExpanded" to="/" :class="$style.logo">
                    <VImg src="/logoHorizontal.svg" width="130px" contain />
                </NuxtLink>
            </VFadeTransition>
            <TheBurgerButton style="position: absolute; right: 0" @click="handleToggleMenu" />
        </div>
        <div :class="$style.list" class="menu__list">
            <template v-for="(item, index) in menuItems">
                <template v-if="item.submenu && item.submenu.length > 0">
                    <div v-if="menuExpanded" :key="item.key">
                        <div class="menu__list-item" @click="openMenu(index)">
                            <SvgIcon class="menu__list-ico" :name="item.iconName" />
                            <span class="menu__list-title">{{ item.name }}</span>
                            <SvgIcon
                                name="outlined/chevronDown"
                                color="#fff"
                                style="height: 20px"
                                class="menu__arrow"
                                :class="{ menu__arrow_rotate: openedSubMenu.includes(index) }"
                            />
                        </div>
                        <v-expand-transition>
                            <div
                                v-if="openedSubMenu.includes(index)"
                                class="menu__list-submenu main-sub-menu"
                            >
                                <NuxtLink
                                    v-for="subItem in item.submenu"
                                    :key="subItem.name"
                                    v-ripple="!item.disable"
                                    class="main-sub-menu__item"
                                    :class="{ 'main-sub-menu__item_disabled': subItem.disable }"
                                    :disabled="subItem.disable"
                                    :to="subItem.route"
                                    @click.native="handleMenuClick"
                                >
                                    {{ subItem.name }}
                                </NuxtLink>
                            </div>
                        </v-expand-transition>
                    </div>
                    <TheSubmenuExpandable v-else :key="item.key + 1" :menu-data="item" />
                </template>
                <template v-else>
                    <NuxtLink
                        :key="item.key"
                        v-ripple="!item.disable"
                        class="menu__list-item"
                        :class="[$style.menuListItem, item.disable && $style.disabled]"
                        :disabled="item.disable"
                        :exact="item.exact || false"
                        :to="
                            item.routeObject
                                ? {
                                      name: item.routeObject.name,
                                      params: {
                                          marketplace: selectedMarketPlaceSlug,
                                      },
                                  }
                                : item.route
                        "
                        @click.native="handleMenuClick(item)"
                    >
                        <SvgIcon class="menu__list-ico" :name="item.iconName" />
                        {{ item.name }}
                    </NuxtLink>
                </template>
            </template>
        </div>

        <VFadeTransition appear>
            <div v-show="menuExpanded && !userPlan.paid" class="menu__banner mt-3 mb-3">
                Доступно только <span class="menu__banner-red">3 товара</span>
                <br />
                Чтобы оптимизировать больше товаров, выберите
                <NuxtLink to="/tariffs">Подписку</NuxtLink>.
            </div>
        </VFadeTransition>

        <VFadeTransition appear>
            <div v-show="menuExpanded" class="menu__footer footer">
                <VImg
                    max-width="154"
                    src="/images/skolkovo-badge.svg"
                    contain
                    class="footer__sk-badge"
                />
                <div class="footer__text-small">Бесплатно по России</div>
                <a class="footer__text-big" href="tel:+79315211659">+7 (931) 521 16 59</a>
            </div>
        </VFadeTransition>
    </div>
</template>

<script>
    const disabledMenuItems = process.env.DISABLED_MENU_ITEMS.split(',').map(_ => _.trim());
    /* eslint-disable no-unused-expressions*/
    import { mapGetters, mapActions, mapState } from 'vuex';

    export default {
        name: 'TheMenu',
        props: {
            isMobile: {
                type: Boolean,
                default: false,
            },
        },
        data() {
            return {
                isShowIconMP: false,
                openedSubMenu: [],
                selected: {
                    section: null,
                    marketplace: 0,
                },
                rolesUserFlag: true,
            };
        },

        computed: {
            ...mapGetters(['getAccounts', 'isSelectedMp', 'userPlan']),
            ...mapGetters('companies', ['isCompanies', 'companiesSelect']),
            ...mapGetters({
                marketplacesItems: 'getMarketplacesItems',
                selectedMarketplaceSlug: 'getSelectedMarketplaceSlug',
                menuExpanded: 'user/getExpandedState',
                userInfo: '$auth',
            }),
            ...mapState(['selectedCompanyId']),
            ...mapState('companies', ['companies']),
            ...mapState('user', {
                menuExpanded: 'isMenuExpanded',
                accMenuExpanded: 'isAccountSettingsMenuExpanded',
            }),
            firstLogin() {
                try {
                    const {
                        user: { first_login },
                    } = this.$store.state.auth;

                    return first_login === 1;
                } catch {
                    return false;
                }
            },
            getStyleIMGMp() {
                const style = [
                    'position: absolute',
                    'transform: translateX(193px)',
                    'transition: all 0.3s ease',
                ];
                if (!this.menuExpanded) {
                    style[1] = 'transform: translateX(-6.5px)';
                }
                return style.join(';');
            },
            menuItems() {
                const selectedMarketplaceSlug = this.selectedMarketplaceSlug || 'ozon';
                return [
                    {
                        id: 1,
                        name: 'Главная',
                        key: 'dashboard',
                        route: {
                            name: 'index',
                        },
                        exact: true,
                        iconName: 'outlined/dashboard',
                        disable: disabledMenuItems.includes('dashboard'),
                    },
                    {
                        id: 2,
                        name: 'Мои товары',
                        key: 'products',
                        iconName: 'outlined/product',
                        route: {
                            name: 'products',
                            params: { marketplace: this.isSelectedMp.key },
                        },

                        disable: disabledMenuItems.includes('products'),
                    },
                    {
                        id: 3,
                        name: 'Рекламные кампании',
                        key: 'adm',
                        route: {
                            name: 'adm-campaigns',
                            params: { marketplace: selectedMarketplaceSlug },
                        },

                        disable: disabledMenuItems.includes('adm'),
                        iconName: 'outlined/presentation',
                    },
                    {
                        id: 4,
                        name: 'Аналитика',
                        key: 'analytics',
                        iconName: 'outlined/cartGraph',
                        disable: disabledMenuItems.includes('analytics'),
                        submenu: [
                            {
                                route: '/analytics/brand',
                                name: 'Бренд',
                                disable: disabledMenuItems.includes('analytics/brand'),
                            },
                            {
                                route: '/analytics/categories',
                                name: 'Категории',
                                disable: disabledMenuItems.includes('analytics/categories'),
                            },
                            {
                                route: '/analytics/category-analysis',
                                name: 'Категорийный анализ',
                                disable: disabledMenuItems.includes('category-analysis'),
                            },
                            {
                                route: '/action-call',
                                name: 'Сигналы к действию',
                                disable: disabledMenuItems.includes('action-call'),
                            },
                        ],
                    },
                    {
                        id: 7,
                        name: 'Уведомления',
                        key: 'notifications',
                        route: '/notifications',
                        iconName: 'outlined/notification',
                        disable: disabledMenuItems.includes('notifications'),
                    },
                    {
                        id: 9,
                        name: 'Обучение',
                        key: 'faq',
                        route: '/faq',
                        iconName: 'outlined/study',
                        disable: disabledMenuItems.includes('faq'),
                    },

                    {
                        id: 10,
                        name: 'Настройки',
                        key: 'settings',
                        iconName: 'outlined/settings',
                        disable: disabledMenuItems.includes('settings'),
                        submenu: [
                            {
                                route: '/settings',
                                name: 'Пользователь',
                                disable: disabledMenuItems.includes('settings'),
                            },
                            {
                                route: '/companies',
                                name: 'Компании',
                                disable: this.rolesUserFlag,
                                // disabledMenuItems.includes('role-model')
                            },
                            {
                                key: 'marketplaces',
                                route: {
                                    name: 'marketplaces',
                                },
                                name: 'Маркетплейсы',
                                disable: disabledMenuItems.includes('marketplaces'),
                            },
                        ],
                    },
                ];
            },
            classes() {
                return [
                    this.$style.TheMenu,
                    'menu',
                    {
                        [this.$style.expanded]: this.menuExpanded,
                        'menu--open': this.menuExpanded,
                        'menu--close': !this.menuExpanded,
                    },
                ];
            },
            styles() {
                return {
                    background: this.backgroundComputed,
                };
            },
            selectedMarketplace() {
                return this.marketplacesItems[this.selected.marketplace];
            },
            backgroundComputed() {
                if (!this.selected.marketplace) {
                    return 'linear-gradient(180deg, #43339A 0%, #5A79E8 100%)';
                }
                return 'linear-gradient(180deg, #43339A 0%, #985AE8 100%)';
            },
            bcgDecorComputed() {
                if (!this.selected.marketplace) {
                    return '/images/menu-fon-decor-ozon.svg';
                }
                return '/images/menu-fon-decor-wb.svg';
            },
        },
        watch: {
            menuExpanded(value) {
                this.setWhenTheMenuClosed(value);
            },
            selectedCompanyId(value) {
                if (value === 0) {
                    this.rolesUserFlag = true;
                    return;
                }

                try {
                    const selectedCompany = this.companies.find(({ id }) => id === value);
                    this.rolesUserFlag = !selectedCompany.roles.some(el =>
                        ['company.admin', 'company.owner'].includes(el)
                    );
                } catch {
                    this.rolesUserFlag = true;
                }

                if (this.$route.name === 'companies') {
                    this.$router.push({ name: 'index' });
                }
            },
            companies(values) {
                const company = this.companies.filter(
                    item =>
                        item.roles.includes('company.admin') || item.roles.includes('company.owner')
                );
                this.rolesUserFlag = !company.length;
            },
        },
        mounted() {
            this.handleResize();
            window.addEventListener('resize', this.handleResize, { passive: true });
        },
        beforeDestroy() {
            window.removeEventListener('resize', this.handleResize);
        },
        methods: {
            ...mapActions('user', {
                setMenuState: 'setMenuState',
                setAccountSettingsMenuState: 'setAccountSettingsMenuState',
            }),
            openMenu(indexMenu) {
                if (indexMenu === this.openedSubMenu[0]) {
                    this.openedSubMenu = [];
                    return;
                }
                this.openedSubMenu = [indexMenu];
            },
            setWhenTheMenuClosed(value) {
                const { $el } = this;

                let lastWidthMenu = $el.offsetWidth;
                const interval = setInterval(() => {
                    if (lastWidthMenu === $el.offsetWidth) {
                        this.isShowIconMP = !this.menuExpanded;
                        clearInterval(interval);
                    } else {
                        lastWidthMenu = $el.offsetWidth;
                    }
                }, 30);
            },
            handleToggleMenu() {
                return this.setMenuState({ val: !this.menuExpanded, bur: true });
            },
            handleResize() {
                if (window.innerWidth < 1024) {
                    return this.setMenuState({ val: false });
                }
            },
            handleMenuClick(item) {
                if (this.isMobile) {
                    return this.setMenuState({ val: false });
                }
            },
            handleOpenAccountSettings() {
                return this.setAccountSettingsMenuState(true);
            },
        },
    };
</script>
<style lang="scss" module>
    :root {
        --menu-width: 72px;
        --menu-expanded-width: 288px;

        @include respond-to(md) {
            --menu-width: 0;
        }
    }

    .TheMenu {
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        z-index: 4;
        overflow: hidden;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        height: 100%;
        padding-bottom: 22px;
        background-position: bottom;
        transition: $primary-transition;
        transition-duration: 0.35s;
        user-select: none;
        will-change: transform, width;
        box-shadow: inset -16px 0 40px rgba(0, 0, 0, 0.08);

        .topBlockWrapper {
            min-height: var(--header-h);
        }

        .logo {
            width: 100%;
            transition: $primary-transition;
            transition-property: opacity;

            &:hover {
                opacity: 0.8;
            }
        }

        .menuBcg {
            width: 100%;
            height: 300px;
        }

        .menuListItem {
            user-select: none;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            width: 100%;
            height: 50px;
            padding: 0 14px;
            border-radius: 8px;
            border: none;
            background: transparent;
            text-decoration: none;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 16px;
            line-height: 1;
            color: white;
            cursor: pointer;
            font-weight: 700;

            .icon {
                width: 24px !important;
                height: 24px !important;
            }

            .text {
                @extend %ellipsis;

                display: none;
            }

            &.disabled {
                opacity: 0.25;
                cursor: unset;
                user-select: none;
            }

            &.menuListItemFAQ {
                margin-top: auto;
                //background: $white;
                border: 1px solid $white;
                background: rgba(255, 255, 255, 0.16);
                color: $white;

                &:global(.nuxt-link-active) {
                    background: $white !important;
                    color: #607fea;
                }

                & :global(.icon.sprite-filled) {
                    width: 24px;
                    height: 24px;
                }
            }
        }

        &.expanded {
            .text {
                display: initial;
            }
        }
    }

    .list {
        flex-direction: column;
        gap: 12px;
        display: flex;
    }

    .settingsWrapper {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;

        .menuListItem {
            max-width: 100%;
            margin-bottom: 0;
            flex-basis: 100%;

            &.active {
                background: rgb(255 255 255 / 6%);
            }
        }
    }
</style>
<style lang="scss">
    .icon_mp {
        &__not-avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 1px solid #fff;
            background: rgb(255 255 255 / 16%) !important;
            font-size: 18px;
        }
    }

    /* stylelint-disable declaration-no-important,no-duplicate-selectors */

    .menu {
        &--open {
            width: var(--menu-expanded-width);
            padding-right: 16px;
            padding-left: 16px;
        }

        &--close {
            width: var(--menu-width);
            padding-right: 11px;
            padding-left: 11px;

            @include respond-to(md) {
                visibility: hidden;
            }
        }

        &__arrow {
            transform: rotate(0deg);
            transition: 0.2s ease all;

            &_rotate {
                transform: rotate(180deg);
            }
        }

        &__list-title {
            flex: auto;
        }

        &__bcg {
            position: absolute;
            right: 0;
            bottom: 0;
            left: 0;
            z-index: -1;
            width: 100%;
            transition: 0.35s;
        }

        &__top {
            display: flex;
            align-items: center;
            max-width: 225px;
            margin: 0 12px 8px 12px;
            // margin-bottom: 8px;
            // padding: 0 12px;
        }

        &__btn {
            position: relative;
            width: 24px;
            min-width: 24px;
            padding: 0;
            border: none;
            background: none;
            outline: none;
            cursor: pointer;
            box-shadow: none;

            span {
                display: block;
                width: 100%;
                height: 2px;
                margin: 6px 0;
                border-radius: 2px;
                background: white;
                transition: 0.2s;
            }

            span:last-child {
                width: 50%;
            }

            &:hover {
                span:last-child {
                    width: 100%;
                }
            }
        }

        &--close &__btn {
            span:last-child {
                width: 100%;
            }

            &:hover {
                span:last-child {
                    width: 50%;
                }
            }
        }

        &__list {
            &-img {
                width: 32px;
                margin-left: auto;
                transition: 0.75s;
                will-change: transform, margin;

                // & + .el-collapse-item__arrow {
                //     margin-left: 10px !important;
                // }
            }

            &-item {
                position: relative;
                display: flex;
                align-items: center;
                width: 100%;
                height: 50px;
                // margin-bottom: 12px;
                padding: 0 14px;
                border-radius: 8px;
                border: none;
                background: transparent;
                text-decoration: none;
                font-size: 16px;
                line-height: 1;
                color: white;
                cursor: pointer;
                font-weight: 700;

                &.nuxt-link-active {
                    background: rgb(255 255 255 / 16%) !important;
                }

                &:hover {
                    background: rgba(255, 255, 255, 0.16);
                }
            }

            &-ico {
                min-width: 24px;
                min-height: 24px;
                margin-top: -1px;
                margin-right: 15px;
            }
        }

        &--close {
            .menu__sublist {
                display: none;
            }

            .menu__list-img {
                margin-left: unset;
                transform: scale(1.2);
            }

            .menu__list-item {
                // justify-content: center;
                font-size: 0;
            }
        }

        &__banner {
            padding: 12px 16px;
            border-radius: 16px;
            background-color: $white;
            font-weight: 500;
            font-size: 14px;
            line-height: 19px;

            &-red {
                color: $error;
            }

            & a {
                text-decoration: underline;
            }
        }

        &__footer {
            margin-top: auto;
            white-space: nowrap;
        }
    }

    .footer {
        padding-left: 10px;
        color: $white;

        &__sk-badge {
            padding-bottom: 24px;
        }

        &__text-small {
            font-weight: 400;
            font-size: 16px;
            line-height: 24px;
        }

        &__text-big {
            font-weight: 500;
            font-size: 24px;
            line-height: 33px;
        }
    }
</style>
