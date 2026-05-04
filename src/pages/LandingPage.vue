<script setup>
import { computed } from "vue";
import { RouterLink } from "vue-router";
import { currentUser, isAuthenticated } from "../state/session";

const quickActions = computed(() => [
  {
    title: "Browse content",
    copy: "Search, sort, and open detailed item pages from the public collection.",
    to: "/browse",
    icon: "bi-grid-1x2",
  },
  {
    title: "Create an account",
    copy: "Register to unlock favourites, comments, likes, and profile tools.",
    to: isAuthenticated.value ? "/profile" : "/register",
    icon: "bi-person-plus",
  },
  {
    title: "Manage content",
    copy: "Staff and admin users can create, edit, and delete published items.",
    to: ["admin", "adminstaff"].includes(currentUser.value?.role)
      ? "/manage-content"
      : "/support",
    icon: "bi-pencil-square",
  },
]);
</script>

<template>
  <section class="content-panel">
    <div class="card surface-card feature-hero">
      <div class="card-body p-4 p-lg-5">
        <div class="d-flex flex-wrap gap-3">
          <RouterLink to="/browse" class="btn btn-accent">
            Start browsing
          </RouterLink>
          <RouterLink
            :to="isAuthenticated ? '/profile' : '/login'"
            class="btn btn-outline-dark"
          >
            {{ isAuthenticated ? "Open your account" : "Login" }}
          </RouterLink>
        </div>
      </div>
    </div>

    <div class="row g-4 mt-1">
      <div
        v-for="action in quickActions"
        :key="action.title"
        class="col-12 col-md-6 col-xl-4"
      >
        <RouterLink :to="action.to" class="card surface-card action-card h-100">
          <div class="card-body p-4">
            <i class="bi action-icon" :class="action.icon"></i>
            <h3 class="content-card-title mt-3">{{ action.title }}</h3>
            <p class="text-muted mb-0">{{ action.copy }}</p>
          </div>
        </RouterLink>
      </div>
    </div>
  </section>
</template>
