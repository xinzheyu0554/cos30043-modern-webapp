<script setup>
import { ref, onMounted } from "vue";
import { apiRequest } from "../api/client";

const emit = defineEmits(["open-content"]);

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
      page: page.value,
      limit: 6,
    });

    const result = await apiRequest(`contents.php?${query.toString()}`);
    contents.value = result.data || [];
  } catch (error) {
    message.value = error.message;
  }
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

onMounted(loadContents);
</script>

<template>
  <div>
    <h2>Contents</h2>

    <div>
      <input v-model="search" placeholder="Search" />
      <input v-model="category" placeholder="Category" />

      <select v-model="sort">
        <option value="newest">Newest</option>
        <option value="oldest">Oldest</option>
        <option value="title">Title</option>
      </select>

      <button @click="loadContents">Search</button>
    </div>

    <p>{{ message }}</p>

    <div v-for="content in contents" :key="content.contentId">
    <h3>{{ content.title }}</h3>

    <img
        v-if="content.imageUrl"
        :src="content.imageUrl"
        alt="content image"
        width="200"
    />

    <p>Author: {{ content.author }}</p>
    <p>Category: {{ content.category }}</p>
    <p>Creator: {{ content.creatorName }}</p>
    <p>{{ content.body }}</p>

    <button @click="emit('open-content', content.contentId)">
        View Detail
    </button>

    <hr />
    </div>

    <button @click="previousPage">Previous</button>
    <span> Page {{ page }} </span>
    <button @click="nextPage">Next</button>
  </div>
</template>