<template>
    <div v-resize="resizeOnBoarding">
        <div v-show="switchBoard" class="se-board">
            <div
                v-for="i in [1, 2, 3, 4]"
                :key="i"
                class="se-board-overflow"
                @click="clickOverflow"
            ></div>

            <div ref="seBoardHelper" class="se-board-helper"></div>
            <div ref="seBorderIntro" class="se-board-intro">
                <div
                    class="se-board-intro__dialog-tri"
                    :class="`se-board-intro__dialog-tri--${introPos}`"
                ></div>
                <v-btn icon absolute small style="top: 22px; right: 15px" @click="clickClose">
                    <SvgIcon name="outlined/close" style="height: 13px" />
                </v-btn>

                <div class="se-board-intro__content" v-html="introText"></div>
                <div v-if="!singleMode && !notTheEnd" class="se-board-intro__info">
                    <div class="se-board-intro__steps">
                        <!-- {{ stepInfo }} -->
                    </div>
                    <div v-if="!notTheEnd" class="se-board-intro__btns">
                        <v-btn
                            text
                            small
                            :disabled="disablePrev"
                            color="primary"
                            @click="prevStep()"
                        >
                            Назад
                        </v-btn>
                        <v-btn text small color="primary" @click="nextStep()">
                            {{ disableNext ? 'Завершить' : 'Дальше' }}
                        </v-btn>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    /* eslint-disable */
    import { mapState } from 'vuex';

    export default {
        data() {
            return {
                step: -1,
                introText: '',
                introPos: '',
                switchBoard: false,
                passedElements: [],
                notTheEnd: false,
                currentStep: undefined,
            };
        },
        computed: {
            ...mapState('onBoarding', ['activateOb', 'elements']),
            singleMode() {
                return this.elements?.length === 1;
            },
            disableNext() {
                return this.step + 1 === this.elements?.length;
            },
            disablePrev() {
                return this.step === 0;
            },
            stepInfo() {
                return `${this.step + 1}/${this.elements?.length}`;
            },
        },
        watch: {
            activateOb(value) {
                if (!value) return;
                if (this.elements) {
                    this.start();
                }

                this.$store.commit('onBoarding/setField', { field: 'activateOb', value: false });
            },
        },
        methods: {
            start() {
                const windowInnerWidth = document.body.offsetWidth;
                if (windowInnerWidth <= 720) {
                    return;
                }
                this.step = -1;

                this.nextStep();
            },
            clickClose() {
                this.closeOnBoarding();
                this.disableOnboard();
            },
            clickOverflow() {
                this.closeOnBoarding();
                this.disableOnboard();
            },
            closeOnBoarding() {
                this.removeShowedClass();
                this.switchBoard = false;
            },
            nextStep() {
                if (this.disableNext) {
                    this.closeOnBoarding();
                    return;
                }
                this.removeShowedClass();

                this.step += 1;

                const currentEl = this.elements[this.step];

                if (currentEl.el) {
                    this.switchBoard = true;
                }

                this.setHelper(currentEl);
            },
            prevStep() {
                if (this.step !== 0) this.step -= 1;
                this.removeShowedClass();
                this.setHelper(this.elements[this.step]);
            },
            removeShowedClass() {
                this.passedElements.forEach(element => {
                    element.classList.remove('se-onboarding-showelement');
                });
            },
            disableOnboard() {
                this.$store.commit('onBoarding/setOnboardActive', false);
            },
            clickEl() {
                this.elements[this.step].el.click();
            },
            getCoords(elem) {
                const box = elem.getBoundingClientRect();

                return {
                    top: box.top + window.pageYOffset,
                    left: box.left + window.pageXOffset,
                };
            },
            nextStepByClickElement(e) {
                this.nextStep(true);
                e.currentTarget.removeEventListener('click', this.nextStepByClickElement);
            },
            resizeOnBoarding() {
                this.currentStep && this.setHelper(this.currentStep);
            },
            setHelper({ el, pos, intro, callback, clickToNext, notTheEnd, forced }) {
                if (!this.switchBoard || !el || !this.checkVisibleEl(el)) return;

                this.currentStep = arguments[0];

                const elWidth = el.offsetWidth;
                const elHeight = el.offsetHeight;

                const { top: elTop, left: elLeft } = this.getCoords(el);
                const seBoardHelper = this.$refs.seBoardHelper;
                const seBorderIntro = this.$refs.seBorderIntro;

                const helperWidthAdd = 24;
                const helperTopSubtract = helperWidthAdd / 2;
                const helperWidth = elWidth + helperWidthAdd;
                const helperHeight = elHeight + helperWidthAdd;
                const helperTop = elTop - helperTopSubtract;
                const helperLeft = elLeft - helperTopSubtract >= 0 ? elLeft - helperTopSubtract : 0;

                seBoardHelper.style.width = `${helperWidth}px`;
                seBoardHelper.style.height = `${helperHeight}px`;
                seBoardHelper.style.top = `${helperTop}px`;
                seBoardHelper.style.left = `${helperLeft}px`;

                const positionsObj = {
                    top: {
                        isAvailable: true,
                        next: 'right',
                    },
                    right: {
                        isAvailable: true,
                        next: 'bottom',
                    },
                    bottom: {
                        isAvailable: true,
                        next: 'left',
                    },
                    left: {
                        isAvailable: true,
                        next: 'top',
                    },
                    topRight: {
                        isAvailable: true,
                        isAdaptive: true,
                        next: 'topLeft',
                    },
                    topLeft: {
                        isAvailable: true,
                        isAdaptive: true,
                        next: 'bottomRight',
                    },
                    bottomRight: {
                        isAvailable: true,
                        isAdaptive: true,
                        next: 'bottomLeft',
                    },
                    bottomLeft: {
                        isAvailable: true,
                        isAdaptive: true,
                        next: 'topRight',
                    },
                };

                seBorderIntro.style.opacity = 0;
                this.introText = intro;

                setTimeout(() => {
                    const introParams = {
                        helperWidth,
                        helperHeight,
                        helperTop,
                        helperLeft,
                        elTop,
                        introText: intro,
                        pos,
                        positionsObj,
                        notTheEnd,
                        forced,
                    };

                    this.setIntro(introParams);

                    const dimensionsParams = {
                        helperTop,
                        helperLeft,
                        helperWidth,
                        helperHeight,
                    };
                    this.setHelperOverflowDimensions(dimensionsParams);

                    el.classList.add('se-onboarding-showelement');
                    el.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center',
                    });
                    this.passedElements.push(el);
                    if (callback) callback();
                    this.notTheEnd = Boolean(notTheEnd);
                    if (clickToNext) {
                        el.addEventListener('click', this.nextStepByClickElement);
                    }
                }, 100);
            },
            setIntro({
                helperWidth,
                helperHeight,
                helperTop,
                helperLeft,
                elTop,
                introText,
                pos,
                positionsObj,
                notTheEnd,
                forced,
            }) {
                this.introPos = this.convertIntroPos(pos);
                const seBorderIntro = this.$refs.seBorderIntro;
                const introPadding = 20;

                const infoEl = document.querySelector('.se-board-intro__info');

                const getIntroTopLeft = {
                    left: (width, height) => ({
                        top: helperTop + (helperHeight - height) / 2,
                        left: helperLeft - width - introPadding,
                    }),
                    top: (width, height) => {
                        let top = helperTop - height - introPadding;
                        if (infoEl && notTheEnd) {
                            top = top + infoEl.offsetHeight;
                        }
                        return {
                            top,
                            left: helperLeft + (helperWidth - width) / 2,
                        };
                    },
                    bottom: (width, height) => ({
                        top: elTop + helperHeight,
                        left: helperLeft + (helperWidth - width) / 2,
                    }),
                    right: (width, height) => ({
                        top: helperTop + (helperHeight - height) / 2,
                        left: helperLeft + helperWidth + introPadding,
                    }),
                    topRight: (width, height) => {
                        let top = helperTop - height - introPadding;
                        if (infoEl && notTheEnd) {
                            top = top + infoEl.offsetHeight;
                        }
                        return {
                            top,
                            left: helperLeft + helperWidth - width,
                        };
                    },
                    topLeft: (width, height) => {
                        let top = helperTop - height - introPadding;
                        if (infoEl && notTheEnd) {
                            top = top + infoEl.offsetHeight;
                        }
                        return {
                            top,
                            left: helperLeft <= 0 ? 1 : helperLeft,
                        };
                    },
                    bottomRight: (width, height) => ({
                        top: elTop + helperHeight,
                        left: helperLeft + helperWidth - width,
                    }),
                    bottomLeft: (width, height) => ({
                        top: elTop + helperHeight,
                        left: helperLeft <= 0 ? 1 : helperLeft,
                    }),
                };

                let introWidth = seBorderIntro.offsetWidth;
                const introHeight = seBorderIntro.offsetHeight;

                const { top, left } = getIntroTopLeft[pos](introWidth, introHeight);

                const screenAreaParams = {
                    top,
                    left,
                    width: introWidth,
                    height: introHeight,
                };

                const isElWithinScreenArea = this.isElWithinScreenArea(screenAreaParams);

                let isAvailablePos = positionsObj[pos].isAvailable;
                const isAdaptive = positionsObj[pos].isAdaptive;
                let isSetIntro = false;

                if (!isElWithinScreenArea) {
                    if (isAvailablePos) {
                        positionsObj[pos].isAvailable = false;
                        pos = positionsObj[pos].next;
                    } else if (!isAdaptive) {
                        pos = 'topRight';
                        isAvailablePos = true;
                    }

                    if (isAvailablePos) {
                        isSetIntro = true;
                    }
                }

                if (isSetIntro && !forced) {
                    const introParams = {
                        helperWidth,
                        helperHeight,
                        helperTop,
                        helperLeft,
                        elTop,
                        introText,
                        pos,
                        positionsObj,
                        notTheEnd,
                        forced,
                    };

                    this.setIntro(introParams);
                } else {
                    seBorderIntro.style.top = `${top}px`;
                    seBorderIntro.style.left = `${left}px`;
                    seBorderIntro.style.opacity = 1;
                    seBorderIntro.style.transition = 'none';
                }
            },
            isElWithinScreenArea({ top, left, width, height }) {
                const windowInnerWidth = window.innerWidth;
                const pageHeight = document.documentElement.scrollHeight;
                const offsetRight = windowInnerWidth - left - width;
                const offsetBottom = pageHeight - top - height;

                if (top <= 0 || left <= 0 || offsetRight <= 0 || offsetBottom <= 0) return false;

                return true;
            },
            setHelperOverflowDimensions({ helperTop, helperLeft, helperWidth, helperHeight }) {
                const windowInnerWidth = window.innerWidth;
                const pageHeight = document.documentElement.scrollHeight;

                const seBoardOverflow = Array.from(
                    document.getElementsByClassName('se-board-overflow')
                );
                const coords = [
                    {
                        width: '100%',
                        height: `${helperTop}px`,
                        top: 0,
                        left: 0,
                    },
                ];
                coords.push({
                    width: `${helperLeft}px`,
                    height: `${helperHeight}px`,
                    top: coords[0].height,
                    left: 0,
                });

                coords.push({
                    width: `${windowInnerWidth - helperLeft - helperWidth}px`,
                    height: `${helperHeight}px`,
                    top: coords[0].height,
                    left: `${helperLeft + helperWidth}px`,
                });

                coords.push({
                    width: '100%',
                    height: `${pageHeight - helperHeight - helperTop}px`,
                    top: `${helperTop + helperHeight}px`,
                    left: 0,
                });

                coords.forEach((coord, index) => {
                    const item = seBoardOverflow[index];

                    Object.keys(coord).forEach(key => {
                        item.style[key] = coord[key];
                    });
                });
            },
            unCaseStr(str) {
                str = str.trim();
                str = str.replace(/-|_/g, ' ');
                str = str.replace(/[A-Z]/g, match => ` ${match.toLowerCase()}`);
                return str.trim();
            },
            convertIntroPos(pos) {
                pos = this.unCaseStr(pos);
                pos = pos.replace(/\s\w/g, match => `-${match[1]}`);
                return pos;
            },
            checkVisibleEl(el) {
                return el.offsetWidth > 0 || el.offsetHeight > 0;
            },
        },
    };
</script>

<style lang="scss" scoped>
    /*stylelint-disable */
    .se-board-overflow {
        position: absolute;
        z-index: 995;
    }

    .se-board-helper {
        position: absolute;
        border-radius: 10px;
        z-index: 995;
        box-sizing: content-box;
        pointer-events: none;
        box-shadow: rgb(33, 33, 33, 0.5) 0px 0px 0px 5000px;
    }

    .se-board-intro {
        min-width: 250px;
        max-width: 400px;
        position: absolute;
        z-index: 9999998;
        color: black;
        background: #fff;
        border-radius: 8px;

        img {
            width: 100%;
        }

        &__content {
            font-size: 14px;
            padding: 24px 50px 24px 24px;
        }

        &__info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: $base-700;
            margin-top: -4px;
            padding: 0px 24px 20px;
        }

        &__steps {
            font-size: 12px;
        }

        &__dialog-tri {
            position: absolute;
            z-index: 101;
            width: 12px;
            height: 12px;
            border: 12px solid transparent;
            border-bottom: 12px solid #fff;
            &--bottom {
                top: -22px;
                left: calc(50% - 8px);

                &-left {
                    left: 35px;
                    top: -22px;
                }

                &-right {
                    left: initial;
                    right: 35px;
                    top: -22px;
                }
            }
            &--top {
                bottom: -22px;
                transform: rotate(180deg);
                left: calc(50% - 8px);

                &-left {
                    left: 35px;
                    bottom: -22px;
                    transform: rotate(180deg);
                }

                &-right {
                    left: initial;
                    right: 35px;
                    bottom: -22px;
                    transform: rotate(180deg);
                }
            }
            &--left {
                top: calc(50% - 8px);
                right: -22px;
                transform: rotate(90deg);
            }
            &--right {
                top: calc(50% - 8px);
                left: -22px;
                transform: rotate(-90deg);
            }
        }
    }
</style>
