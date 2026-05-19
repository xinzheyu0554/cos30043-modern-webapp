<script setup>
import { currentUser, isAuthenticated } from "../state/session";

defineProps({
  pageLabel: {
    type: String,
    default: "Modern Web App",
  },
});
</script>

<template>
  <section class="hero-band">
    <div class="container">
      <div class="row align-items-end g-4">
        <div :class="isAuthenticated ? 'col-12 col-lg-8' : 'col-12'">
          
          <h1 class="hero-title">{{ pageLabel }}</h1>
        </div>

        <div v-if="isAuthenticated" class="col-12 col-lg-4">
          <div class="status-card">
            <p class="status-label">Current Access</p>
            <p class="status-value">
              Hello, {{ currentUser?.username || "Signed in" }}. Welcome to MyWay!
            </p>

          </div>
        </div>

      </div>
    </div>
  </section>
</template>