<template>
    <div class="se-page-tabs se-page-tabs--lg">
        <div ref="roll" class="se-page-tabs__rolls"></div>
        <ul ref="menuItems" class="se-page-tabs__menu">
            <li
                v-for="(item, index) in items"
                :key="item.title"
                class="se-page-tabs__item"
                :class="{ active: activeItemIndex === index, 'error-item': item.error, 'disabled-item': isItemDisabled(index) }"
                @click="setMenuActiveMenu(index)"
            >
                {{ item.title }}
                <div v-if="item.error" class="se-notif-badge ml-2 mr-1"></div>
            </li>
        </ul>
    </div>
</template>

<script>
    /* eslint-disable */
    export default {
        props: {
            value: {
                type: Number,
                default: 0,
            },
            items: {
                type: Array,
                default: () => [],
            },
            disableByIndex: {
                type: Array,
                default: () => [],
            }
        },
        data() {
            return {
                signals: [],
                thereAreChanges: false,
                roll: undefined,
                rollWidth: 0,
                activeItemIndex: 1,
                standardAnimateDuration: 150,
                toRight: false,
                reduceRoll: false,
                diffBetweenItems: 0,
                rollOl: 0,
                targetLeft: 0,
                nextIndex: undefined,
            };
        },
        mounted() {
            if (!this.items.length) {
                console.warn('se-menu-tabs does not contain any item');
                return;
            }
            this.setSignals();

            const { menuItems, roll } = this.$refs;
            this.roll = roll;
            this.menuElements = Array.from(menuItems.children);

            this.activeItemIndex = this.value;
            this.init(this.value);
        },
        watch: {
            value(val) {
                this.activeItemIndex = val;
                this.init(val);
            },
        },
        methods: {
            setSignals() {
                this.signals = this.items.map(({ error }) => Boolean(error));
            },
            init(i) {
                this.roll.style.width = `${this.menuElements[i].offsetWidth}px`;
                this.roll.style.height = `${this.menuElements[i].offsetHeight}px`;
                this.roll.style.left = `${this.menuElements[i].offsetLeft}px`;
            },
            setMenuActiveMenu(index) {
                this.toRight = this.activeItemIndex < index;
                const prevIndex = this.activeItemIndex;
                this.activeItemIndex = index;
                this.rollWidth = this.roll.offsetWidth;
                this.rollOl = this.roll.offsetLeft;

                const currElement = this.menuElements[index];
                const prevElement = this.menuElements[prevIndex];
                const currElWidth = currElement.offsetWidth;
                const prevElWidth = prevElement.offsetWidth;

                this.reduceRoll = currElWidth < prevElWidth;
                this.targetLeft = currElement.offsetLeft;
                this.rollOl = this.roll.offsetLeft;
                this.diffBetweenItems = Math.abs(currElWidth - prevElWidth);

                this.animate({
                    timing(timeFraction) {
                        return timeFraction;
                    },
                    draw: this.setRoll,
                });
                this.nextIndex = index;
            },
            setRoll(progress) {
                if (progress < 0) {
                    return;
                }
                const progressWidth = this.diffBetweenItems * progress;

                let rollWidth = this.rollWidth;
                if (this.reduceRoll) rollWidth -= progressWidth;
                else rollWidth += progressWidth;

                this.roll.style.width = `${rollWidth}px`;

                const targetLeft = Math.abs(this.targetLeft - this.rollOl) * progress;

                let res = this.rollOl;
                if (this.toRight) res += targetLeft;
                else res -= targetLeft;

                this.roll.style.left = `${res}px`;

                if (progress === 1) {
                    setTimeout(() => {
                        this.$emit('input', this.nextIndex);
                    }, 50);
                }
            },
            animate({ timing, draw, duration }) {
                if (!duration) duration = this.standardAnimateDuration;
                const start = performance.now();

                requestAnimationFrame(function animate(time) {
                    let timeFraction = (time - start) / duration;
                    if (timeFraction > 1) timeFraction = 1;

                    const progress = timing(timeFraction);

                    draw(progress);

                    if (timeFraction < 1) {
                        requestAnimationFrame(animate);
                    }
                });
            },
            isItemDisabled(item) {
                return Boolean(this.disableByIndex.find(el => el === item));
            },
        },
    };
</script>

<style lang="scss" scoped>
    .se-notif-badge {
        position: absolute;
        top: 0;
        right: -2px;
        animation: signal 3s ease-in-out infinite;
    }

    @keyframes signal {
        0% {
            box-shadow: 0 2px 0 rgb(255 57 128 / 0%);
            // left: 0;
        }

        50% {
            box-shadow: 0 2px 6px rgb(255 57 128 / 100%);
        }

        to {
            box-shadow: 0 2px 4px rgb(255 57 128 / 0%); // left: 300px;
        }
    }

    .se-page-tabs {
        position: relative;
        padding: 4px;
        border-radius: 16px;
        background: $background-2;

        &__rolls {
            position: absolute;
            z-index: 1;
            border-radius: 12px;
            background: #fff;
        }

        &__menu {
            display: flex;
            margin: 0;
            padding: 0;
            color: $base-700;
        }

        &__item {
            position: relative;
            z-index: 2;
            display: flex;
            margin-right: 2px;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 16px;
            transition: all ease 0.2s;
            cursor: pointer;
            user-select: none;

            &.active {
                background: none !important;

                &:hover {
                    background: none !important;
                }
            }

            &.error-item {
                background: #8b939e0d;
            }

            &.disabled-item {
                pointer-events: none;
            }

            &:hover {
                background: $background-light;
                color: black;
            }
        }
    }

    .active {
        color: black !important;
    }
</style>
