<script>
    import { VDialog } from 'vuetify/lib';
    export default {
        name: 'BaseDialog',
        extends: VDialog,
        methods: {
            handleAfterLeave() {
                // this.$emit('leaved');
                return this.$modal.close();
            },
            genTransition() {
                const content = this.genInnerContent();

                if (!this.transition) {
                    return content;
                }

                return this.$createElement(
                    'transition',
                    {
                        props: {
                            name: this.transition,
                            origin: this.origin,
                            appear: true,
                        },
                        on: {
                            afterLeave: this.handleAfterLeave,
                        },
                    },
                    [content]
                );
            },
        },
    };
</script>
