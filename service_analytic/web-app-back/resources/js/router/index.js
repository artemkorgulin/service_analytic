import VueRouter from 'vue-router';
import store from '../store';
import UsersJournal from '../components/user/UsersJournal.vue';
import DashboardPage from '../components/DashboardPage.vue';
import Roles from "../components/roles/Roles";
import RolesCreate from "../components/roles/RolesCreate";
import UserCreate from "../components/user/UserCreate";
import UserEdit from "../components/user/UserEdit";
import GoodsOz from "../components/goods/GoodsOz";
import GoodsWb from "../components/goods/GoodsWb";
import state from "../store/state";
import AppPermissions from "../components/permissions/AppPermissions";
import BlackLIst from "../components/black_list/BlackLIst";
import BlackListEdit from "../components/black_list/BlackListEdit";
import BlackListCreate from "../components/black_list/BlackListCreate";

const ifAuthenticated = (to, from, next) => {
    if (store.getters['isAuthUser']) {
        next();
        return;
    }
    next('/admin/login');
};

const isPermission = (to, from, next) => {
    if(!state.permission){
        window.Vue.$toast.error("Нет прав", {
            position: "top-right",
            timeout: 5000,
            closeOnClick: true,
            pauseOnFocusLoss: true,
            pauseOnHover: true,
            draggable: true,
            draggablePercent: 0.6,
            showCloseButtonOnHover: false,
            hideProgressBar: true,
            closeButton: "button",
            icon: true,
            rtl: false
        });
        next('/admin/dashboard');
    }

    if(state.permission && state.permission['user.edit'] === undefined){
        window.Vue.$toast.error("Доступ закрыт", {
            position: "top-right",
            timeout: 5000,
            closeOnClick: true,
            pauseOnFocusLoss: true,
            pauseOnHover: true,
            draggable: true,
            draggablePercent: 0.6,
            showCloseButtonOnHover: false,
            hideProgressBar: true,
            closeButton: "button",
            icon: true,
            rtl: false
        });
        next('/admin/dashboard');

    }
    return false;

}

const routes = [
    {
        path: '/admin/dashboard',
        name: 'Dashboard',
        component: DashboardPage,
        meta: {
            layout: 'AppLayout'
        },
        beforeEnter: ifAuthenticated
    },
    {
        path: '/admin/users',
        name: 'Users',
        component: UsersJournal,
        meta: {
            layout: 'AppLayout'
        },
        beforeEnter: ifAuthenticated
    },

    {
        path: '/admin/roles',
        name: 'Roles',
        component: Roles,
        meta: {
            layout: 'AppLayout'
        },
        beforeEnter: (to, from, next) => {
            isPermission(to, from, next);
            ifAuthenticated(to, from, next)
        }
    },
    {
        path: '/admin/roles/create',
        name: 'RolesCreate',
        component: RolesCreate,
        meta: {
            layout: 'AppLayout'
        },
        beforeEnter: ifAuthenticated
    },
    {
        path: '/admin/permission/edit/:id',
        name: 'Permissions',
        component: AppPermissions,
        meta: {
            layout: 'AppLayout'
        },
        beforeEnter: ifAuthenticated
    },
    {
        path: '/admin/user/create',
        name: 'UserCreate',
        component: UserCreate,
        meta: {
            layout: 'AppLayout'
        },
        beforeEnter: ifAuthenticated
    },
    {
        path: '/admin/user/edit/:id',
        name: 'UserEdit',
        component: UserEdit,
        props: true,
        meta: {
            layout: 'AppLayout'
        },
        beforeEnter: ifAuthenticated
    },
    {
        path: '/admin/good-oz/:id?',
        name: 'GoodsOz',
        component: GoodsOz,
        props: true,
        meta: {
            layout: 'AppLayout'
        },
        beforeEnter: ifAuthenticated
    },
    {
        path: '/admin/good-wb/:id?',
        name: 'GoodsWb',
        component: GoodsWb,
        props: true,
        meta: {
            layout: 'AppLayout'
        },
        beforeEnter: ifAuthenticated
    },
    {
        path: '/admin/black_list',
        name: 'BlackLIst',
        component: BlackLIst,
        props: true,
        meta: {
            layout: 'AppLayout'
        },
        beforeEnter: ifAuthenticated
    },
    {
        path: '/admin/black_list/edit/:id?',
        name: 'BlackListEdit',
        component: BlackListEdit,
        props: true,
        meta: {
            layout: 'AppLayout'
        },
        beforeEnter: ifAuthenticated
    },
    {
        path: '/admin/black_list/create',
        name: 'BlackListCreate',
        component: BlackListCreate,
        props: true,
        meta: {
            layout: 'AppLayout'
        },
        beforeEnter: ifAuthenticated
    }
];

const router = new VueRouter({
    mode: 'history',
    routes
});

export default router;
