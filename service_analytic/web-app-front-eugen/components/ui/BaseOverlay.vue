<template>
    <div :class="$style.BaseOverlay" :style="styles" v-on="$listeners" />
</template>

<script>
    import { preventScroll } from '~mixins/prevent-scroll';

    export default {
        name: 'BaseOverlay',
        props: {
            zIndex: {
                type: [String, Number],
                default: 4,
            },
            absolute: {
                type: Boolean,
                default: false,
            },
            isPreventScroll: {
                type: Boolean,
                default: false,
            },
        },
        computed: {
            styles() {
                return {
                    'z-index': this.zIndex,
                    position: this.absolute ? 'absolute' : 'fixed',
                };
            },
        },
        beforeMount() {
            if (this.isPreventScroll) {
                preventScroll(true);
            }

            // document.documentElement.classList.add('overflow-y-hidden');
        },
        beforeDestroy() {
            if (this.isPreventScroll) {
                preventScroll(false);
            }

            // document.documentElement.classList.remove('overflow-y-hidden');
        },
        methods: {
            handleClick() {
                this.$emit('clicked');
            },
        },
    };
</script>

<style lang="scss" module>
    .BaseOverlay {
        // position: absolute;
        top: 0;
        left: 0;
        z-index: 30;
        width: 100%;
        height: 100%;
        background-color: rgba(33, 33, 33, 0.46);
        will-change: opacity;
        transition-property: opacity;
    }
</style>
