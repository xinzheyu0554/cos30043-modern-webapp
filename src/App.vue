<script setup>
import { computed } from "vue";
import { RouterView, useRoute } from "vue-router";
import AppFooter from "./components/AppFooter.vue";
import AppHero from "./components/AppHero.vue";
import AppNavbar from "./components/AppNavbar.vue";

const route = useRoute();

const pageLabel = computed(() => route.meta.label || "Modern Web App");
</script>

<template>
  <div class="app-shell">
    <header class="site-header">
      <AppNavbar />
      <AppHero :page-label="pageLabel" />
    </header>

    <main class="page-frame">
      <div class="container">
        <RouterView />
      </div>
    </main>

    <AppFooter />
  </div>
</template>
