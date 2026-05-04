<script setup>
import { ref } from "vue";
import { getStoredUser, clearAuth } from "./api/client";

import LoginPage from "./pages/LoginPage.vue";
import RegisterPage from "./pages/RegisterPage.vue";
import HomePage from "./pages/HomePage.vue";
import ContentDetailPage from "./pages/ContentDetailPage.vue";
import ProfilePage from "./pages/ProfilePage.vue";
import FavouritesPage from "./pages/FavouritesPage.vue";
import AdminUsersPage from "./pages/AdminUsersPage.vue";
import ManageContentPage from "./pages/ManageContentPage.vue";

const user = ref(getStoredUser());
const page = ref(user.value ? "home" : "login");
const selectedContentId = ref(null);

function handleAuth(newUser) {
  user.value = newUser;
  page.value = "home";
}

function logout() {
  clearAuth();
  user.value = null;
  selectedContentId.value = null;
  page.value = "login";
}

function openContent(contentId) {
  selectedContentId.value = contentId;
  page.value = "contentDetail";
}
</script>

<template>
  <div>
    <h1>Modern Web App</h1>

    <nav>
      <button @click="page = 'home'">Home</button>

      <template v-if="user">
        <button @click="page = 'profile'">Profile</button>
        <button @click="page = 'favourites'">Favourites</button>

        <button
          v-if="user.role === 'admin' || user.role === 'adminstaff'"
          @click="page = 'manageContent'"
        >
          Manage Content
        </button>

        <button v-if="user.role === 'admin'" @click="page = 'adminUsers'">
          Admin Users
        </button>

        <button @click="logout">Logout</button>
      </template>

      <template v-else>
        <button @click="page = 'login'">Login</button>
        <button @click="page = 'register'">Register</button>
      </template>
    </nav>

    <hr />

    <LoginPage v-if="page === 'login'" @login-success="handleAuth" />
    <RegisterPage v-if="page === 'register'" @register-success="handleAuth" />
    <HomePage v-if="page === 'home'" @open-content="openContent" />
    <ContentDetailPage
      v-if="page === 'contentDetail'"
      :content-id="selectedContentId"
      :user="user"
      @back="page = 'home'"
    />
    <ProfilePage v-if="page === 'profile'" />
    <FavouritesPage v-if="page === 'favourites'" @open-content="openContent" />
    <AdminUsersPage v-if="page === 'adminUsers'" />
    <ManageContentPage v-if="page === 'manageContent'" />
  </div>
</template>