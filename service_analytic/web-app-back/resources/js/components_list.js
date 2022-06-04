// Mount layouts
Vue.component('Layout', require('./layouts/Layout.vue').default);
Vue.component('AppLayout', require('./layouts/AppLayout.vue').default);

// Mount components
Vue.component('BaseLoader', require('./components/BaseLoader.vue').default);
Vue.component('DashboardPage', require('./components/DashboardPage.vue').default);
Vue.component('JournalPagination', require('./components/JournalPagination.vue').default);
Vue.component('ProfileButton', require('./components/user/ProfileButton.vue').default);
Vue.component('UserForm', require('./components/user/UserForm.vue').default);
Vue.component('UsersJournal', require('./components/user/UsersJournal.vue').default);
Vue.component('AppSidebar', require('./components/AppSidebar.vue').default);
Vue.component('AppNavbar', require('./components/AppNavbar.vue').default);







