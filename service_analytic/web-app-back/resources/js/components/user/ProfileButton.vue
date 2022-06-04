<template>
    <li class="nav-item dropdown ">
          <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
             data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="fas fa-user-circle fa-2x"></i>
              {{ $store.getters.currentUser.name }}
          </a>

          <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
              <a class="dropdown-item" href="#" v-on:click="logout()">Выход</a>
          </div>
      </li>
</template>

<script>
export default {
    name: "ProfileButton",
    methods: {
        async logout() {
            axios.post('/admin/logout').then((response) => {
                window.open('/admin/login');
            });
        }
    }
}
</script>
