<script setup>
import { onMounted, ref } from "vue";
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

      message.value = "Content updated successfully.";
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

      message.value = "Content created successfully.";
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

    message.value = "Content removed.";
    await loadContents();
  } catch (error) {
    message.value = error.message;
  }
}

onMounted(loadContents);
</script>

<template>
  <section class="content-panel">
    <div class="row g-4">
      <div class="col-12 col-xl-5">
        <div class="card surface-card h-100">
          <div class="card-body p-4">
            <p class="section-kicker">Publishing</p>
            <h2 class="section-title mb-3">Manage content</h2>
            <form class="row g-3" @submit.prevent="saveContent">
              <div class="col-12">
                <label class="form-label">Title</label>
                <input v-model="title" class="form-control" />
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label">Author</label>
                <input v-model="author" class="form-control" />
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label">Category</label>
                <input v-model="category" class="form-control" />
              </div>

              <div class="col-12">
                <label class="form-label">Image URL</label>
                <input v-model="imageUrl" class="form-control" />
              </div>

              <div class="col-12">
                <label class="form-label">Body</label>
                <textarea v-model="body" class="form-control" rows="6"></textarea>
              </div>

              <div class="col-12 d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-accent">
                  {{ editingId ? "Update content" : "Create content" }}
                </button>
                <button type="button" class="btn btn-outline-dark" @click="resetForm">
                  Clear
                </button>
              </div>
            </form>

            <p v-if="message" class="alert alert-info mt-4 mb-0">{{ message }}</p>
          </div>
        </div>
      </div>

      <div class="col-12 col-xl-7">
        <div class="card surface-card h-100">
          <div class="card-body p-4">
            <p class="section-kicker">Existing entries</p>
            <div class="content-manager-list">
              <article
                v-for="content in contents"
                :key="content.contentId"
                class="manager-item"
              >
                <div>
                  <p class="content-chip mb-2">{{ content.category || "General" }}</p>
                  <h3 class="content-card-title mb-1">{{ content.title }}</h3>
                  <p class="content-subtle mb-0">
                    {{ content.author || "Unknown author" }}
                  </p>
                </div>

                <div class="table-actions">
                  <button class="btn btn-sm btn-outline-dark" @click="editContent(content)">
                    Edit
                  </button>
                  <button
                    class="btn btn-sm btn-outline-danger"
                    @click="deleteContent(content.contentId)"
                  >
                    Delete
                  </button>
                </div>
              </article>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
