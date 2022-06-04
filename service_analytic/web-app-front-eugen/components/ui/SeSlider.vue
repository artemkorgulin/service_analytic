<template>
    <div class="se-slider">
        <div ref="sliderWrapper" class="se-slider__wrapper">
            <div class="se-slider__track-container">
                <div class="se-slider__track-filled" :style="filledTrackStyle"/>
            </div>
            <div class="se-slider__ticks-container">
            <span v-for="(tick, index) in ticksProcessed"
                  :key="index"
                  class="tick"
                  :style="tick.style"
            >
            <span class="tick__body" :class="{ 'tick__body_filled': tick.filled }" />
        </span>
            </div>
            <div ref="pointerTrack" class="se-slider__pointer-container">
                <span ref="pointer" class="pointer" :style="pointerStyle">
                    <span class="pointer__body" :class="{ 'pointer__body_active': isFocused }" />
                </span>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'SeSlider',
        props: {
            value: {
                type: [Number, String],
                default: 0,
            },
            max: {
                type: [Number, String],
                default: 10000,
            },
            min: {
                type: [Number, String],
                default: 1,
            },
            ticksCount: {
                type: Number,
                default: 3,
            },
        },
        data: () => ({
            isFocused: false,
            pointerLeft: 0,
        }),
        computed: {
            ticksProcessed() {
                const { max } = this;
                const arr = [];
                for (let i = 0; i <= this.ticksCount; i++) {
                    const value = Math.round(max / this.ticksCount * i);
                    arr.push({
                        value,
                        filled: this.pointerPositionAbsolute >= value,
                        style: this.getTickStyle(i, this.ticksCount),
                    });
                }
                return arr;
            },
            pointerParentBounds() {
                const {top, right, bottom, left, width, height, x, y} = this.$refs.pointerTrack.getBoundingClientRect();
                return {top, right, bottom, left, width, height, x, y};
            },
            pointerStyle() {
                return {
                    left: `${this.pointerLeft}%`,
                };
            },
            pointerPositionAbsolute() {
                return this.pointerLeft * this.max / 100;
            },
            pointerSmallestPositionPercentage() {
                return this.min * 100 / this.max;
            },
            filledTrackStyle() {
                return {
                    width: `${this.pointerLeft}%`,
                };
            },
            emitValueTrigger() {
                 return `${typeof this.value}${this.pointerPositionAbsolute}`;
            },
        },
        watch: {
            value: {
                immediate: true,
                handler(val) {
                    this.pointerLeft = this.getPointerLeftByValue(Number(val));
                }
            },
            emitValueTrigger() {
                const value = Math.round(this.pointerPositionAbsolute);
                this.$emit('input', value);
                this.$emit('change', value);
            },
        },
        mounted() {
            this.$refs.sliderWrapper.addEventListener('mousedown', this.handleMouseDown);
            this.$refs.sliderWrapper.addEventListener('touchstart', this.handleMouseDown);
            this.$refs.sliderWrapper.addEventListener('mousemove', this.handleMouseMove);
            this.$refs.sliderWrapper.addEventListener('touchmove', this.handleMouseMove);
            window.addEventListener('mouseup', this.handleMouseUp);
            window.addEventListener('touchend', this.handleMouseUp);
        },
        beforeDestroy() {
            this.$refs.sliderWrapper.removeEventListener('mousedown', this.handleMouseDown);
            this.$refs.sliderWrapper.removeEventListener('touchstart', this.handleMouseDown);
            this.$refs.sliderWrapper.removeEventListener('mousemove', this.handleMouseMove);
            this.$refs.sliderWrapper.removeEventListener('touchmove', this.handleMouseMove);
            window.removeEventListener('mouseup', this.handleMouseUp);
            window.removeEventListener('touchend', this.handleMouseUp);
        },
        methods: {
            getTickStyle(index, count) {
                return {
                    left: `${100 / count * index}%`,
                };
            },
            getPointerLeftByPosition(e) {
                const x = e.clientX || e.touches[0].clientX;
                const leftInPercents = (x - this.pointerParentBounds.left) * 100 / this.pointerParentBounds.width;
                return this.checkValueMinMax(leftInPercents, this.pointerSmallestPositionPercentage, 100);
            },
            getPointerLeftByValue(val) {
                return this.checkValueMinMax(val, this.min, this.max) * 100 / this.max;
            },
            checkValueMinMax(value, min, max) {
                if (value < min) {
                    return min;
                } else if (value > max) {
                    return max;
                }
                return value;
            },
            handleMouseDown(e) {
                this.isFocused = true;
                this.pointerLeft = this.getPointerLeftByPosition(e);
            },
            handleMouseUp() {
                this.isFocused = false;
            },
            handleMouseMove(e) {
                if (!this.isFocused) {
                    return;
                }
                this.pointerLeft = this.getPointerLeftByPosition(e);
            },
        },
    };
</script>

<style lang='scss' scoped>
    $empty: $color-gray-light;
    $full: $primary-500;

    .se-slider {
        height: 32px;
        margin: 0 8px;

        &__wrapper {
            position: relative;
            width: 100%;
            height: 100%;
        }

        &__track-container {
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: $empty;
            transform: translateY(-50%);
        }

        &__track-filled {
            position: absolute;
            top: 50%;
            left: 0;
            height: 100%;
            background-color:  $full;
        }

        &__ticks-container,
        &__pointer-container {
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%;
            height: 0;
        }

        .tick {
            position: absolute;
            top: 0;
            width: 0;
            height: 0;

            &__body {
                position: absolute;
                top: -1px;
                left: 50%;
                width: 5px;
                height: 5px;
                border-radius: 50%;
                background-color: $empty;
                transform: translateX(-50%);

                &.tick__body_filled {
                    background-color: $full;
                }
            }
        }

        .pointer {
            position: absolute;
            top: 0;
            left: 0;
            z-index: 100;
            width: 0;
            height: 0;

            &__body {
                position: absolute;
                top: 50%;
                left: 50%;
                width: 12px;
                height: 12px;
                border-radius: 50%;
                background-color: $full;
                transform: translate(-50%, -50%);

                &:before {
                    content: "";
                    position: absolute;
                    top: -12px;
                    left: -12px;
                    width: 36px;
                    height: 36px;
                    border-radius: 50%;
                    background-color: $full;
                    color: inherit;
                    opacity: 0.3;
                    transform: scale(0.1);
                    transition: 0.3s cubic-bezier(0.25, 0.8, 0.5, 1);
                    pointer-events: none;
                }

                &:after {
                    content: "";
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    width: 42px;
                    height: 42px;
                    transform: translate(-50%, -50%);
                    transition: 0.3s cubic-bezier(0.25, 0.8, 0.5, 1);
                }

                &:hover {
                    &:before {
                        transform: scale(1);
                    }
                }

                &.pointer__body_active {
                    &.pointer__body:before {
                        transform: scale(1.5);
                    }
                }
            }
        }
    }
</style>
