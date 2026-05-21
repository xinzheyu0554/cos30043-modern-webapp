<template>
  <main class="map-page">
    <section class="map-hero">
      <p class="eyebrow">Explore destinations</p>
      <h1>Map Attraction</h1>
      <p>This feature provides a map to different attractions based on student posts.</p>
    </section>

    <section class="map-container">
      <!-- Map search and category filter -->
      <div class="search-card">
        <label>Search attractions</label>
        <input
          v-model="search"
          type="text"
          placeholder="Search by title, category, or description..."
        />

        <div class="filter-buttons">
          <button
            v-for="category in categories"
            :key="category"
            :class="{ active: selectedCategory === category }"
            @click="selectedCategory = category"
          >
            {{ category }}
          </button>
        </div>
      </div>

      <!-- User-added cards -->
      <div class="cards-grid">
        <article
          v-for="item in paginatedAttractions"
          :key="item.id"
          class="map-card"
        >
          <img
            :src="item.image_url || item.imageUrl || item.image || item.thumbnail || defaultImage"
            alt="Attraction image"
          />

          <div class="card-content">
            <span class="badge">{{ item.category || "Attraction" }}</span>
            <h2>{{ item.title }}</h2>
            <p>{{ shortText(item.body || item.description || "") }}</p>

            <!-- Navigation Google map location -->
            <iframe
              :src="getMapUrl(item.title)"
              loading="lazy"
            ></iframe>
          </div>
        </article>
      </div>

      <p v-if="filteredAttractions.length === 0" class="empty-message">
        No attractions found yet.
      </p>

      <!-- Pagination -->
      <div v-if="filteredAttractions.length > itemsPerPage" class="pagination">
        <button @click="previousPage" :disabled="page === 1">
          Previous
        </button>

        <span>Page {{ page }} of {{ totalPages }}</span>

        <button @click="nextPage" :disabled="page === totalPages">
          Next
        </button>
      </div>
    </section>
  </main>
</template>

<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { apiRequest } from "../api/client";

const attractions = ref([]);
const search = ref("");
const selectedCategory = ref("All");
const page = ref(1);
const itemsPerPage = 4;
const defaultImage = "https://upload.wikimedia.org/wikipedia/commons/1/14/No_Image_Available.jpg";

// Category filter options
const categories = computed(() => {
  const uniqueCategories = attractions.value
    .map((item) => item.category)
    .filter(Boolean);

  return ["All", ...new Set(uniqueCategories)];
});

// Search and filter attractions
const filteredAttractions = computed(() => {
  const searchText = search.value.toLowerCase();

  return attractions.value.filter((item) => {
    const matchesCategory =
      selectedCategory.value === "All" || item.category === selectedCategory.value;

    const matchesSearch =
      item.title?.toLowerCase().includes(searchText) ||
      item.category?.toLowerCase().includes(searchText) ||
      item.body?.toLowerCase().includes(searchText) ||
      item.description?.toLowerCase().includes(searchText);

    return matchesCategory && matchesSearch;
  });
});

// Pagination calculation
const totalPages = computed(() => {
  return Math.ceil(filteredAttractions.value.length / itemsPerPage);
});

const paginatedAttractions = computed(() => {
  const start = (page.value - 1) * itemsPerPage;
  return filteredAttractions.value.slice(start, start + itemsPerPage);
});

function shortText(text) {
  return text.length > 120 ? text.substring(0, 120) + "..." : text;
}

// Create Google Maps link from attraction title
function getMapUrl(title) {
  return `https://www.google.com/maps?q=${encodeURIComponent(title)}&output=embed`;
}

function previousPage() {
  if (page.value > 1) {
    page.value--;
  }
}

function nextPage() {
  if (page.value < totalPages.value) {
    page.value++;
  }
}

// replace with actual API endpoint to load attractions data
async function loadAttractions() {
  const data = await apiRequest("contents.php");

  attractions.value =
    data.items ||
    data.contents ||
    data.data ||
    data.records ||
    data ||
    [];
}

watch([search, selectedCategory], () => {
  page.value = 1;
});

onMounted(loadAttractions);
</script>