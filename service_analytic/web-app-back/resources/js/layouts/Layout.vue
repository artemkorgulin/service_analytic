<template>
    <component :is="currentLayout">
        <router-view/>
    </component>
</template>

<script>
import AppLayout from "./AppLayout";
import state from "../store/state";

export default {
    name: 'Layout',
    components: { AppLayout },
    props: {
        user: {
            type: Object,
            required: true
        },
        urlApi: {
            type: String,
            required: false
        },
        urlAdminApi: {
            type: String,
            required: false
        },
        urlVa: {
            type: String,
            required: false
        },
        token: {
            type: String,
            required: false
        },
        seller: {
            type: Array,
            required: false
        }
    },
    data() {

        return {
            layouts: {
                AppLayout
            },

        };
    },
    methods: {
        catch: function (err) {
            if (err.response) {
                this.showError(String(err.response.data.message));
            } else if (err.request) {
                this.showError(String(err.request));
            } else {
                this.showError(String(err));
            }
        },
        showError: function (err) {
            window.Vue.$toast.error(err, {
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
        },
        getPermission: function(token){
            axios.get(state.urlApi + '/v1/get-user-permissions/' + this.user.id, {headers: {Authorization: token}})
                .then((res) => {
                    let permissions = {};
                    for(const item in res.data){
                        if(res.data.hasOwnProperty(item)){
                            permissions[res.data[item].name] = true;
                        }
                    }
                    this.$store.commit('setPermission', permissions);
                })
                .catch(this.catch);
       }
    },
    computed: {
        currentLayout() {
            return this.layouts[this.$route.meta.layout];
        }

    },

    created() {
        this.$store.commit('setUrlApi', this.urlApi);
        this.$store.commit('setUrlAdminApi', this.urlAdminApi);
        this.$store.commit('setUrlApiVA', this.urlVa);
        this.$store.commit('setTokenApi', this.token);
        this.$store.commit('setCurrentUser', this.user);
        this.$store.commit('setSeller', this.seller);
        this.getPermission(this.token);
    }
};
</script>
