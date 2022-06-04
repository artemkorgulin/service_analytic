<template>
    <div :class="[$style.Wrapper, { [$style.WrapperSingle]: items.length < 2 }]">
        <div v-if="items.length > 1" v-ripple :class="$style.Arrow" @click="slideMinus">
            <SvgIcon name="outlined/chevronBack" />
        </div>
        <v-carousel
            v-model="activeOption"
            hide-delimiters
            :show-arrows="false"
            height="40"
            @change="handleSlideChange($event)"
        >
            <v-carousel-item v-for="item in items" :key="item[dataKey]">
                <span :class="$style.Slide" v-html="item[displayKey]" />
            </v-carousel-item>
        </v-carousel>
        <div v-if="items.length > 1" v-ripple :class="$style.Arrow" @click="slidePlus">
            <SvgIcon name="outlined/chevronNext" />
        </div>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex';

    export default {
        name: 'CarouselSwitcher',
        props: {
            items: {
                type: Array,
                required: true,
            },
            displayKey: {
                type: String,
                default: 'name',
            },
            dataKey: {
                type: String,
                default: 'id',
            },
        },
        data() {
            return {
                model: 0,
            };
        },
        computed: {
            ...mapGetters({
                activeOptionIndex: 'product/getActiveOptionIndex',
            }),
            activeOption: {
                get() {
                    return this.activeOptionIndex;
                },
                set(val) {
                    this.setActiveOptionIndex(val);
                },
            },
        },
        watch: {
            model() {
                this.handleSlideChange();
            },
        },
        methods: {
            ...mapActions({
                setActiveOptionIndex: 'product/setActiveOptionIndex',
            }),
            slideMinus() {
                if (this.activeOption <= 0) {
                    this.setActiveOptionIndex(this.items.length - 1);
                } else {
                    this.setActiveOptionIndex(this.activeOption - 1);
                }
            },
            slidePlus() {
                if (this.activeOption >= this.items.length - 1) {
                    this.setActiveOptionIndex(0);
                    this.model = 0;
                } else {
                    this.setActiveOptionIndex(this.activeOption + 1);
                }
            },
            handleSlideChange() {
                this.$emit('optionChange', this.model);
            },
        },
    };
</script>

<style lang="scss" module>
    .Wrapper {
        display: grid;
        grid-template-columns: 2.5rem auto 2.5rem;
        grid-template-rows: none;
        width: 17rem;
        border-radius: 0.5rem;
        border: 1px $color-gray-blue-light solid;

        &.WrapperSingle {
            grid-template-columns: auto;
            overflow: hidden;
        }
    }

    .Arrow {
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .Slide {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        background-color: $color-main-background;
    }
</style>
