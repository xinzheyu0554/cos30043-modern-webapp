<script setup>
import { onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import { apiRequest } from "../api/client";

const router = useRouter();
const contents = ref([]);
const search = ref("");
const category = ref("");
const sort = ref("newest");
const page = ref(1);
const message = ref("");

async function loadContents() {
  try {
    message.value = "";

    const query = new URLSearchParams({
      search: search.value,
      category: category.value,
      sort: sort.value,
      page: String(page.value),
      limit: "6",
    });

    const result = await apiRequest(`contents.php?${query.toString()}`);
    contents.value = result.data || [];
  } catch (error) {
    message.value = error.message;
  }
}

function submitSearch() {
  page.value = 1;
  loadContents();
}

function nextPage() {
  page.value += 1;
  loadContents();
}

function previousPage() {
  if (page.value > 1) {
    page.value -= 1;
    loadContents();
  }
}

function openContent(contentId) {
  router.push(`/content/${contentId}`);
}

onMounted(loadContents);
</script>

<template>
  <section class="content-panel">
    <div class="section-heading">
      <div>
        <p class="section-kicker">Discover</p>
        <h2 class="section-title">Browse the content collection</h2>
      </div>
      <p class="section-copy">
        Search by keyword, filter by category, sort the results, and open any
        item for a detailed view.
      </p>
    </div>

    <div class="card surface-card filter-card">
      <div class="card-body">
        <div class="row g-3 align-items-end">
          <div class="col-12 col-md-4">
            <label class="form-label">Search</label>
            <input
              v-model="search"
              class="form-control"
              type="text"
              placeholder="Find by title, category, or body"
            />
          </div>

          <div class="col-12 col-md-3">
            <label class="form-label">Category</label>
            <input
              v-model="category"
              class="form-control"
              type="text"
              placeholder="e.g. Design"
            />
          </div>

          <div class="col-12 col-md-3">
            <label class="form-label">Sort by</label>
            <select v-model="sort" class="form-select">
              <option value="newest">Newest</option>
              <option value="oldest">Oldest</option>
              <option value="title">Title</option>
            </select>
          </div>

          <div class="col-12 col-md-2">
            <button class="btn btn-accent w-100" @click="submitSearch">
              Search
            </button>
          </div>
        </div>
      </div>
    </div>

    <p v-if="message" class="alert alert-warning mt-4">{{ message }}</p>

    <div class="row g-4 mt-1">
      <div
        v-for="content in contents"
        :key="content.contentId"
        class="col-12 col-md-6 col-xl-4"
      >
        <article class="card h-100 surface-card content-card">
          <img
            v-if="content.imageUrl"
            :src="content.imageUrl"
            class="card-img-top content-image"
            alt="content image"
          />
          <div class="card-body d-flex flex-column">
            <div class="content-meta-row">
              <span class="content-chip">{{ content.category || "General" }}</span>
              <span class="content-subtle">By {{ content.author || "Unknown" }}</span>
            </div>
            <h3 class="content-card-title">{{ content.title }}</h3>
            <p class="content-subtle mb-2">
              Added by {{ content.creatorName || "Staff" }}
            </p>
            <p class="card-text flex-grow-1 content-excerpt">
              {{ content.body }}
            </p>
            <button class="btn btn-outline-dark mt-3" @click="openContent(content.contentId)">
              View detail
            </button>
          </div>
        </article>
      </div>
    </div>

    <div class="pager-bar">
      <button
        class="btn btn-outline-dark"
        :disabled="page <= 1"
        @click="previousPage"
      >
        Previous
      </button>
      <span class="pager-status">Page {{ page }}</span>
      <button class="btn btn-outline-dark" @click="nextPage">Next</button>
    </div>
  </section>
</template>
