<template>

    <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 bg-dark">



        <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 text-white min-vh-100">
            <router-link :to="'/admin/dashboard'" :key="'dashboard'"
                         class="d-flex align-items-center pb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                <span class="fs-5 d-none d-sm-inline">Admin WebApp</span>
            </router-link>
            <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start"
                id="app-sidebar-menu">
                <li class="nav-item" v-for="item in menuItems">
                    <router-link :class="item.classes" :to="item.route" :key="item.key" :title="item.title" v-if="types.isEmpty(item.submenu) && item.accept">
                        <i :class="item.iconClasses"></i>
                        <span class="ms-1 d-none d-sm-inline">{{ item.name }}</span>
                    </router-link>
                    <div v-else-if="types.isNotEmpty(item.submenu) && item.accept">
                        <a :href="'#' + item.key" data-bs-toggle="collapse" :class="item.classes" :title="item.title">
                            <i :class="item.iconClasses"></i>
                            <span class="ms-1 d-none d-sm-inline">{{ item.name }}</span>
                        </a>
                        <ul class="collapse nav flex-column ms-1" :id="item.key" data-bs-parent="#app-sidebar-menu">
                            <li v-for="subItem in item.submenu">
                                <router-link :class="subItem.classes" :to="subItem.route" :key="subItem.key" :title="subItem.title" v-if="subItem.accept">
                                    <i :class="subItem.iconClasses"></i>
                                    <span class="ms-1 d-none d-sm-inline">{{ subItem.name }}</span>
                                </router-link>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
import { appTypes } from '../helpers/appTypes';

export default {
    name: 'AppSidebar',
    data() {
        return {
            menuItems: {},
            types: {}
        }
    },
    created() {
        axios.get('/admin/get-menu').then((menu) => {
            this.menuItems = menu.data.data;
        });
    },
    mounted() {
        this.types = appTypes;
    }
};
</script>
