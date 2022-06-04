<template>
    <NuxtLink v-ripple :to="route" :class="$style.GoBackBtn">
        {{ text }}
    </NuxtLink>
</template>

<script>
    export default {
        name: 'GoBackBtn',
        props: {
            text: {
                type: String,
                default: '',
            },
            route: {
                type: [String, Object],
                required: true,
            },
        },
    };
</script>

<style lang="scss" module>
    .GoBackBtn {
        position: relative;
        display: inline-block;
        height: 32px;
        padding: 16px 16px 16px 30px;
        border-radius: 20px;
        border: 1px solid transparent;
        background: transparent;
        text-decoration: none;
        text-decoration-style: none;
        // text-transform: uppercase;
        white-space: nowrap;
        font-size: 14px;
        line-height: 0;
        letter-spacing: 0.5px;
        color: $base-800;
        transition: ease 0.2s all;
        font-weight: 500;
        user-select: none;

        &:before,
        &:after {
            content: '';
            position: absolute;
            top: 8px;
            left: 8px;
            display: block;
            width: 16px;
            height: 16px;
            background-image: url(/goBack.svg);
            background-position: center center;
            background-repeat: no-repeat;
            transition: ease 0.3s all;
        }

        &:before {
            left: 20px;
            opacity: 0;
        }

        &:hover {
            border-color: var(--border-primary);

            &:before {
                left: 8px;
                opacity: 1;
            }

            &:after {
                left: -4px;
                opacity: 0;
            }
        }
    }
</style>
