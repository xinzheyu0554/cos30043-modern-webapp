<script setup>
import { computed, watch } from "vue";
import { RouterView, useRoute } from "vue-router";
import AppFooter from "./components/AppFooter.vue";
import AppHero from "./components/AppHero.vue";
import AppNavbar from "./components/AppNavbar.vue";
import { theme } from "./state/theme";

const route = useRoute();

const pageLabel = computed(() => route.meta.label || "MyWay");

watch(
  [pageLabel, theme],
  ([label]) => {
    document.title = label === "Welcome" ? "MyWay" : `MyWay | ${label}`;
  },
  { immediate: true }
);
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
