<template>
    <div class="se-helper">
        <div
            v-show="showUserHelper"
            ref="modal"
            v-click-outside="onClickOutside"
            :style="helperCoords"
            class="se-helper__modal se-helper__modal_place_br"
        >
            <div class="se-helper__list">
                <template v-for="(item, index) in modalMenu">
                    <div
                        v-if="menuItemShow[index]"
                        :key="item.tilte"
                        v-ripple
                        class="se-helper__list-item"
                        @click="actionClickMenuItem(item.handler)"
                    >
                        <SvgIcon class="se-helper__icon" :name="item.icon" color="#000"></SvgIcon>
                        <span class="se-helper__list-text" v-text="item.title"></span>
                    </div>
                </template>
            </div>
        </div>
        <div
            v-if="!otherBtn"
            v-ripple
            class="se-helper__btn se-helper__btn_place_br"
            @click="openModal"
        >
            <SvgIcon class="se-helper__btn-icon" name="filled/question" color="#fff"></SvgIcon>
        </div>
    </div>
</template>

<script>
    import { mapGetters, mapState, mapMutations } from 'vuex';
    import { getCoords } from '~/assets/js/utils/helpers';
    // b24-widget-button-wrapper
    export default {
        data() {
            return {
                helperCoords: undefined,
                otherBtn: false,
                btxBtn: undefined,
                showCallBack: false,
                ytUrl: undefined,
                faqTab: undefined,
                studyUrl: undefined,
                modalMenu: [
                    {
                        icon: 'outlined/sparkles',
                        title: 'Справка',
                        handler: 'faq',
                    },
                    {
                        icon: 'outlined/study',
                        title: 'Обучение',
                        handler: 'study',
                    },
                    {
                        icon: 'outlined/chatComment',
                        title: 'Написать в чат',
                        handler: 'mess',
                    },
                ],
                optionsUrl: [
                    {
                        url: '/welcome',
                    },
                    {
                        url: '/marketplaces',
                        study: true,
                    },
                    {
                        url: '/login',
                        ytUrl: 'https://www.youtube.com/embed/ereTkiaT-fs',
                        openModal: true,
                    },
                    {
                        url: '/signup',
                        ytUrl: 'https://www.youtube.com/embed/ereTkiaT-fs',
                        openModal: true,
                    },
                    {
                        url: '/',
                        selection: 3,
                    },
                    {
                        url: '/product',
                        ytUrl: 'https://www.youtube.com/embed/QiTOgHsPwQ8',
                        section: 'search_opt',
                        selection: 8,
                    },
                    {
                        url: '/product',
                        section: 'deposit',
                        study: true,
                    },
                    {
                        url: '/product',
                        ytUrl: 'https://www.youtube.com/embed/IcpZiFnxESY',
                        section: 'content',
                        selection: 9,
                    },
                    {
                        url: '/product',
                        ytUrl: 'https://www.youtube.com/embed/WjoqJEdJbq0',
                        section: 'dashboard',
                    },
                    {
                        url: '/tariffs',
                        ytUrl: 'https://www.youtube.com/embed/kGg2rZhw1iI',
                        selection: 2,
                        study: true,
                    },
                    {
                        url: '/products',
                        ytUrl: 'https://www.youtube.com/embed/74gZNkoEHnQ',
                        selection: 7,
                        study: true,
                    },
                ],
            };
        },
        computed: {
            ...mapGetters(['firstLogin', 'userActiveAccounts']),
            ...mapState(['showUserHelper', 'userHelperCalledBtn', 'openHelper']),

            routerPath() {
                return {
                    path: this.$route.path,
                    query: this.$route.query,
                };
            },
            showStudy() {
                const windowInnerWidth = document.body.offsetWidth;
                return windowInnerWidth >= 720;
            },
            menuItemShow() {
                return [Boolean(this.faqTab), Boolean(this.studyUrl) && this.showStudy, true];
            },
        },

        watch: {
            openHelper(value) {
                if (!value) {
                    return;
                }

                this.setField({
                    field: 'openHelper',
                    value: false,
                });

                this.openModal();
            },
            userHelperCalledBtn(value) {
                if (!value) {
                    return;
                }

                this.otherBtn = true;
            },
            routerPath: {
                deep: true,
                handler(value) {
                    this.setYtUrl(value);
                },
            },
        },
        mounted() {
            this.btxBtn = document.querySelector('.b24-widget-button-icon-animation');

            if (this.firstLogin) {
                this.openModal(true);
            }

            this.setYtUrl(this.routerPath);
        },
        methods: {
            ...mapMutations(['setField']),
            setYtUrl(value) {
                const { query = {} } = value;

                const findFieldQuery = val => val.find(_ => Object.keys(query).includes(_));
                const setLinks = item => {
                    if (item) {
                        if (!this.firstLogin && item.openModal !== undefined) {
                            this.openModal(item.openModal);
                        }
                        this.faqTab = item.selection;
                        this.ytUrl = item.ytUrl;
                        this.studyUrl = item.study ? item.url : undefined;
                    } else {
                        this.faqTab = undefined;
                        this.ytUrl = undefined;
                        this.studyUrl = undefined;
                    }
                };

                try {
                    const path = (() => {
                        const npath = value.path.split('/').filter(_ => _)[0];
                        return npath ? `/${npath}` : '/';
                    })();

                    const items = this.optionsUrl.filter(({ url }) => url === path);

                    if (items.length && items.length <= 1) {
                        const item = items[0];

                        setLinks(item);
                    } else if (items.length && items.length > 1) {
                        const genField = findFieldQuery(Object.keys(items[0]));
                        const querySet = value.query[genField];
                        const item = items.find(_ => _[genField] === querySet);

                        setLinks(item);
                    } else {
                        setLinks();
                    }
                } catch {
                    setLinks();
                }
            },
            async openModal(state) {
                await this.setCoords();

                const boxChat = document.querySelector('.bx-livechat-box');
                if (boxChat) {
                    return;
                }

                this.setField({
                    field: 'showUserHelper',
                    value: typeof state === 'boolean' ? state : !this.showUserHelper,
                });
            },
            setCoords() {
                let attempts = 0;
                const numAttemptsToSearch = 2;
                const timeInterval = 30;

                const setCoords = () => {
                    const { offsetHeight: elHeight, offsetWidth: elWidth } =
                        this.userHelperCalledBtn;
                    const { top, left } = getCoords(this.userHelperCalledBtn, true);

                    this.helperCoords = {
                        right: `${document.body.offsetWidth - left - elWidth}px`,
                        top: `${top + elHeight + 10}px`,
                        bottom: 'auto',
                    };
                };
                return new Promise(resolve => {
                    if (this.userHelperCalledBtn) {
                        setCoords();
                        resolve();
                    }
                    const interval = setInterval(() => {
                        if (!this.userHelperCalledBtn && attempts <= numAttemptsToSearch) {
                            attempts += 1;
                        } else if (this.userHelperCalledBtn) {
                            setCoords();
                            clearInterval(interval);
                            resolve();
                        } else {
                            this.helperCoords = undefined;
                            clearInterval(interval);
                            resolve();
                        }
                    }, timeInterval);
                });

                /* eslint-disable */
            },
            onClickOutside(e) {
                const btn = document.querySelector('.se-helper__btn');

                if (btn && btn.contains(e.target)) {
                    return;
                }

                this.openModal(false);
            },
            openChat() {
                this.openModal(false);
                this.btxBtn.click();
            },
            openFaq() {
                this.$router.push({ name: 'faq', query: { selection: this.faqTab } });
            },
            openStudy() {
                this.$store.commit('onBoarding/setFirstStepOnPage', this.$route.name);
                this.$store.commit('onBoarding/setField', { field: 'activateOb', value: true });
            },
            actionClickMenuItem(handler) {
                const actions = {
                    faq: this.openFaq,
                    mess: this.openChat,
                    study: this.openStudy,
                    call: () => {
                        this.showCallBack = true;
                    },
                };

                if (actions[handler]) {
                    actions[handler]();
                }
                this.openModal(false);
            },
        },
    };
</script>

<style lang="scss" scoped>
    .se-helper {
        &__modal {
            position: fixed;
            z-index: 10;
            overflow: hidden;
            min-width: 250px;
            border-radius: $menu-content-border-radius;
            background: #fff;
            box-shadow: 0 4px 64px rgba(0, 0, 0, 0.22);
            font-size: 16px;

            &_place_br {
                right: 50px;
                bottom: 126px;
            }
        }

        &__btn {
            position: fixed;
            z-index: 9;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            border-radius: 8px;
            background: #607fea;
            cursor: pointer;
            box-shadow: 0 4px 64px rgba(0, 0, 0, 0.46);

            &_place_br {
                right: 50px;
                bottom: 50px;
            }
        }

        &__video {
            overflow: hidden;
            margin: 8px 8px 0 8px;
            border-radius: 8px;
        }

        &__video-iframe {
            border: none;
        }

        &__list-item {
            display: flex;
            align-items: center;
            padding: 18px;
            border-bottom: 1px solid #dfdfdf;
            cursor: pointer;

            &:last-of-type {
                border-bottom: none;
            }
        }

        &__icon {
            width: 24px;
            height: 24px;
            margin-right: 6px;
        }

        &__btn-icon {
            width: 32px;
            height: 32px;
        }
    }
</style>
