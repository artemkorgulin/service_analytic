<script>
    import { VBtn, VProgressCircular } from 'vuetify/lib';
    // VAvatar import SvgIcon from '~/modules/icons/runtime/components/SvgIcon.vue';
    import { consoleError } from '~utils/console';
    // VIcon,
    // Components
    // import VIcon from '../../components/ui/VIcon';
    // import VBtn from '../../components/ui/VBtn';
    // import VProgressCircular from '../../components/ui/VProgressCircular';
    // import VAvatar from '../../components/ui/VAvatar';

    let uid = 0;
    const defaults = {};

    /* eslint-disable no-unused-expressions,no-use-before-define,brace-style */
    const positionList = [
        'top-left',
        'top-right',
        'bottom-left',
        'bottom-right',
        'top',
        'bottom',
        'left',
        'right',
        'center',
    ];

    const badgePositions = ['top-left', 'top-right', 'bottom-left', 'bottom-right'];

    let groups = {};
    const positionClass = {};

    export default {
        name: 'VNotifications',

        created() {
            this.notifs = {};

            positionList.forEach(pos => {
                this.notifs[pos] = [];

                const vert =
                    ['left', 'center', 'right'].includes(pos) === true
                        ? 'center'
                        : pos.includes('top')
                        ? 'top'
                        : 'bottom';
                const align = pos.includes('left')
                    ? 'start'
                    : pos.includes('right')
                    ? 'end'
                    : 'center';
                const classes = ['left', 'right'].includes(pos)
                    ? `items-${pos === 'left' ? 'start' : 'end'} justify-center`
                    : pos === 'center'
                    ? 'flex-center'
                    : `items-${align}`;

                positionClass[
                    pos
                ] = `v-notifications__list v-notifications__list--${vert} ${classes}`;
                // fixed column no-wrap
            });
        },

        methods: {
            add(config, originalApi) {
                if (!config) {
                    return consoleError('parameter required');
                }

                let Api;
                const notif = { textColor: 'white' };

                if (config.ignoreDefaults !== true) {
                    Object.assign(notif, defaults);
                }

                if (Object(config) !== config) {
                    if (notif.type) {
                        Object.assign(notif, originalApi.types[notif.type]);
                    }

                    config = { message: config };
                }

                Object.assign(notif, originalApi.types[config.type || notif.type], config);

                if (typeof notif.icon === 'function') {
                    notif.icon = notif.icon.call(this);
                }

                if (notif.spinner === undefined) {
                    notif.spinner = false;
                } else if (notif.spinner === true) {
                    notif.spinner = VProgressCircular;
                }

                notif.meta = {
                    hasMedia: Boolean(notif.spinner !== false || notif.icon || notif.avatar),
                };

                if (notif.position) {
                    if (positionList.includes(notif.position) === false) {
                        return consoleError('wrong position', config);
                    }
                } else {
                    notif.position = 'bottom';
                }

                if (notif.timeout === undefined) {
                    notif.timeout = 5000;
                } else {
                    const t = parseInt(notif.timeout, 10);
                    if (isNaN(t) || t < 0) {
                        return consoleError('wrong timeout', config);
                    }
                    notif.timeout = t;
                }

                if (notif.timeout === 0) {
                    notif.progress = false;
                } else if (notif.progress === true) {
                    notif.meta.progressStyle = {
                        animationDuration: `${notif.timeout + 1000}ms`,
                    };
                }

                const actions = (Array.isArray(config.actions) === true ? config.actions : [])
                    .concat(
                        config.ignoreDefaults !== true && Array.isArray(defaults.actions) === true
                            ? defaults.actions
                            : []
                    )
                    .concat(
                        originalApi.types[config.type] !== undefined &&
                            Array.isArray(originalApi.types[config.type].actions) === true
                            ? originalApi.types[config.type].actions
                            : []
                    );

                notif.closeBtn &&
                    actions.push({
                        label: typeof notif.closeBtn === 'string' ? notif.closeBtn : 'Закрыть',
                    });

                notif.actions = actions.map(
                    ({ handler, noDismiss, style, class: klass, attrs, ...props }) => ({
                        staticClass: klass,
                        style,
                        props: { flat: true, ...props },
                        attrs,
                        on: {
                            click:
                                typeof handler === 'function'
                                    ? () => {
                                          handler();
                                          noDismiss !== true && dismiss();
                                      }
                                    : () => {
                                          dismiss();
                                      },
                        },
                    })
                );

                if (notif.multiLine === undefined) {
                    notif.multiLine = notif.actions.length > 1;
                }

                Object.assign(notif.meta, {
                    //  row items-stretch
                    staticClass:
                        'v-notification' +
                        ` v-notification--${notif.multiLine === true ? 'multi-line' : 'standard'}` +
                        (notif.color !== undefined ? ` v-notification-${notif.color}` : '') +
                        //  +
                        // (notif.textColor !== undefined
                        //     ? ` ${notif.textColor}--text`
                        //     : '') +
                        (notif.classes !== undefined ? ` ${notif.classes}` : ''),
                    //  relative-position border-radius-inherit
                    wrapperClass:
                        'v-notification__wrapper' +
                        (notif.multiLine === true ? 'v-notification__wrapper--multiLine' : ''),
                    //  col column no-wrap justify-center row items-center
                    contentClass: 'v-notification__content',
                    //  +
                    // (notif.multiLine === true ? '' : ' col'),
                    // row items-center
                    attrs: {
                        role: 'alert',
                        ...notif.attrs,
                    },
                });

                if (notif.group === false) {
                    notif.group = undefined;
                    notif.meta.group = undefined;
                } else {
                    if (notif.group === undefined || notif.group === true) {
                        // do not replace notifications with different buttons
                        notif.group = [notif.message, notif.caption, notif.multiline]
                            .concat(notif.actions.map(a => `${a.props.label}*${a.props.icon}`))
                            .join('|');
                    }

                    notif.meta.group = notif.group + '|' + notif.position;
                }

                if (notif.actions.length === 0) {
                    notif.actions = undefined;
                } else {
                    notif.meta.actionsClass =
                        'v-notification__actions ' +
                        (notif.multiLine === true ? 'v-notification__actions--multiline' : '') +
                        (notif.meta.hasMedia === true
                            ? ' v-notification__actions--with-media'
                            : '');
                }
                //  justify-end col-auto
                const original = groups[notif.meta.group];

                // woohoo, it's a new notification
                if (original === undefined) {
                    notif.meta.uid = uid++;
                    notif.meta.badge = 1;

                    if (['left', 'right', 'center'].includes(notif.position)) {
                        this.notifs[notif.position].splice(
                            Math.floor(this.notifs[notif.position].length / 2),
                            0,
                            notif
                        );
                    } else {
                        const action = notif.position.includes('top') ? 'unshift' : 'push';
                        this.notifs[notif.position][action](notif);
                    }

                    if (notif.group !== undefined) {
                        groups[notif.meta.group] = notif;
                    }
                }
                // ok, so it's NOT a new one
                else {
                    // reset timeout if any
                    clearTimeout(original.meta.timer);

                    if (notif.badgePosition !== undefined) {
                        if (badgePositions.includes(notif.badgePosition) === false) {
                            return consoleError('wrong badgePosition', config);
                        }
                    } else {
                        notif.badgePosition = `top-${
                            notif.position.includes('left') ? 'right' : 'left'
                        }`;
                    }

                    notif.meta.uid = original.meta.uid;
                    notif.meta.badge = original.meta.badge + 1;
                    notif.meta.badgeStaticClass =
                        `v-notification__badge v-notification__badge--${notif.badgePosition}` +
                        (notif.badgeColor !== undefined ? ` ${notif.badgeColor}` : '') +
                        (notif.badgeTextColor !== undefined
                            ? ` ${notif.badgeTextColor}--text`
                            : '');

                    const index = this.notifs[notif.position].indexOf(original);
                    this.notifs[notif.position][index] = groups[notif.meta.group] = notif;
                }
                // }

                const dismiss = () => {
                    this.remove(notif);
                    Api = undefined;
                };

                this.$forceUpdate();

                if (notif.timeout > 0) {
                    notif.meta.timer = setTimeout(() => {
                        dismiss();
                    }, notif.timeout + /* show duration */ 1000);
                }

                // only non-groupable can be updated
                if (notif.group !== undefined) {
                    return props => {
                        if (props !== undefined) {
                            consoleError(
                                'trying to update a grouped one which is forbidden',
                                config
                            );
                        } else {
                            dismiss();
                        }
                    };
                }

                Api = {
                    dismiss,
                    config,
                    notif,
                };

                if (originalApi !== undefined) {
                    Object.assign(originalApi, Api);
                    return;
                }

                return props => {
                    // if notification wasn't previously dismissed
                    if (Api !== undefined) {
                        // if no params, then we must dismiss the notification
                        if (props === undefined) {
                            Api.dismiss();
                        }
                        // otherwise we're updating it
                        else {
                            const newNotif = Object.assign({}, Api.config, props, {
                                group: false,
                                position: notif.position,
                            });

                            this.add(newNotif, Api);
                        }
                    }
                };
            },

            remove(notif) {
                clearTimeout(notif.meta.timer);

                const index = this.notifs[notif.position].indexOf(notif);
                if (index !== -1) {
                    if (notif.group !== undefined) {
                        delete groups[notif.meta.group];
                    }

                    const el = this.$refs[`notif_${notif.meta.uid}`];

                    if (el) {
                        const { width, height } = getComputedStyle(el);

                        el.style.left = `${el.offsetLeft}px`;
                        el.style.width = width;
                        el.style.height = height;
                    }

                    this.notifs[notif.position].splice(index, 1);

                    this.$forceUpdate();

                    if (typeof notif.onDismiss === 'function') {
                        notif.onDismiss();
                    }
                }
            },

            closeAll() {
                Object.values(this.notifs).forEach(el => {
                    el.splice(0);
                });
                groups = {};
                this.$forceUpdate();
            },
        },

        render(h) {
            return h(
                'div',
                { staticClass: 'v-notifications' },
                positionList.map(pos =>
                    h(
                        'transition-group',
                        {
                            key: pos,
                            staticClass: positionClass[pos],
                            tag: 'div',
                            props: {
                                name: `v-notification--${pos}`,
                                mode: 'out-in',
                            },
                        },
                        this.notifs[pos].map(notif => {
                            let msgChild;

                            const meta = notif.meta;
                            const msgData = {
                                staticClass: 'v-notification__message col',
                            };

                            if (notif.html === true) {
                                msgData.domProps = {
                                    innerHTML: notif.caption
                                        ? `<div>${notif.message}</div><div class="v-notification__caption">${notif.caption}</div>`
                                        : notif.message,
                                };
                            } else {
                                const msgNode = [notif.message];
                                msgChild = notif.caption
                                    ? [
                                          h('div', msgNode),
                                          h(
                                              'div',
                                              {
                                                  staticClass: 'v-notification__caption',
                                              },
                                              [notif.caption]
                                          ),
                                      ]
                                    : msgNode;
                            }

                            const mainChild = [];

                            // if (meta.hasMedia === true) {
                            //     if (notif.spinner !== false) {
                            //         mainChild.push(h(notif.spinner, {
                            //             staticClass: 'v-notification__spinner',
                            //             props: { indeterminate: true },
                            //         }));
                            //     } else if (notif.icon) {
                            //         mainChild.push(h(SvgIcon, {
                            //             staticClass: 'v-notification__icon',
                            //             attrs: { role: 'img' },
                            //             props: { name: notif.icon },
                            //         }));
                            //     } else if (notif.avatar) {
                            //         mainChild.push(h(VAvatar, { props: { size: 36 }, staticClass: 'v-notification__avatar' }, [
                            //             h('img', { attrs: { src: notif.avatar, 'aria-hidden': 'true' } }),
                            //         ]));
                            //     }
                            // }
                            // <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" role="img" aria-hidden="true" class="v-icon__svg"><path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z"></path></svg>
                            const closeIcon = h(
                                'svg',
                                {
                                    attrs: {
                                        viewBox: '0 0 24 24',
                                        role: 'img',
                                        'aria-hidden': true,
                                    },
                                },
                                [
                                    h('path', {
                                        attrs: {
                                            fill: 'currentColor',
                                            d: 'M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z',
                                        },
                                    }),
                                ]
                            );

                            mainChild.push(h('div', msgData, msgChild));

                            const child = [h('div', { staticClass: meta.contentClass }, mainChild)];

                            notif.progress === true &&
                                child.push(
                                    h('div', {
                                        key: `${meta.uid}|p|${meta.badge}`,
                                        staticClass: 'v-notification__progress',
                                        style: meta.progressStyle,
                                        class: notif.progressClass,
                                    })
                                );

                            notif.actions !== undefined &&
                                child.push(
                                    h(
                                        'div',
                                        {
                                            staticClass: meta.actionsClass,
                                        },
                                        notif.actions.map(action =>
                                            h(VBtn, { ...action }, [closeIcon])
                                        )
                                    )
                                );
                            // [h(VIcon, ['close'])]
                            meta.badge > 1 &&
                                child.push(
                                    h(
                                        'div',
                                        {
                                            key: `${meta.uid}|${meta.badge}`,
                                            staticClass: meta.badgeStaticClass,
                                            style: notif.badgeStyle,
                                            class: notif.badgeClass,
                                        },
                                        [meta.badge]
                                    )
                                );

                            return h(
                                'div',
                                {
                                    ref: `notif_${meta.uid}`,
                                    key: meta.uid,
                                    staticClass: meta.staticClass,
                                    attrs: meta.attrs,
                                },
                                [h('div', { staticClass: meta.wrapperClass }, child)]
                            );
                        })
                    )
                )
            );
        },
    };
</script>
<style lang="scss">
    $z-notify: 9500 !default;
    $generic-border-radius: 16px !default;
    $ios-statusbar-height: 20px !default;
    $primary: #1976d2 !default;
    $shadow-color: #000 !default;
    $negative: #c10015 !default;

    $elevation-umbra: rgba($shadow-color, 0.2) !default;
    $elevation-penumbra: rgba($shadow-color, 0.14) !default;
    $elevation-ambient: rgba($shadow-color, 0.12) !default;
    $shadow-2: 0 1px 5px $elevation-umbra, 0 2px 2px $elevation-penumbra,
        0 3px 1px -2px $elevation-ambient !default;
    $shadow-1: 0 1px 3px $elevation-umbra, 0 1px 1px $elevation-penumbra,
        0 2px 1px -1px $elevation-ambient !default;
    $breakpoint-xs: 599px !default;
    $breakpoint-sm: 1023px !default;
    $breakpoint-md: 1439px !default;
    $breakpoint-lg: 1919px !default;
    $sizes: (
        'xs': 0,
        'sm': (
            $breakpoint-xs + 1,
        ),
        'md': (
            $breakpoint-sm + 1,
        ),
        'lg': (
            $breakpoint-md + 1,
        ),
        'xl': (
            $breakpoint-lg + 1,
        ),
    ) !default;

    $breakpoint-xs-min: 0 !default;
    $breakpoint-xs-max: $breakpoint-xs !default;

    $breakpoint-sm-min: map-get($sizes, 'sm') !default;
    $breakpoint-sm-max: $breakpoint-sm !default;

    /* stylelint-disable declaration-no-important */
    .fixed {
        position: fixed;
    }

    .fixed,
    .fixed-full,
    .fullscreen,
    .fixed-center,
    .fixed-bottom,
    .fixed-left,
    .fixed-right,
    .fixed-top,
    .fixed-top-left,
    .fixed-top-right,
    .fixed-bottom-left,
    .fixed-bottom-right {
        position: fixed !important;
    }

    // .row,
    // .column,
    // .flex {
    //     display: flex;
    //     flex-wrap: wrap;

    //     &.inline {
    //         display: inline-flex;
    //     }
    // }

    // .row.reverse {
    //     flex-direction: row-reverse;
    // }

    // .column {
    //     flex-direction: column;

    //     &.reverse {
    //         flex-direction: column-reverse;
    //     }
    // }

    // .wrap {
    //     flex-wrap: wrap;
    // }

    // .no-wrap {
    //     flex-wrap: nowrap;
    // }

    // .reverse-wrap {
    //     flex-wrap: wrap-reverse;
    // }

    // .justify- {
    //     &start {
    //         justify-content: flex-start;
    //     }

    //     &end {
    //         justify-content: flex-end;
    //     }

    //     &center {
    //         justify-content: center;
    //     }

    //     &between {
    //         justify-content: space-between;
    //     }

    //     &around {
    //         justify-content: space-around;
    //     }

    //     &evenly {
    //         justify-content: space-evenly;
    //     }
    // }

    .items- {
        &start {
            align-items: flex-start;
        }

        &end {
            align-items: flex-end;

            .v-notification {
                margin-right: 0;
            }
        }

        &center {
            align-items: center;
        }

        &baseline {
            align-items: baseline;
        }

        &stretch {
            align-items: stretch;
        }
    }

    // .content- {
    //     &start {
    //         align-content: flex-start;
    //     }

    //     &end {
    //         align-content: flex-end;
    //     }

    //     &center {
    //         align-content: center;
    //     }

    //     &stretch {
    //         align-content: stretch;
    //     }

    //     &between {
    //         align-content: space-between;
    //     }

    //     &around {
    //         align-content: space-around;
    //     }
    // }

    // .col,
    // .col-xs {
    //     flex: 10000 1 0%;
    // }

    // .col-auto {
    //     width: auto;
    //     min-width: 0;
    //     max-width: 100%;
    // }

    .v-notifications__list {
        position: fixed;
        // position: relative;
        right: var(--gap, 0);
        left: var(--gap, 0);
        z-index: $z-notify;
        display: flex;
        // margin-bottom: 10px;
        flex-wrap: nowrap;
        flex-direction: column;
        pointer-events: none;

        &--center {
            top: 0;
            bottom: 0;
        }

        &--top {
            top: var(--header-h, 0);
        }

        &--bottom {
            bottom: 0;
        }
    }

    // body.v-ios-padding .v-notifications__list {
    //     &--center,
    //     &--top {
    //         top: $ios-statusbar-height;
    //         top: env(safe-area-inset-top);
    //     }

    //     &--center,
    //     &--bottom {
    //         bottom: env(safe-area-inset-bottom);
    //     }
    // }

    .v-notification {
        z-index: $z-notify;
        // overflow: hidden;
        display: inline-flex;
        flex-shrink: 0;
        max-width: 95vw;
        margin: 10px 10px 0;
        border-radius: $generic-border-radius;
        background: #323232;
        font-family: $body-font-family;
        font-size: 16px;
        color: #fff;
        transition: transform 1s, opacity 1s;
        box-shadow: $shadow-2;
        pointer-events: all;

        &__icon {
            flex: 0 0 1em;
            margin-right: 10px;
            // font-size: 24px;
        }

        &__avatar {
            margin-right: 8px;
            font-size: 32px;
        }

        &__spinner {
            margin-right: 8px;
            // font-size: 32px;
        }

        &__message {
            padding: 10px 0;
        }

        &__caption {
            font-size: 0.9em;
            opacity: 0.7;
        }

        &__actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            width: 40px;
            color: $primary;
            // color: var(--VColor-primary);
        }

        &__badge {
            position: absolute;
            padding: 4px 7px;
            border-radius: 9999px;
            // background: $negative;
            background-color: $negative;
            // background-color: var(--VColor-negative);
            font-size: 12px;
            line-height: 12px;
            color: #fff;
            animation: v-notif-badge 0.42s;
            box-shadow: $shadow-1; //$generic-border-radius;

            &--top-left,
            &--top-right {
                top: -6px;
            }

            &--bottom-left,
            &--bottom-right {
                bottom: -6px;
            }

            &--top-left,
            &--bottom-left {
                left: -22px;
            }

            &--top-right,
            &--bottom-right {
                right: -22px;
            }
        }

        &__progress {
            position: absolute;
            right: -10px;
            bottom: 0;
            left: -10px;
            height: 3px;
            border-radius: $generic-border-radius $generic-border-radius 0 0;
            background: currentColor;
            opacity: 0.3;
            transform: scaleX(0);
            transform-origin: 0 50%;
            animation: v-notif-progress linear;
        }

        &__content {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
        }

        &__wrapper {
            position: relative;
            display: flex;
            // flex-wrap: wrap;
            border-radius: inherit;
        }

        &--standard {
            min-height: 48px;
            // padding: 0 16px;
            padding-left: 16px;

            .v-notification__actions {
                width: 40px;
                margin-left: 16px;
                // margin-right: -8px;
                // padding: 6px 0 6px 8px;
            }
        }

        &--multi-line {
            min-height: 68px;
            padding: 8px 16px;

            .v-notification__badge {
                &--top-left,
                &--top-right {
                    top: -15px;
                }

                &--bottom-left,
                &--bottom-right {
                    bottom: -15px;
                }
            }

            .v-notification__progress {
                bottom: -8px;
            }

            .v-notification__actions {
                justify-content: flex-end;
                padding: 0;

                &--with-media {
                    padding-left: 25px;
                }
            }
        }

        &--top-left-enter,
        &--top-left-leave-to,
        &--top-enter,
        &--top-leave-to,
        &--top-right-enter,
        &--top-right-leave-to {
            z-index: ($z-notify - 1);
            opacity: 0;
            transform: translateY(-50px);
        }

        &--left-enter,
        &--left-leave-to,
        &--center-enter,
        &--center-leave-to,
        &--right-enter,
        &--right-leave-to {
            z-index: ($z-notify - 1);
            opacity: 0;
            transform: rotateX(90deg);
        }

        &--bottom-left-enter,
        &--bottom-left-leave-to,
        &--bottom-enter,
        &--bottom-leave-to,
        &--bottom-right-enter,
        &--bottom-right-leave-to {
            z-index: ($z-notify - 1);
            opacity: 0;
            transform: translateY(50px);
        }

        &--top-left-leave-active,
        &--top-leave-active,
        &--top-right-leave-active,
        &--left-leave-active,
        &--center-leave-active,
        &--right-leave-active,
        &--bottom-left-leave-active,
        &--bottom-leave-active,
        &--bottom-right-leave-active {
            position: absolute;
            z-index: ($z-notify - 1);
            margin-right: 0;
            margin-left: 0;
        }

        &--top-leave-active,
        &--center-leave-active {
            top: 0;
        }

        &--bottom-left-leave-active,
        &--bottom-leave-active,
        &--bottom-right-leave-active {
            bottom: 0;
        }
    }

    @media (min-width: $breakpoint-sm-min) {
        .v-notification {
            max-width: 65vw;
        }
    }

    @keyframes v-notif-badge {
        15% {
            transform: translate3d(-25%, 0, 0) rotate3d(0, 0, 1, -5deg);
        }

        30% {
            transform: translate3d(20%, 0, 0) rotate3d(0, 0, 1, 3deg);
        }

        45% {
            transform: translate3d(-15%, 0, 0) rotate3d(0, 0, 1, -3deg);
        }

        60% {
            transform: translate3d(10%, 0, 0) rotate3d(0, 0, 1, 2deg);
        }

        75% {
            transform: translate3d(-5%, 0, 0) rotate3d(0, 0, 1, -1deg);
        }
    }

    @keyframes v-notif-progress {
        0% {
            transform: scaleX(1);
        }

        100% {
            transform: scaleX(0);
        }
    }
</style>
