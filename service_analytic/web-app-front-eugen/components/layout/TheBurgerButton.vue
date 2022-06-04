<template>
    <button
        type="button"
        :class="[$style.TheBurgerButton, dark && $style.dark]"
        class="menu__btn"
        v-on="$listeners"
    >
        <span></span>
        <span></span>
        <span></span>
    </button>
</template>

<script>
    export default {
        name: 'TheBurgerButton',
        props: {
            dark: {
                type: Boolean,
                default: false,
            },
        },
    };
</script>

<style lang="scss" module>
    .TheBurgerButton {
        //
        &.dark {
            span {
                background-color: $base-900;
            }
        }
    }
</style>
