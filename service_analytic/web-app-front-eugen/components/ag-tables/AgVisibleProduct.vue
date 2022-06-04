<template>
    <div class="ag-product-link">
        <v-btn v-if="visible" icon @click="clickBtn">
            <SvgIcon :name="`filled/${show ? 'eye' : 'eyeClosed'}`" />
        </v-btn>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                show: true,
                visible: true,
            };
        },
        mounted() {
            const {
                colDef: {
                    columnSetup: { mode },
                },
                rowIndex,
                value,
            } = this.params;
            this.visible = !(mode === 'copy' && rowIndex === 0);
            this.show = value;
        },
        methods: {
            clickBtn() {
                const { rowIndex } = this.params;

                this.params.clicked({ rowIndex, showRow: this.show });
                this.show = !this.show;
            },
        },
    };
</script>
