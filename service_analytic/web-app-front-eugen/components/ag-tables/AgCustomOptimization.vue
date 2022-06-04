<template>
    <div class="progress-min d-flex">
        <div
            class="progress-min__circle"
            :class="`progress-min__circle_${optimizationClass}`"
        ></div>
        <div class="progress-min__title">{{ params.value || 0 }} %</div>
    </div>
</template>

<script>
    export default {
        computed: {
            optimizationClass() {
                const opt = Math.ceil(this.params.value);
                const terms = [opt <= 84 || 'grenn', opt <= 34 || 'yellow', 'pink'];

                return terms.find(item => typeof item !== 'boolean');
            },
        },
    };
</script>
