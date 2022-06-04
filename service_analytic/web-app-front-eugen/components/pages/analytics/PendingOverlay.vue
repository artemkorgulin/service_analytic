<template>
    <div>
        <VProgressCircular indeterminate :size="circleSize" color="accent" />
    </div>
</template>

<script>
    export default {
        name: 'PendingOverlay',
        props: {
            circleSize: {
                type: Number,
                default: 100,
            }
        }
    };
</script>
