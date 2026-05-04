<script setup>
import { onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import { apiRequest } from "../api/client";

const router = useRouter();
const favourites = ref([]);
const message = ref("");

async function loadFavourites() {
  try {
    const result = await apiRequest("favourites.php");
    favourites.value = result.data || [];
    message.value = favourites.value.length ? "" : "No favourites saved yet.";
  } catch (error) {
    message.value = error.message;
  }
}

function openContent(contentId) {
  router.push(`/content/${contentId}`);
}

onMounted(loadFavourites);
</script>

<template>
  <section class="content-panel">
    <div class="section-heading">
      <div>
        <p class="section-kicker">Saved Items</p>
        <h2 class="section-title">Your favourites</h2>
      </div>
      <p class="section-copy">
        Revisit content you have starred and jump back into the detailed page.
      </p>
    </div>

    <p v-if="message" class="alert alert-secondary">{{ message }}</p>

    <div class="row g-4">
      <div
        v-for="content in favourites"
        :key="content.contentId"
        class="col-12 col-md-6 col-xl-4"
      >
        <article class="card surface-card h-100">
          <img
            v-if="content.imageUrl"
            :src="content.imageUrl"
            class="card-img-top content-image"
            alt="content image"
          />
          <div class="card-body d-flex flex-column">
            <span class="content-chip mb-3">{{ content.category || "General" }}</span>
            <h3 class="content-card-title">{{ content.title }}</h3>
            <p class="content-subtle">By {{ content.author || "Unknown" }}</p>
            <p class="card-text flex-grow-1 content-excerpt">{{ content.body }}</p>
            <button class="btn btn-outline-dark mt-3" @click="openContent(content.contentId)">
              Open detail page
            </button>
          </div>
        </article>
      </div>
    </div>
  </section>
</template>
