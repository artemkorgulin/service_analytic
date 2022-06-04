<template>
    <div class="row flex-nowrap">

        <app-sidebar></app-sidebar>

        <div class="col py-3" style="padding-top: 0 !important">
            <app-navbar></app-navbar>
            <main class="py-4">
                <slot/>
            </main>
        </div>

    </div>

</template>

<script>
import AppNavbar from '../components/AppNavbar.vue';
import AppSidebar from '../components/AppSidebar.vue';
import ProfileButton from '../components/user/ProfileButton';

export default {
    name: "AppLayout",
    components: { AppNavbar, AppSidebar, ProfileButton }
}
</script>

<style scoped>

</style>
