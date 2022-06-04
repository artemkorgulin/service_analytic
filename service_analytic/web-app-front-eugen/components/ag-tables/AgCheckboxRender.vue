<template>
    <div class="d-flex justify-center" style="width: 100%">
        <BaseCheckbox :value="Boolean(params.value)" @click="setDataValue()"></BaseCheckbox>
    </div>
</template>

<script>
    export default {
        methods: {
            setDataValue(value) {
                const colId = this.params.column.colId;
                this.params.node.setDataValue(colId, !this.params.value);
            },
        },
    };
</script>
