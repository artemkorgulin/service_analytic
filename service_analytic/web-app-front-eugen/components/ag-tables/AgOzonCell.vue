<template>
    <div>{{ values }}</div>
</template>

<script>
    export default {
        data() {
            return {
                values: '',
            };
        },
        created() {
            if (this.params.value) {
                this.values = this.params.value.map(({ title }) => title).join(', ');
            }
        },
    };
</script>
