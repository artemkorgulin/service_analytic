<template>
    <component :is="component" v-bind="attributes" @close="close" />
</template>

<script>
    import { isUnset, isSet } from '~utils/helpers';
    import { consoleWarn } from '~utils/console';
    import preventScroll from '~mixins/prevent-scroll';

    export default {
        name: 'TheModal',
        mixins: [preventScroll],
        data() {
            return {
                component: null,
                attributes: null,
                preventScroll: true,
                preventedScroll: undefined,
                pageKey: 2,
            };
        },
        beforeMount() {
            this.$emitter.on('openModal', this.open);
            this.$emitter.on('closeModal', this.close);
        },

        beforeDestroy() {
            this.$emitter.off('openModal', this.open);
            this.$emitter.off('closeModal', this.close);
        },

        methods: {
            async open(options) {
                if (this?.component) {
                    await this.close();
                }
                const component = options?.component;
                const attributes = options?.attrs;
                if (isUnset(component)) {
                    consoleWarn('[TheModal open] component is required', this.$el);
                    return;
                }
                if (isSet(options?.preventScroll)) {
                    this.preventScroll = options.preventScroll;
                }
                if (this.preventScroll) {
                    this.__preventScroll(true);
                }

                this.component = component;
                this.attributes = attributes;
            },

            close() {
                this.component = null;
                this.attributes = null;
                this.preventScroll = true;
                this.__preventScroll(false);
            },
        },
    };
</script>
