<script setup>
import { ref, onMounted } from "vue";
import { apiRequest } from "../api/client";

const contents = ref([]);
const message = ref("");

const editingId = ref(null);
const title = ref("");
const author = ref("");
const category = ref("");
const imageUrl = ref("");
const body = ref("");

async function loadContents() {
  try {
    const result = await apiRequest("contents.php?limit=100");
    contents.value = result.data || [];
  } catch (error) {
    message.value = error.message;
  }
}

function resetForm() {
  editingId.value = null;
  title.value = "";
  author.value = "";
  category.value = "";
  imageUrl.value = "";
  body.value = "";
}

function editContent(content) {
  editingId.value = content.contentId;
  title.value = content.title || "";
  author.value = content.author || "";
  category.value = content.category || "";
  imageUrl.value = content.imageUrl || "";
  body.value = content.body || "";
}

async function saveContent() {
  try {
    if (editingId.value) {
      await apiRequest("contents.php", {
        method: "PUT",
        body: JSON.stringify({
          contentId: editingId.value,
          title: title.value,
          author: author.value,
          category: category.value,
          imageUrl: imageUrl.value,
          body: body.value,
        }),
      });

      message.value = "Content updated";
    } else {
      await apiRequest("contents.php", {
        method: "POST",
        body: JSON.stringify({
          title: title.value,
          author: author.value,
          category: category.value,
          imageUrl: imageUrl.value,
          body: body.value,
        }),
      });

      message.value = "Content created";
    }

    resetForm();
    await loadContents();
  } catch (error) {
    message.value = error.message;
  }
}

async function deleteContent(contentId) {
  try {
    await apiRequest("contents.php", {
      method: "DELETE",
      body: JSON.stringify({
        contentId,
      }),
    });

    await loadContents();
  } catch (error) {
    message.value = error.message;
  }
}

onMounted(loadContents);
</script>

<template>
  <div>
    <h2>Manage Content</h2>

    <p>{{ message }}</p>

    <form @submit.prevent="saveContent">
      <div>
        <label>Title</label>
        <input v-model="title" />
      </div>

      <div>
        <label>Author</label>
        <input v-model="author" />
      </div>

      <div>
        <label>Category</label>
        <input v-model="category" />
      </div>

      <div>
        <label>Image URL</label>
        <input v-model="imageUrl" />
      </div>

      <div>
        <label>Body</label>
        <textarea v-model="body"></textarea>
      </div>

      <button type="submit">
        {{ editingId ? "Update Content" : "Create Content" }}
      </button>

      <button type="button" @click="resetForm">Clear</button>
    </form>

    <hr />

    <div v-for="content in contents" :key="content.contentId">
      <h3>{{ content.title }}</h3>
      <p>{{ content.category }}</p>

      <button @click="editContent(content)">Edit</button>
      <button @click="deleteContent(content.contentId)">Delete</button>

      <hr />
    </div>
  </div>
</template>