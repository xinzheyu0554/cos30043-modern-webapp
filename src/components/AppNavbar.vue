<script setup>
import { computed } from "vue";
import { RouterLink, useRouter } from "vue-router";
import { currentUser, isAuthenticated, logoutUser } from "../state/session";
import { isDarkMode, toggleTheme } from "../state/theme";

const router = useRouter();

const primaryLinks = computed(() => [
  { label: "Home", to: "/", show: true },
  { label: "Browse", to: "/browse", show: isAuthenticated.value },
  { label: "About", to: "/about", show: true },
  { label: "Map", to: "/map", show: isAuthenticated.value },
  { label: "Login", to: "/login", show: !isAuthenticated.value },
  { label: "Support", to: "/support", show: isAuthenticated.value },
  { label: "Groups", to: "/groups", show: isAuthenticated.value},
  { label: "Profile", to: "/profile", show: isAuthenticated.value },
  { label: "Favourites", to: "/favourites", show: isAuthenticated.value },

  {
    label: "Manage Content",
    to: "/manage-content",
    show: ["admin", "adminstaff"].includes(currentUser.value?.role),
  },
  {
    label: "Admin Users",
    to: "/admin/users",
    show: currentUser.value?.role === "admin",
  },
]);

async function handleLogout() {
  logoutUser();
  await router.push("/login");
}
</script>

<template>
  <nav class="navbar navbar-expand-lg navbar-dark shell-navbar">
    <div class="container">
      <RouterLink to="/" class="brand">
      <img src="/mywaylogo.png" alt="MyWay logo" class="navbar-logo" />
        </RouterLink>

      <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#mainNav"
        aria-controls="mainNav"
        aria-expanded="false"
        aria-label="Toggle navigation"
      >
        <span class="navbar-toggler-icon"></span>
      </button>

      <div id="mainNav" class="collapse navbar-collapse">
        <div class="navbar-nav ms-auto nav-pills-wrap">
          <RouterLink
            v-for="link in primaryLinks.filter((item) => item.show)"
            :key="link.to"
            :to="link.to"
            class="nav-link shell-link"
          >
            {{ link.label }}
          </RouterLink>

          <button
            type="button"
            class="btn btn-sm theme-toggle mt-3 mt-lg-0"
            :aria-label="isDarkMode ? 'Switch to light mode' : 'Switch to dark mode'"
            :title="isDarkMode ? 'Switch to light mode' : 'Switch to dark mode'"
            @click="toggleTheme"
          >
            <i
              class="bi"
              :class="isDarkMode ? 'bi-sun-fill' : 'bi-moon-stars-fill'"
            ></i>
          </button>

          <button
            v-if="isAuthenticated"
            type="button"
            class="btn btn-sm btn-outline-light ms-lg-2 mt-3 mt-lg-0"
            @click="handleLogout"
          >
            Logout
          </button>
        </div>
      </div>
    </div>
  </nav>
</template>
