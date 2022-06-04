<template>
    <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <button v-if="$route.name.indexOf('UserEdit') !== -1" class="btn btn-primary" v-on:click="$emit('saveUser')">сохранить</button>

            <!-- Left Side Of Navbar -->
            <div class="col" style="width: 100%">
                <ul class="navbar-nav mr-auto float-start">

                </ul>
            </div>

            <!-- Right Side Of Navbar -->
            <div class="col" style="width: 100%">
                <ul class="navbar-nav ml-auto float-end">
                    <profile-button class="right"></profile-button>
                </ul>
            </div>
        </div>
    </nav>
</template>

<script>
import store from "../store";

export default {
    name: 'AppNavbar',
    data: function() {
        return {

        }
    },


    created() {
        window.navBar = this;
    }
};
</script>
