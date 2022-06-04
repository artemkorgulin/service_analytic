<template>
    <div class="submenu-expandable">
        <!-- TODO: Нужно сделать это все в одном компоненте, чтобы область данных была в одном пространстве -->
        <v-menu offset-x nudge-right="24" :disabled="menuData.disable">
            <template #activator="{ on, attrs }">
                <div
                    class="menu__list-item mb-0"
                    :class="{ 'menu__list-item_disabled': menuData.disable }"
                    v-bind="attrs"
                    v-on="on"
                >
                    <SvgIcon class="menu__list-ico" :name="menuData.iconName" disabled />
                    {{ menuData.name }}
                </div>
            </template>
            <v-list v-if="menuData.submenu" class="submenu-expandable__menu">
                <v-list-item
                    v-for="(item, index) in menuData.submenu"
                    :key="index"
                    class="submenu-expandable__item"
                >
                    <NuxtLink
                        v-ripple
                        class="submenu-expandable__link"
                        :disabled="item.disable"
                        :to="item.route"
                        @click.native="handleMenuClick(item)"
                    >
                        {{ item.name }}
                    </NuxtLink>
                </v-list-item>
            </v-list>
        </v-menu>
    </div>
</template>

<script>
    import { mapActions } from 'vuex';
    export default {
        name: 'TheSubmenuExpandable',
        props: {
            menuData: {
                type: Object,
                required: true,
            },
        },
        methods: {
            ...mapActions('user', ['setMenuState']),
            handleMenuClick(item) {
                if (!item.close) {
                    return;
                }
                return this.setMenuState({ val: false });
            },
        },
    };
</script>

<style lang="scss" scoped>
    .menu {
        &__list-item {
            &_disabled {
                opacity: 0.25;
                cursor: unset;
                user-select: none;

                &:hover {
                    background-color: transparent !important;
                }
            }
        }
    }

    .submenu-expandable {
        position: relative;
        z-index: 100;

        &__menu {
            padding: 0;
        }

        &__item {
            height: 49px;
            padding: 0;

            &:not(:last-child) {
                border-bottom: 1px solid $color-gray-light;
            }

            &_disabled {
                opacity: 0.25;
                cursor: unset;
                user-select: none;
            }
        }

        &__link {
            display: flex;
            align-items: center;
            width: 100%;
            height: 100%;
            padding: 0 16px;
            font-size: 16px;
            font-weight: 500;
            line-height: percentage(22px/16px);
            transition: background-color 300ms ease;

            &:hover {
                background-color: $base-100;
            }
        }
    }
</style>
