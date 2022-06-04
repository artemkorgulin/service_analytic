<template>
    <div>
        <v-btn icon @click="clickBtn">
            <SvgIcon :name="`filled/${showAllElementsForBulk ? 'eyeClosed' : 'eye'}`" />
        </v-btn>
    </div>
</template>

<script>
    import { mapState } from 'vuex';
    export default {
        computed: {
            ...mapState('product', ['showAllElementsForBulk']),
        },
        methods: {
            clickBtn() {
                this.params.clicked();
            },
        },
    };
</script>
